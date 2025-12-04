<?php
/**
 * Cleanup script to fix inconsistent friend request data
 * Run this once to clean up any orphaned or invalid friend requests
 */

require_once 'config/config.php';
require_once 'src/models/Friendship.php';

echo "Starting friend request cleanup...\n\n";

$friendshipModel = new Friendship();

// Run cleanup
$result = $friendshipModel->cleanupRequests();

if ($result) {
    echo "✓ Cleanup completed successfully!\n";
} else {
    echo "✗ Cleanup failed. Check error logs.\n";
}

// Show statistics
$pdo = getDBConnection();

$stmt = $pdo->query("SELECT COUNT(*) FROM friend_requests WHERE status = 'pending'");
$pendingCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM friend_requests WHERE status = 'accepted'");
$acceptedCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM friend_requests WHERE status = 'rejected'");
$rejectedCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM friendships");
$friendshipCount = $stmt->fetchColumn();

echo "\nCurrent Statistics:\n";
echo "- Pending requests: $pendingCount\n";
echo "- Accepted requests: $acceptedCount\n";
echo "- Rejected requests: $rejectedCount\n";
echo "- Active friendships: $friendshipCount\n";

echo "\nCleanup complete!\n";
