<?php
require_once(__DIR__ . "/../libs/jwt-utils.php");
require_once(__DIR__ . "/../model/dao/requests/ArticleRequest.php");
require_once(__DIR__ . "/../model/dao/requests/UserRequest.php");
require_once(__DIR__ . "/../model/dao/requests/MOPRequest.php");
require_once(__DIR__ . "/../model/dao/requests/ReactionRequest.php");
require_once (__DIR__ . "/../libs/functions-utils.php");
require_once(__DIR__ . "/../model/Article.php");
// Identification du type de méthode HTTP envoyée par le client
use model\Article;
use model\dao\requests\ArticleRequest;
use model\dao\requests\MOPRequest;
use model\dao\requests\ReactionRequest;
use model\dao\requests\UserRequest;
use model\User;
use function libs\deliverResponse;
use function libs\getJWTUser;

// Initialisation du fichier de log
//ini_set("error_log", "..\logs\journal.log");

$http_method = $_SERVER['REQUEST_METHOD'];
$articleRequest = new ArticleRequest();
$reactionRequest = new ReactionRequest();
$userRequest = new UserRequest();
$moderatorRequest = new MOPRequest();

switch ($http_method) {
    // Cas de la méthode GET
    case "GET":
        $bearer_token = get_bearer_token();
        $matchingData = null;
        if (is_jwt_valid($bearer_token)) {
            // Récupération des critères de recherche envoyés par le Client
            try {
                $user = getJWTUser($bearer_token, $userRequest);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        } else {
            // Si le token n'est pas valide, on considère que l'utilisateur est anonyme
            $user = new User("anonymous", "none", "anonymous");
        }
        if (isset($_GET['id'])) {
            $id = htmlspecialchars($_GET['id']);
            $article = $articleRequest->getArticle($id);
            $matchingData = $article->toArray();
            // Si l'utilisateur est un modérateur, on lui envoie les données supplémentaires
            if ($user->isModerator()) {
                $matchingData['likes_count'] = $moderatorRequest->getNbLikesFromArticle($article);
                $matchingData['users_who_liked'] = $moderatorRequest->getUsersWhoLiked($article);
                $matchingData['dislikes_count'] = $moderatorRequest->getNbDislikesFromArticle($article);
                $matchingData['users_who_disliked'] = $moderatorRequest->getUsersWhoDisliked($article);
            } elseif ($user->isPublisher()) {
                $matchingData['likes_count'] = $moderatorRequest->getNbLikesFromArticle($article);
                $matchingData['dislikes_count'] = $moderatorRequest->getNbDislikesFromArticle($article);
            }
            deliverResponse(200, "[{$user->getLogin()}] L'article a ete recupere avec succes", $matchingData);
        } elseif (isset($_GET['users'])) {
            if (!$user->isModerator()) {
                die("ERROR 403 : Vous n'avez pas les droits pour acceder a cette ressource !");
            }
            $matchingData = $moderatorRequest->getNbUsers();
            deliverResponse(200, "All users", $matchingData);
        } elseif (isset($_GET['my-articles'])) {
            if (!$user->isPublisher()) {
                die("ERROR 403 : Vous n'avez pas les droits pour acceder a cette ressource !");
            }
            $matchingData = $articleRequest->getMyOwnArticles($user);
            deliverResponse(200, "My articles", $matchingData);
        } else {
            $matchingData = $articleRequest->getAllArticles($user);
            deliverResponse(200, "All articles", $matchingData);
        }
        break;
    // Cas de la méthode POST
    case "POST":
        $bearer_token = get_bearer_token();
        if (is_jwt_valid($bearer_token)) {
            try {
                $user = getJWTUser($bearer_token, $userRequest);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            // Récupération des données envoyées par le Client
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData, true);

            // Protection contre les injections SQL et Cie
            $data = array_map('htmlspecialchars', $data);

            // Traitement
            if (isset($_GET['add'])) {
                $command = htmlspecialchars($_GET['add']);
                switch ($command) {
                    // Ajout d'un article
                    case "article":
                        if (!$user->isPublisher()) {
                            die("ERROR 403 : Vous n'avez pas les droits pour acceder a cette ressource !");
                        }
                        $data['auteur'] = $user->getLogin();
                        $data['date_de_publication'] = date('Y-m-d H:i:s');
                        $article = new Article($data['id'], $data['contenu'], $data['date_de_publication'], $data['auteur']);
                        $res = $articleRequest->insertArticle($article);
                        // Envoi de la réponse au Client
                        deliverResponse(201, "L'article a bien ete ajoutee", $data);
                        break;
                    // Liker d'un article
                    case "like":
                        if ($user->isModerator()) {
                            die("ERROR 403 : Vous n'avez pas les droits pour acceder a cette ressource !");
                        }
                        if (isset($data['id_article'])) {
                            $article = $articleRequest->getArticle($data['id_article']);
                            if (!$reactionRequest->likerArticle($article, $user)) {
                                die("Vous avez deja like cet article !");
                            }
                            // Envoi de la réponse au Client
                            deliverResponse(201, "Le like a bien ete ajoutee", $data);
                        } else {
                            die("ERROR 404 : L'ID de l'article n'est pas valide !");
                        }
                        break;
                    // Disliker d'un article
                    case "dislike":
                        if ($user->isModerator()) {
                            die("ERROR 403 : Vous n'avez pas les droits pour acceder a cette ressource !");
                        }
                        if (isset($data['id_article'])) {
                            $article = $articleRequest->getArticle($data['id_article']);
                            if (!$reactionRequest->dislikerArticle($article, $user)) {
                                die("Vous avez deja dislike cet article !");
                            }
                            // Envoi de la réponse au Client
                            deliverResponse(201, "Le dislike a bien ete ajoutee", $data);
                        } else {
                            die("ERROR 404 : L'ID de l'article n'est pas valide !");
                        }
                        break;
                    // Ajout d'un utilisateur
                    case "user":
                        if (!$user->isModerator()) {
                            die("ERROR 403 : Vous n'avez pas les droits pour acceder a cette ressource !");
                        }
                        if (isset($data['login']) && isset($data['password']) && isset($data['role'])) {
                            $user = new User($data['login'], hash('sha256', $data['password']), $data['role']);
                            if (!$userRequest->insertUser($user)) {
                                die("ERROR 409 : L'utilisateur existe deja !");
                            }
                            // Envoi de la réponse au Client
                            deliverResponse(201, "L'utilisateur a bien ete ajoutee", $data);
                        } else {
                            die("ERROR 404 : L'utilisateur n'est pas valide !");
                        }
                        break;
                    default:
                        die("ERROR 400 : La commande n'est pas valide !");
                }
            } else {
                die("ERROR 400 : La commande n'est pas valide !");
            }
        } else {
            die("ERROR 401 : Vous n'etes pas autorise a acceder a cette ressource !");
        }
        break;
    // Cas de la méthode PUT
    case "PUT":
        $bearer_token = get_bearer_token();
        $reactionRequest = new ReactionRequest();
        if (is_jwt_valid($bearer_token)) {
            try {
                $user = getJWTUser($bearer_token, $userRequest);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            // Récupération des données envoyées par le Client
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData, true);
            // Traitement
            if (isset($_GET['edit'])) {
                $edit_type = htmlspecialchars($_GET['edit']);
                switch ($edit_type) {
                    case "article":
                        $article = $articleRequest->getArticle($data['article_id']);
                        // Si l'utilisateur est un éditeur et qu'il est propriétaire de l'article
                        if ($user->isPublisher() && Article::isOwner($user, $article)) {
                            if ($articleRequest->updateArticle($data, $user)) {
                                deliverResponse(200, "L'article a ete modifie !", $data);
                            } else {
                                deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                            }
                        } else {
                            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                        }
                        break;
                    case "user":
                        if ($user->isModerator()) {
                            if ($userRequest->updateUser($data)) {
                                deliverResponse(200, "L'utilisateur a ete modifie !", $data);
                            } else {
                                deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                            }
                        } else {
                            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                        }
                        break;
                    default:
                        deliverResponse(404, "Commande inconnue !", null);
                        break;
                }
            } else {
                deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
            }
        } else {
            deliverResponse(401, "Utilisateur non authentifie !", null);
        }
        break;
    // Cas de la méthode DELETE
    case "DELETE":
        $bearer_token = get_bearer_token();
        if (is_jwt_valid($bearer_token)) {
            try {
                $user = getJWTUser($bearer_token, $userRequest);
            } catch (Exception $e) {
                die($e->getMessage());
            }
            // Récupération des données envoyées par le Client
            $postedData = file_get_contents('php://input');
            $data = json_decode($postedData, true);
            // Traitement
            if (isset($_GET['delete'])) {
                $delete_type = htmlspecialchars($_GET['delete']);
                switch ($delete_type) {
                    case "article":
                        $article = $articleRequest->getArticle($data['article_id']);
                        // Si l'utilisateur est un moderateur ou qu'il est propriétaire de l'article
                        if ($user->isModerator() || Article::isOwner($user, $article)) {
                            $res = $articleRequest->deleteArticle($article);
                            if (!$res) {
                                die("L'article n'a pas pu être supprimé !");
                            }
                            deliverResponse(200, "L'article a bien été supprimé", null);
                        } else {
                            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                        }
                        break;
                    case "unlike":
                        $reactionRequest = new ReactionRequest();
                        if ($user->isPublisher()) {
                            $article = $articleRequest->getArticle($data['id']);
                            if ($reactionRequest->unlikerArticle($article, $user)) {
                                deliverResponse(200, "Le like a ete retire !", $data);
                            } else {
                                deliverResponse(403, "Erreur lors de l'utilisation de la commande !", null);
                            }
                        } else {
                            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                        }
                        break;
                    case "undislike":
                        $reactionRequest = new ReactionRequest();
                        if ($user->isPublisher()) {
                            $article = $articleRequest->getArticle($data['id']);
                            if ($reactionRequest->undislikerArticle($article, $user)) {
                                deliverResponse(200, "Le dislike a ete retire !", $data);
                            } else {
                                deliverResponse(403, "Erreur lors de l'utilisation de la commande !", null);
                            }
                        } else {
                            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                        }
                        break;
                    case "user":
                        if ($user->isModerator()) {
                            if ($userRequest->deleteUser($data['user_id'])) {
                                deliverResponse(200, "L'utilisateur a bien été supprimé", null);
                            } else {
                                deliverResponse(404, "L'utilisateur n'a pas pu être supprimé !", null);
                            }
                        } else {
                            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
                        }
                        break;
                    default:
                        deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction ou elle n'existe pas", null);
                        break;
                }
            } else {
                deliverResponse(404, "Commande inconnue !", null);
            }
        } else {
            deliverResponse(403, "Vous n'êtes pas autorisé à accéder à cette fonction", null);
        }
        break;
    default:
        deliverResponse(400, "Methode non autorisee ou inconnue !", null);
        break;
}
