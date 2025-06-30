<?php

require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        if ($this->usernameExists($data['username'])) {
            return ['success' => false, 'errors' => ['Ce nom d\'utilisateur est déjà pris.']];
        }
        
        if ($this->emailExists($data['email'])) {
            return ['success' => false, 'errors' => ['Cette adresse email est déjà utilisée.']];
        }
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "
            INSERT INTO user (username, email, password_hash, first_name, last_name, bio, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";
        
        $params = [
            $data['username'],
            $data['email'],
            $passwordHash,
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['bio'] ?? null
        ];
        
        if ($this->db->execute($query, $params)) {
            $userId = $this->db->getLastInsertId();
            return ['success' => true, 'user_id' => $userId];
        }
        
        return ['success' => false, 'errors' => ['Erreur lors de la création du compte.']];
    }
    
    public function authenticate($usernameOrEmail, $password) {
        $query = "
            SELECT id, username, email, password_hash, first_name, last_name, bio, avatar_url, is_active
            FROM user 
            WHERE (username = ? OR email = ?) AND is_active = 1
        ";
        
        $user = $this->db->selectOne($query, [$usernameOrEmail, $usernameOrEmail]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->updateLastLogin($user['id']);
            
            unset($user['password_hash']);
            return ['success' => true, 'user' => $user];
        }
        
        return ['success' => false, 'error' => 'Nom d\'utilisateur ou mot de passe incorrect.'];
    }
    
    public function getById($id) {
        $query = "
            SELECT id, username, email, first_name, last_name, bio, avatar_url, created_at, last_login
            FROM user 
            WHERE id = ? AND is_active = 1
        ";
        
        return $this->db->selectOne($query, [$id]);
    }
    
    public function getByUsername($username) {
        $query = "
            SELECT id, username, email, first_name, last_name, bio, avatar_url, created_at, last_login
            FROM user 
            WHERE username = ? AND is_active = 1
        ";
        
        return $this->db->selectOne($query, [$username]);
    }
    
    public function updateProfile($userId, $data) {
        $query = "
            UPDATE user 
            SET first_name = ?, last_name = ?, bio = ?, updated_at = NOW()
            WHERE id = ?
        ";
        
        $params = [
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['bio'] ?? null,
            $userId
        ];
        
        return $this->db->execute($query, $params);
    }
    
    public function updateAvatar($userId, $avatarUrl) {
        $query = "UPDATE user SET avatar_url = ?, updated_at = NOW() WHERE id = ?";
        return $this->db->execute($query, [$avatarUrl, $userId]);
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->db->selectOne("SELECT password_hash FROM user WHERE id = ?", [$userId]);
        
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Mot de passe actuel incorrect.'];
        }
        
        if (!$this->isValidPassword($newPassword)) {
            return ['success' => false, 'error' => 'Le nouveau mot de passe ne respecte pas les critères de sécurité.'];
        }
        
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE user SET password_hash = ?, updated_at = NOW() WHERE id = ?";
        
        if ($this->db->execute($query, [$newPasswordHash, $userId])) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'Erreur lors de la mise à jour du mot de passe.'];
    }
    
    private function usernameExists($username) {
        $query = "SELECT id FROM user WHERE username = ?";
        return $this->db->selectOne($query, [$username]) !== false;
    }
    
    private function emailExists($email) {
        $query = "SELECT id FROM user WHERE email = ?";
        return $this->db->selectOne($query, [$email]) !== false;
    }
    
    private function updateLastLogin($userId) {
        $query = "UPDATE user SET last_login = NOW() WHERE id = ?";
        $this->db->execute($query, [$userId]);
    }
    

    // VALIDATIONS
    private function validate($data) {
        $errors = [];
        
        // Nom d'utilisateur
        if (empty($data['username'])) {
            $errors[] = "Le nom d'utilisateur est obligatoire.";
        } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
            $errors[] = "Le nom d'utilisateur doit contenir entre 3 et 50 caractères.";
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $data['username'])) {
            $errors[] = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres, tirets et underscores.";
        }
        
        // Email
        if (empty($data['email'])) {
            $errors[] = "L'adresse email est obligatoire.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }
        
        // Mot de passe
        if (empty($data['password'])) {
            $errors[] = "Le mot de passe est obligatoire.";
        } elseif (!$this->isValidPassword($data['password'])) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.";
        }
        
        // Confirmation du mot de passe
        if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        return $errors;
    }
    
    //format du mot de passe
    private function isValidPassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }
    
    // Stats user
    public function getUserStats($userId) {
        $stats = [];
        
        $storyCount = $this->db->selectOne("SELECT COUNT(*) as count FROM story WHERE user_id = ?", [$userId]);
        $stats['story_count'] = $storyCount['count'] ?? 0;
        
        $chapterCount = $this->db->selectOne("
            SELECT COUNT(c.id) as count 
            FROM chapter c 
            INNER JOIN story s ON c.story_id = s.id 
            WHERE s.user_id = ?
        ", [$userId]);
        $stats['chapter_count'] = $chapterCount['count'] ?? 0;
        
        $followerCount = $this->db->selectOne("SELECT COUNT(*) as count FROM follow WHERE followed_id = ?", [$userId]);
        $stats['follower_count'] = $followerCount['count'] ?? 0;
        
        $followingCount = $this->db->selectOne("SELECT COUNT(*) as count FROM follow WHERE follower_id = ?", [$userId]);
        $stats['following_count'] = $followingCount['count'] ?? 0;
        
        return $stats;
    }
}