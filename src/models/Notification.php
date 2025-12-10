<?php

class Notification {
    private $pdo;

    public function __construct() {
        $this->pdo = getDBConnection();
        $this->ensureTable();
    }

    private function ensureTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                type ENUM('like','comment','friend_request','friend_accepted','story_view','mention') NOT NULL,
                related_user_id INT DEFAULT NULL,
                related_item_id INT DEFAULT NULL,
                message TEXT NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_user_id (user_id),
                INDEX idx_is_read (is_read),
                INDEX idx_type (type),
                INDEX idx_created_at (created_at)
            )
        ");
    }

    public function create($userId, $type, $message, $relatedUserId = null, $relatedItemId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, type, message, related_user_id, related_item_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $type, $message, $relatedUserId, $relatedItemId]);
    }

    public function getUnread($userId, $limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT id, type, message, created_at 
            FROM notifications
            WHERE user_id = ? AND is_read = FALSE
            ORDER BY created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function markAllRead($userId) {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
