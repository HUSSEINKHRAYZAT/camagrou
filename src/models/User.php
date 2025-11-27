<?php

class User {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function create($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, verification_token) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashedPassword, $verificationToken]);
            return $verificationToken;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function ensureProfileTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS user_profiles (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT UNIQUE NOT NULL,
                profile_picture VARCHAR(255) DEFAULT NULL,
                bio TEXT DEFAULT NULL,
                full_name VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function findById($id) {
        $this->ensureProfileTable();
        $stmt = $this->pdo->prepare("
            SELECT u.*, up.profile_picture AS avatar, up.bio
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function verifyEmail($token) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET verified = TRUE, verification_token = NULL 
            WHERE verification_token = ?
        ");
        return $stmt->execute([$token]);
    }
    
    public function updateNotificationPreference($userId, $enabled) {
        $stmt = $this->pdo->prepare("
            UPDATE users SET email_notifications = ? WHERE id = ?
        ");
        return $stmt->execute([$enabled, $userId]);
    }

    public function updateProfile($userId, $bio, $avatarPath = null) {
        $this->ensureProfileTable();
        $stmt = $this->pdo->prepare("
            INSERT INTO user_profiles (user_id, bio, profile_picture)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE bio = VALUES(bio), profile_picture = IFNULL(VALUES(profile_picture), profile_picture)
        ");
        return $stmt->execute([$userId, $bio, $avatarPath]);
    }

    public function findSuggestions($currentUserId, $limit = 6) {
        $this->ensureProfileTable();
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, up.profile_picture AS avatar, up.bio
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE u.id != ?
            ORDER BY u.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $currentUserId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStory($userId, $storyPath, $expiresAt) {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS stories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                media_type ENUM('image','video') DEFAULT 'image',
                filename VARCHAR(255) NOT NULL,
                caption TEXT DEFAULT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_expires_at (expires_at)
            )
        ");
        $stmt = $this->pdo->prepare("
            INSERT INTO stories (user_id, filename, expires_at) VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $storyPath, $expiresAt]);
    }

    public function getActiveStory($userId) {
        $stmt = $this->pdo->prepare("
            SELECT image_path AS story_path, expires_at 
            FROM stories 
            WHERE user_id = ? AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
