<?php

class Comment {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    public function create($imageId, $userId, $comment) {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (image_id, user_id, comment) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$imageId, $userId, $comment]);
    }
    
    public function getByImage($imageId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.username 
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.image_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$imageId]);
        return $stmt->fetchAll();
    }
    
    public function delete($commentId, $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM comments WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$commentId, $userId]);
    }
}
