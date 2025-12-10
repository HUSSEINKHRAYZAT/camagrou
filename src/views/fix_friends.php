<?php
/**
 * Diagnostic and cleanup page for friend requests
 * Access this via: index.php?page=fix-friends
 */

session_start();

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

require_once 'config/config.php';
require_once 'src/models/Friendship.php';

$friendshipModel = new Friendship();
$pdo = getDBConnection();

// Run cleanup if requested
if (isset($_POST['cleanup'])) {
    $friendshipModel->cleanupRequests();
    $_SESSION['success'] = "Cleanup completed!";
    header('Location: index.php?page=fix-friends');
    exit;
}

// Get statistics
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) FROM friend_requests WHERE status = 'pending'");
$stats['pending'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM friend_requests WHERE status = 'accepted'");
$stats['accepted'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM friend_requests WHERE status = 'rejected'");
$stats['rejected'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM friendships");
$stats['friendships'] = $stmt->fetchColumn();

// Find inconsistencies
$stmt = $pdo->query("
    SELECT fr.id, fr.sender_id, fr.receiver_id, fr.status,
           u1.username as sender_name, u2.username as receiver_name,
           (SELECT COUNT(*) FROM friendships 
            WHERE (user_id = fr.sender_id AND friend_id = fr.receiver_id)) as is_friend
    FROM friend_requests fr
    JOIN users u1 ON fr.sender_id = u1.id
    JOIN users u2 ON fr.receiver_id = u2.id
    WHERE fr.status IN ('accepted', 'rejected')
    ORDER BY fr.created_at DESC
    LIMIT 20
");
$inconsistencies = $stmt->fetchAll();

$title = "Friend Request Diagnostics";
include 'src/views/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1>Friend Request Diagnostics</h1>
    
    <div style="background: var(--bg-elevated); padding: 20px; border-radius: 12px; margin: 20px 0;">
        <h2>Statistics</h2>
        <ul style="list-style: none; padding: 0;">
            <li><strong>Pending Requests:</strong> <?= $stats['pending'] ?></li>
            <li><strong>Accepted Requests (should be 0):</strong> <?= $stats['accepted'] ?></li>
            <li><strong>Rejected Requests:</strong> <?= $stats['rejected'] ?></li>
            <li><strong>Active Friendships:</strong> <?= $stats['friendships'] ?></li>
        </ul>
    </div>
    
    <?php if ($stats['accepted'] > 0 || $stats['rejected'] > 10): ?>
    <div style="background: #FFF3CD; color: #856404; padding: 20px; border-radius: 12px; margin: 20px 0;">
        <h3>⚠️ Issues Detected</h3>
        <p>There are <?= $stats['accepted'] ?> accepted requests that should be cleaned up.</p>
        <p>There are <?= $stats['rejected'] ?> rejected requests in the database.</p>
        
        <form method="POST" style="margin-top: 15px;">
            <button type="submit" name="cleanup" class="btn btn-primary">
                🧹 Run Cleanup
            </button>
        </form>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($inconsistencies)): ?>
    <div style="background: var(--bg-elevated); padding: 20px; border-radius: 12px; margin: 20px 0;">
        <h2>Old/Inconsistent Requests</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--divider);">
                    <th style="padding: 10px; text-align: left;">ID</th>
                    <th style="padding: 10px; text-align: left;">From</th>
                    <th style="padding: 10px; text-align: left;">To</th>
                    <th style="padding: 10px; text-align: left;">Status</th>
                    <th style="padding: 10px; text-align: left;">Are Friends?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inconsistencies as $row): ?>
                <tr style="border-bottom: 1px solid var(--divider);">
                    <td style="padding: 10px;"><?= $row['id'] ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($row['sender_name']) ?></td>
                    <td style="padding: 10px;"><?= htmlspecialchars($row['receiver_name']) ?></td>
                    <td style="padding: 10px;"><?= $row['status'] ?></td>
                    <td style="padding: 10px;"><?= $row['is_friend'] ? 'Yes' : 'No' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <p><a href="index.php?page=profile" class="btn btn-outline">← Back to Profile</a></p>
</div>

<?php include 'src/views/footer.php'; ?>
