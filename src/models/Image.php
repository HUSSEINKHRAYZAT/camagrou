<?php

class Image {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->ensureImagesTable();
        $this->ensureLikesTable();
        $this->ensureCommentsTable();
    }
    
    public function create($userId, $filename) {
        $stmt = $this->pdo->prepare("
            INSERT INTO images (user_id, filename) VALUES (?, ?)
        ");
        return $stmt->execute([$userId, $filename]);
    }
    
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare("
            SELECT i.*, u.username, 
                   COUNT(DISTINCT l.id) as like_count,
                   COUNT(DISTINCT c.id) as comment_count
            FROM images i
            JOIN users u ON i.user_id = u.id
            LEFT JOIN likes l ON i.id = l.image_id
            LEFT JOIN comments c ON i.id = c.image_id
            GROUP BY i.id
            ORDER BY i.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getUserImages($userId) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, 
                   COUNT(DISTINCT l.id) as like_count,
                   COUNT(DISTINCT c.id) as comment_count
            FROM images i
            LEFT JOIN likes l ON i.id = l.image_id
            LEFT JOIN comments c ON i.id = c.image_id
            WHERE i.user_id = ?
            GROUP BY i.id
            ORDER BY i.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function delete($imageId, $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM images WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$imageId, $userId]);
    }
    
    public function getTotalCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM images");
        return $stmt->fetchColumn();
    }
    
    /**
     * Get image owner information including email preferences
     * Used for sending comment notifications
     */
    public function getImageOwner($imageId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, u.email, u.email_notifications
            FROM images i
            JOIN users u ON i.user_id = u.id
            WHERE i.id = ?
        ");
        $stmt->execute([$imageId]);
        return $stmt->fetch();
    }

    private function ensureImagesTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS images (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                filename VARCHAR(255) NOT NULL,
                caption TEXT DEFAULT NULL,
                filter VARCHAR(50) DEFAULT NULL,
                is_public BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_created_at (created_at),
                INDEX idx_is_public (is_public)
            )
        ");
    }

    private function ensureLikesTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS likes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                image_id INT NOT NULL,
                user_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_like (image_id, user_id),
                INDEX idx_image_id (image_id),
                INDEX idx_user_id (user_id)
            )
        ");
    }

    private function ensureCommentsTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS comments (
                id INT PRIMARY KEY AUTO_INCREMENT,
                image_id INT NOT NULL,
                user_id INT NOT NULL,
                comment TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_image_id (image_id),
                INDEX idx_user_id (user_id)
            )
        ");
    }
}
