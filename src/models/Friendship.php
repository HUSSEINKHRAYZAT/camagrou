<?php

class Friendship {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->ensureTables();
    }

    private function ensureTables() {
        // Minimal schema for friend requests and friendships
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
    
    /**
     * Send friend request
     */
    public function sendRequest($senderId, $receiverId) {
        try {
            // Check if users are already friends
            if ($this->areFriends($senderId, $receiverId)) {
                return ['success' => false, 'message' => 'Already friends'];
            }
            
            // Check if request already exists
            $stmt = $this->pdo->prepare("
                SELECT id, status FROM friend_requests 
                WHERE (sender_id = ? AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = ?)
            ");
            $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                if ($existing['status'] === 'pending') {
                    return ['success' => false, 'message' => 'Request already sent'];
                }
            }
            
            // Create new request
            $stmt = $this->pdo->prepare("
                INSERT INTO friend_requests (sender_id, receiver_id, status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$senderId, $receiverId]);
            
            return ['success' => true, 'message' => 'Friend request sent'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Request already exists'];
        }
    }
    
    /**
     * Accept friend request
     */
    public function acceptRequest($requestId, $userId) {
        try {
            $this->pdo->beginTransaction();
            
            // Get request details
            $stmt = $this->pdo->prepare("
                SELECT sender_id, receiver_id FROM friend_requests 
                WHERE id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$requestId, $userId]);
            $request = $stmt->fetch();
            
            if (!$request) {
                $this->pdo->rollBack();
                return ['success' => false, 'message' => 'Request not found'];
            }
            
            // Update request status
            $stmt = $this->pdo->prepare("
                UPDATE friend_requests SET status = 'accepted' WHERE id = ?
            ");
            $stmt->execute([$requestId]);
            
            // Create friendships (both directions for easy querying)
            $stmt = $this->pdo->prepare("
                INSERT INTO friendships (user_id, friend_id) VALUES (?, ?), (?, ?)
            ");
            $stmt->execute([
                $request['sender_id'], 
                $request['receiver_id'],
                $request['receiver_id'],
                $request['sender_id']
            ]);
            
            $this->pdo->commit();
            return ['success' => true, 'message' => 'Friend request accepted', 'sender_id' => $request['sender_id']];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => 'Error accepting request'];
        }
    }
    
    /**
     * Reject friend request
     */
    public function rejectRequest($requestId, $userId) {
        $stmt = $this->pdo->prepare("
            UPDATE friend_requests SET status = 'rejected' 
            WHERE id = ? AND receiver_id = ?
        ");
        $stmt->execute([$requestId, $userId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Remove friend
     */
    public function removeFriend($userId, $friendId) {
        try {
            $this->pdo->beginTransaction();
            
            // Remove both friendship records
            $stmt = $this->pdo->prepare("
                DELETE FROM friendships 
                WHERE (user_id = ? AND friend_id = ?) 
                   OR (user_id = ? AND friend_id = ?)
            ");
            $stmt->execute([$userId, $friendId, $friendId, $userId]);
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
    
    /**
     * Check if two users are friends
     */
    public function areFriends($userId1, $userId2) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count FROM friendships 
            WHERE (user_id = ? AND friend_id = ?) 
               OR (user_id = ? AND friend_id = ?)
        ");
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        return $stmt->fetch()['count'] > 0;
    }
    
    /**
     * Get user's friends
     */
    public function getFriends($userId, $limit = 50, $offset = 0) {
        $limit = (int)$limit;
        $offset = (int)$offset;
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, up.profile_picture AS avatar, up.bio, f.created_at as friends_since
            FROM friendships f
            JOIN users u ON f.friend_id = u.id
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function countFriends($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM friendships WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get pending friend requests (received)
     */
    public function getPendingRequests($userId) {
        $stmt = $this->pdo->prepare("
            SELECT fr.id, fr.sender_id, fr.created_at,
                   u.username, up.profile_picture AS avatar, up.bio
            FROM friend_requests fr
            JOIN users u ON fr.sender_id = u.id
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE fr.receiver_id = ? AND fr.status = 'pending'
            ORDER BY fr.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sent friend requests
     */
    public function getSentRequests($userId) {
        $stmt = $this->pdo->prepare("
            SELECT fr.id, fr.receiver_id, fr.status, fr.created_at,
                   u.username, up.profile_picture AS avatar, up.bio
            FROM friend_requests fr
            JOIN users u ON fr.receiver_id = u.id
            LEFT JOIN user_profiles up ON up.user_id = u.id
            WHERE fr.sender_id = ?
            ORDER BY fr.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get friend suggestions (mutual friends, etc.)
     */
    public function getSuggestions($userId, $limit = 10) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT u.id, u.username, up.profile_picture AS avatar, up.bio,
                   COUNT(DISTINCT f2.friend_id) as mutual_friends
            FROM users u
            LEFT JOIN user_profiles up ON up.user_id = u.id
            LEFT JOIN friendships f1 ON (f1.friend_id = u.id AND f1.user_id IN (
                SELECT friend_id FROM friendships WHERE user_id = ?
            ))
            LEFT JOIN friendships f2 ON (f2.user_id = u.id AND f2.friend_id IN (
                SELECT friend_id FROM friendships WHERE user_id = ?
            ))
            WHERE u.id != ?
            AND u.id NOT IN (SELECT friend_id FROM friendships WHERE user_id = ?)
            AND u.id NOT IN (SELECT receiver_id FROM friend_requests WHERE sender_id = ? AND status = 'pending')
            AND u.id NOT IN (SELECT sender_id FROM friend_requests WHERE receiver_id = ? AND status = 'pending')
            GROUP BY u.id
            ORDER BY mutual_friends DESC, u.created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll();
    }
}
