<?php

class Message {
    private $pdo;

    public function __construct() {
        $this->pdo = getDBConnection();
        $this->ensureTable();
    }

    private function ensureTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sender_id INT NOT NULL,
                receiver_id INT NOT NULL,
                body TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_sender (sender_id),
                INDEX idx_receiver (receiver_id),
                INDEX idx_pair (sender_id, receiver_id),
                FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    }

    public function send($senderId, $receiverId, $body) {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, body)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$senderId, $receiverId, $trimmed]);
    }

    public function getConversation($userId, $otherUserId, $limit = 50) {
        $limit = (int)$limit;
        $stmt = $this->pdo->prepare("
            SELECT id, sender_id, receiver_id, body, created_at
            FROM messages
            WHERE (sender_id = :u1 AND receiver_id = :u2)
               OR (sender_id = :u2 AND receiver_id = :u1)
            ORDER BY created_at ASC
            LIMIT $limit
        ");
        $stmt->execute([':u1' => $userId, ':u2' => $otherUserId]);
        return $stmt->fetchAll();
    }
}
