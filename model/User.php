<?php
namespace model;

/**
 * Classe représentant un utilisateur
 */
class User
{
    private $login;
    private $password;
    private $role;

    /**
     * Constructeur de la classe User
     *
     * @param string $login // Le login de l'utilisateur
     * @param string $password // Le mot de passe de l'utilisateur
     * @param string $role // Le rôle de l'utilisateur
     */
    public function __construct(string $login, string $password, string $role)
    {
        $this->login = $login;
        $this->password = $password;
        $this->role = $role;
    }

    /**
     * Cette fonction renvoie le login de l'utilisateur
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * Cette fonction renvoie le mot de passe de l'utilisateur encodé en sha256
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Cette fonction renvoie le rôle de l'utilisateur [anonymous, moderator, publisher]
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Crée un tableau associatif à partir de l'objet User
     * @return array
     */
    public function toArray(): array
    {
        return array(
            'login' => $this->login,
            'password' => $this->password,
            'role' => $this->role
        );
    }

    /**
     * Cette fonction permet de savoir si l'utilisateur est modérateur
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    /**
     * Cette fonction permet de savoir si l'utilisateur est un éditeur
     * @return bool
     */
    public function isPublisher(): bool
    {
        return $this->role === 'publisher';
    }

    /**
     * Cette fonction permet de savoir si l'utilisateur est un administrateur suprême
     * @return bool
     */
    public function isMaster(): bool
    {
        return $this->login === 'maxiwere' ||
                 $this->login === 'iutprof';
    }
}
