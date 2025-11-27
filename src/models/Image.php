<?php

class Image {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
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
}
