<?php

namespace model\dao\requests;

require_once(__DIR__ . "/../../dao/Database.php");
require_once(__DIR__ . "/../../Article.php");
use model\Article;
use model\dao\Database;
use PDO;

/**
 * Cette classe permet de récupérer des informations sur les utilisateurs, les articles, les likes et les dislikes
 * Class MOPRequest
 * @package model\dao\requests
 */
class MOPRequest
{
    private $linkpdo;

    public function __construct()
    {
        $this->linkpdo = Database::getInstance('root', "9wms351v")->getConnection();
    }

    /**
     * Cette fonction retourne le nombre de modérateurs
     * @return int
     */
    public function getNbModerators(): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'moderator'";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        return $data[0];
    }

    /**
     * Cette fonction retourne le nombre de rédacteurs
     * @return int
     */
    public function getNbPublishers(): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE role = 'publisher'";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        return $data[0];
    }

    /**
     * Cette fonction retourne le nombre d'utilisateurs
     * @return int
     */
    public function getNbUsers(): int
    {
        $sql = "SELECT COUNT(*) FROM users";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        return $data[0];
    }

    /**
     * Cette fonction retourne le nombre de likes d'un article
     * @param Article $article
     * @return int
     */
    public function getNbLikesFromArticle(Article $article): int
    {
        $sql = "SELECT COUNT(*) FROM likes WHERE article_id = :id";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute(array(':id' => $article->getId()));
        $data = $stmt->fetch();
        return $data[0];
    }


    /**
     * Cette fonction retourne le nombre de dislikes d'un article
     * @param Article $article
     * @return int
     */
    public function getNbDislikesFromArticle(Article $article): int
    {
        $sql = "SELECT COUNT(*) FROM dislikes WHERE article_id = :id";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute(array(':id' => $article->getId()));
        $data = $stmt->fetch();
        return $data[0];
    }

    /**
     * Cette fonction retourne les utilisateurs qui ont liké un article
     * @param Article $article
     * @return array
     */
    public function getUsersWhoLiked(Article $article): array
    {
        $sql = "SELECT id_username FROM likes WHERE article_id = :id";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute(array(':id' => $article->getId()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cette fonction retourne les utilisateurs qui ont disliké un article
     * @param Article $article
     * @return array
     */
    public function getUsersWhoDisliked(Article $article): array
    {
        $sql = "SELECT id_username FROM dislikes WHERE article_id = :id";
        $stmt = $this->linkpdo->prepare($sql);
        $stmt->execute(array(':id' => $article->getId()));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
