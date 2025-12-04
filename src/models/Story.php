<?php

class Story {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Create a new story
     */
    public function create($userId, $filename, $caption = null, $mediaType = 'image') {
        // Stories expire after 24 hours
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $this->pdo->prepare("
            INSERT INTO stories (user_id, media_type, filename, caption, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$userId, $mediaType, $filename, $caption, $expiresAt]);
    }
    
    /**
     * Get active stories from user
     */
    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stories 
            WHERE user_id = ? AND expires_at > NOW()
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active stories from friends
     */
    public function getFriendsStories($userId, $limit = 50) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.username, p.profile_picture,
                   (SELECT COUNT(*) FROM story_views WHERE story_id = s.id) as view_count,
                   (SELECT COUNT(*) FROM story_views WHERE story_id = s.id AND viewer_id = ?) as has_viewed
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN user_profiles p ON u.id = p.user_id
            WHERE s.expires_at > NOW()
            AND (s.user_id IN (SELECT friend_id FROM friendships WHERE user_id = ?) OR s.user_id = ?)
            ORDER BY has_viewed ASC, s.created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get stories grouped by user
     */
    public function getStoriesByUsers($userId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id as user_id, u.username, p.profile_picture,
                   COUNT(s.id) as story_count,
                   MAX(s.created_at) as latest_story,
                   MIN(CASE WHEN sv.viewer_id IS NULL THEN 0 ELSE 1 END) as all_viewed
            FROM users u
            JOIN user_profiles p ON u.id = p.user_id
            JOIN stories s ON s.user_id = u.id
            LEFT JOIN story_views sv ON sv.story_id = s.id AND sv.viewer_id = ?
            WHERE s.expires_at > NOW()
            AND (u.id IN (SELECT friend_id FROM friendships WHERE user_id = ?) OR u.id = ?)
            GROUP BY u.id
            ORDER BY all_viewed ASC, latest_story DESC
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get story by ID
     */
    public function getById($storyId) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.username, p.profile_picture
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN user_profiles p ON u.id = p.user_id
            WHERE s.id = ? AND s.expires_at > NOW()
        ");
        $stmt->execute([$storyId]);
        return $stmt->fetch();
    }
    
    /**
     * Mark story as viewed
     */
    public function markAsViewed($storyId, $viewerId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO story_views (story_id, viewer_id) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP
            ");
            return $stmt->execute([$storyId, $viewerId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get story viewers
     */
    public function getViewers($storyId) {
        $stmt = $this->pdo->prepare("
            SELECT sv.viewed_at, u.id, u.username, p.profile_picture
            FROM story_views sv
            JOIN users u ON sv.viewer_id = u.id
            JOIN user_profiles p ON u.id = p.user_id
            WHERE sv.story_id = ?
            ORDER BY sv.viewed_at DESC
        ");
        $stmt->execute([$storyId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Delete story
     */
    public function delete($storyId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM stories WHERE id = ? AND user_id = ?");
        return $stmt->execute([$storyId, $userId]);
    }
    
    /**
     * Delete expired stories
     */
    public function deleteExpired() {
        $stmt = $this->pdo->prepare("SELECT filename FROM stories WHERE expires_at < NOW()");
        $stmt->execute();
        $stories = $stmt->fetchAll();
        
        // Delete files
        foreach ($stories as $story) {
            $filepath = "public/uploads/stories/" . $story['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        // Delete from database
        $stmt = $this->pdo->prepare("DELETE FROM stories WHERE expires_at < NOW()");
        return $stmt->execute();
    }
    
    /**
     * Get story count for user
     */
    public function getActiveCount($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM stories 
            WHERE user_id = ? AND expires_at > NOW()
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Check if user can view story
     */
    public function canView($storyId, $viewerId) {
        $stmt = $this->pdo->prepare("
            SELECT s.user_id
            FROM stories s
            WHERE s.id = ? AND s.expires_at > NOW()
        ");
        $stmt->execute([$storyId]);
        $story = $stmt->fetch();
        
        if (!$story) {
            return false;
        }
        
        // Anyone can view any story
        return true;
    }
}
