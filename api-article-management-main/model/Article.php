<?php
namespace model;

/**
 * Une classe représentant un article
 */
class Article
{
    private $id;
    private $content;
    private $date_add;
    private $author;

    public function __construct($id, $content, $date_add, $author)
    {
        $this->id = $id;
        $this->content = $content;
        $this->date_add = $date_add;
        $this->author = $author;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getDate_add()
    {
        return $this->date_add;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Retourne un tableau associatif contenant les données de l'article
     * @return array
     */
    public function toArray(): array
    {
        return array(
            'id' => $this->id,
            'author' => $this->author,
            'content' => $this->content,
            'date_add' => $this->date_add
        );
    }

    /**
     * Retourne vrai si l'utilisateur est propriétaire de l'article
     * @param User $user
     * @param Article $article
     * @return bool
     */
    public static function isOwner(User $user, Article $article): bool
    {
        return $user->getLogin() === $article->getAuthor();
    }
}