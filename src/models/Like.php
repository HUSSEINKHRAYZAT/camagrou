<?php

class Like {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function toggle($imageId, $userId) {
        // Check if like exists
        $stmt = $this->pdo->prepare("
            SELECT id FROM likes WHERE image_id = ? AND user_id = ?
        ");
        $stmt->execute([$imageId, $userId]);
        $like = $stmt->fetch();
        
        if ($like) {
            // Unlike
            $stmt = $this->pdo->prepare("
                DELETE FROM likes WHERE image_id = ? AND user_id = ?
            ");
            $stmt->execute([$imageId, $userId]);
            return false; // Unliked
        } else {
            // Like
            $stmt = $this->pdo->prepare("
                INSERT INTO likes (image_id, user_id) VALUES (?, ?)
            ");
            $stmt->execute([$imageId, $userId]);
            return true; // Liked
        }
    }
    
    public function hasUserLiked($imageId, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM likes WHERE image_id = ? AND user_id = ?
        ");
        $stmt->execute([$imageId, $userId]);
        return $stmt->fetchColumn() > 0;
    }
}
