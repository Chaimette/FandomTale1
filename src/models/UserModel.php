<?php

namespace App\Models;

use PDO;
use PDOException;
use \DateTime;
use App\Models\AbstractModel;

class UserModel extends AbstractModel
{
    public const TABLE = 'user';
    public const ID = 'id';

    /**
     * Récupère un user par son email
     *
     * @param string $email
     * @return array|null
     */
    public function getUserByMail(string $email): ?array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Créer un user
     */
    public function createUser(
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
        ?string $bio = null,
        ?string $avatarUrl = null,
        bool $isActive = true
    ): bool {
        $query = "INSERT INTO " . self::TABLE . " 
              (username, email, first_name, last_name, password, bio, avatar_url, created_at, updated_at, is_active)
              VALUES (:username, :email, :first_name, :last_name, :password, :bio, :avatar_url, NOW(), NOW(), :is_active)";
        $stmt = $this->pdo->prepare($query);
        $params = [
            ':username' => $username,
            ':email' => $email,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':password' => password_hash($password, PASSWORD_ARGON2I, [
                'memory_cost' => 1 << 17,
                'time_cost' => 4,
                'threads' => 2
            ]),
            ':bio' => $bio,
            ':avatar_url' => $avatarUrl,
            ':is_active' => $isActive ? 1 : 0
        ];
        try {
            if ($this->emailExists($email)) {
                return false;
            }
            return $stmt->execute($params);
        } catch (\PDOException $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un email existe déjà
     *
     * @param string $email
     * @return boolean
     */
    public function emailExists(string $email): bool
    {
        $query = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Récupère un user avec son id
     *
     * @param integer $id
     * @return array|null
     */
    public function getUserById(int $id): ?array
    {
        $query = "SELECT username, first_name, last_name, bio, avatar_url, is_active, created_at, updated_at FROM " . self::TABLE . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        try {
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
            return null;
        }
    }

/**
 * Modifie les données d'un user dont le mdp
 *
 * @param integer $id
 * @param string $username
 * @param string $email
 * @param string $firstName
 * @param string $lastName
 * @param string|null $bio
 * @param string|null $avatarUrl
 * @param boolean $isActive
 * @param string|null $password
 * @return boolean
 */
    public function updateUser(
        int $id,
        string $username,
        string $email,
        string $firstName,
        string $lastName,
        ?string $bio = null,
        ?string $avatarUrl = null,
        bool $isActive = true,
        ?string $password = null

    ): bool {
        $query = "UPDATE " . self::TABLE . " SET
            username = :username,
            email = :email,
            first_name = :first_name,
            last_name = :last_name,
            bio = :bio,
            avatar_url = :avatar_url,
            is_active = :is_active,
            updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $params = [
            ':id' => $id,
            ':username' => $username,
            ':email' => $email,
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':bio' => $bio,
            ':avatar_url' => $avatarUrl,
            ':is_active' => $isActive ? 1 : 0
        ];
        if ($password) {
            $query .= ", password = :password";
            $params[':password'] = password_hash(
                $password,
                PASSWORD_ARGON2I,
                [
                    'memory_cost' => 1 << 17,
                    'time_cost' => 4,
                    'threads' => 2
                ]
            );
        }
        try {
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Récupère tous les utilisateurs, seront affichés dans le panel admin uniquement
     *
     * @return array
     */
    public function getAllUsers(): array
    {
        $query = "SELECT id, username, email, first_name, last_name, bio, avatar_url, is_active, created_at, updated_at FROM " . self::TABLE;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un user à partir de son id
     *
     * @param integer $id
     * @return boolean
     */
    public function deleteUserById(int $id): bool
    {
        $query = "DELETE FROM " . self::TABLE . " WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur avec email et mdp correspondant
     *
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function getUserByMailAndPassword(string $email, string $password): ?array
    {
        $query = "SELECT * FROM " . self::TABLE . " WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return null;
    }

    /**
     * Update le mdp uniquement, à utiliser éventuellement pour la fonction "mdp oublié"
     *
     * @param integer $id
     * @param string $password
     * @return boolean
     */
    public function updatePassword(int $id, string $password): bool
    {
        $hashedPassword = password_hash($password, PASSWORD_ARGON2I, [
            'memory_cost' => 1 << 17,
            'time_cost' => 4,
            'threads' => 2
        ]);
        $sql = "UPDATE " . self::TABLE . " SET password = :password WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        return $stmt->execute();
    }

    /**
     * Récupère les histoires d'un utilisateur
     *
     * @param integer $userId
     * @return array
     */
    public function getUserStories(int $userId): array
    {
        $query = "SELECT s.id, s.title, s.content, s.created_at, s.updated_at
                    FROM story s
                    JOIN user_story us ON s.id = us.story_id
                    WHERE us.user_id = :user_id
                    ORDER BY s.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

/** Récupère les commentaires d'un user
 */
    public function getUserComments(int $userId): array
    {
        $query = "SELECT c.id, c.content, c.created_at, c.updated_at, s.title
                    FROM comment c
                    JOIN story s ON c.story_id = s.id
                    WHERE c.user_id = :user_id
                    ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //TODO: isUserAdmin() // isUserActive() // isUserBanned() // Token management (JWT, etc.) for user sessions // methods to search users by username, email, etc. // ...

}
