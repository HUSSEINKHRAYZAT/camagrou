<?php

class User {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function create($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        // Generate 6-digit OTP code
        $otpCode = sprintf("%06d", mt_rand(0, 999999));
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP expires in 15 minutes
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, verification_token, otp_expiry) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashedPassword, $otpCode, $otpExpiry]);
            return $otpCode;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByIdentifier($identifier) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$identifier, $identifier]);
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

    private function ensureStoriesTable() {
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
    
    public function verifyOTP($email, $otpCode) {
        // Check if OTP is valid and not expired
        $stmt = $this->pdo->prepare("
            SELECT id FROM users 
            WHERE email = ? 
            AND verification_token = ? 
            AND otp_expiry > NOW()
            AND verified = FALSE
        ");
        $stmt->execute([$email, $otpCode]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Mark user as verified
            $updateStmt = $this->pdo->prepare("
                UPDATE users 
                SET verified = TRUE, verification_token = NULL, otp_expiry = NULL 
                WHERE id = ?
            ");
            return $updateStmt->execute([$user['id']]);
        }
        
        return false;
    }
    
    public function resendOTP($email) {
        // Generate new 6-digit OTP
        $otpCode = sprintf("%06d", mt_rand(0, 999999));
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET verification_token = ?, otp_expiry = ? 
            WHERE email = ? AND verified = FALSE
        ");
        
        if ($stmt->execute([$otpCode, $otpExpiry, $email])) {
            return $otpCode;
        }
        
        return false;
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

    private function ensureFriendshipTables() {
        // Keep schema small here to avoid depending on Friendship model instantiation
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS friend_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                status ENUM('pending','accepted','rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_request (sender_id, receiver_id),
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS friendships (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                friend_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_friend (user_id, friend_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function updateAccount($userId, $username, $email, $newPassword = null) {
        $params = [$username, $email];
        $passwordSql = "";
        if (!empty($newPassword)) {
            $passwordSql = ", password = ?";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        $params[] = $userId;

        $stmt = $this->pdo->prepare("
            UPDATE users SET username = ?, email = ? $passwordSql
            WHERE id = ?
        ");
        return $stmt->execute($params);
    }

    private function ensureResetTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_token (token),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function createResetToken($email) {
        $this->ensureResetTable();
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $this->pdo->prepare("
            INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)
        ");
        $stmt->execute([$user['id'], $token, $expires]);
        return $token;
    }

    public function validateResetToken($token) {
        $this->ensureResetTable();
        $stmt = $this->pdo->prepare("
            SELECT pr.user_id, u.email
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? AND pr.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function updatePasswordById($userId, $newPassword) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
    }

    public function findSuggestions($currentUserId, $limit = 6) {
        $this->ensureProfileTable();
        $this->ensureFriendshipTables();
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, up.profile_picture AS avatar, up.bio
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE u.id != :current
              AND u.id NOT IN (SELECT friend_id FROM friendships WHERE user_id = :current)
              AND u.id NOT IN (SELECT user_id FROM friendships WHERE friend_id = :current)
              AND u.id NOT IN (SELECT receiver_id FROM friend_requests WHERE sender_id = :current AND status = 'pending')
              AND u.id NOT IN (SELECT sender_id FROM friend_requests WHERE receiver_id = :current AND status = 'pending')
            ORDER BY u.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':current', $currentUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStory($userId, $storyPath, $expiresAt) {
        $this->ensureStoriesTable();
        $stmt = $this->pdo->prepare("
            INSERT INTO stories (user_id, filename, expires_at) VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $storyPath, $expiresAt]);
    }

    public function getActiveStory($userId) {
        $this->ensureStoriesTable();
        $stmt = $this->pdo->prepare("
            SELECT filename AS story_path, expires_at 
            FROM stories 
            WHERE user_id = ? AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
