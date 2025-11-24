<?php

class UserProfile {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * Get user profile by user ID
     */
    public function getByUserId($userId) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, u.username, u.email, u.created_at as user_since
            FROM user_profiles p
            JOIN users u ON p.user_id = u.id
            WHERE p.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    /**
     * Update profile information
     */
    public function update($userId, $data) {
        $allowedFields = ['full_name', 'bio', 'location', 'website', 'birth_date', 'gender', 'is_private'];
        $fields = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $userId;
        $sql = "UPDATE user_profiles SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Update profile picture
     */
    public function updateProfilePicture($userId, $filename) {
        $stmt = $this->pdo->prepare("UPDATE user_profiles SET profile_picture = ? WHERE user_id = ?");
        return $stmt->execute([$filename, $userId]);
    }
    
    /**
     * Get profile statistics
     */
    public function getStats($userId) {
        // Get image count
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM images WHERE user_id = ?");
        $stmt->execute([$userId]);
        $imageCount = $stmt->fetch()['count'];
        
        // Get friends count
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM friendships 
            WHERE user_id = ? OR friend_id = ?
        ");
        $stmt->execute([$userId, $userId]);
        $friendsCount = $stmt->fetch()['count'];
        
        // Get likes received
        $stmt = $this->pdo->prepare("
            SELECT COUNT(l.id) as count 
            FROM likes l 
            JOIN images i ON l.image_id = i.id 
            WHERE i.user_id = ?
        ");
        $stmt->execute([$userId]);
        $likesCount = $stmt->fetch()['count'];
        
        return [
            'images' => $imageCount,
            'friends' => $friendsCount,
            'likes' => $likesCount
        ];
    }
    
    /**
     * Search profiles
     */
    public function search($query, $limit = 20) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, p.profile_picture, p.full_name, p.bio
            FROM users u
            JOIN user_profiles p ON u.id = p.user_id
            WHERE u.username LIKE ? OR p.full_name LIKE ?
            LIMIT $limit
        ");
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if profile is private
     */
    public function isPrivate($userId) {
        $stmt = $this->pdo->prepare("SELECT is_private FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? (bool)$result['is_private'] : false;
    }
}
