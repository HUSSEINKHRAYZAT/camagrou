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
    
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
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
}
