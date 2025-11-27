<?php
/**
 * User Verification Script
 * Usage: php verify_user.php [email or username]
 * Example: php verify_user.php admin@admin.com
 * Example: php verify_user.php hussein
 */

require_once 'config/database.php';

// Check if an argument was provided
if ($argc < 2) {
    echo "Usage: php verify_user.php [email or username]\n";
    echo "Examples:\n";
    echo "  php verify_user.php admin@admin.com\n";
    echo "  php verify_user.php hussein\n";
    echo "  php verify_user.php --all (verify all users)\n";
    echo "  php verify_user.php --list (list all users)\n";
    exit(1);
}

$identifier = $argv[1];

try {
    $pdo = getDBConnection();
    
    // Special commands
    if ($identifier === '--list') {
        echo "\n=== All Users ===\n";
        $stmt = $pdo->query("SELECT id, username, email, verified, created_at FROM users ORDER BY id");
        $users = $stmt->fetchAll();
        
        if (empty($users)) {
            echo "No users found.\n";
        } else {
            printf("%-5s %-20s %-30s %-10s %s\n", "ID", "Username", "Email", "Verified", "Created At");
            echo str_repeat("-", 90) . "\n";
            foreach ($users as $user) {
                printf(
                    "%-5s %-20s %-30s %-10s %s\n",
                    $user['id'],
                    $user['username'],
                    $user['email'],
                    $user['verified'] ? 'YES' : 'NO',
                    $user['created_at']
                );
            }
        }
        exit(0);
    }
    
    if ($identifier === '--all') {
        $stmt = $pdo->prepare("UPDATE users SET verified = 1 WHERE verified = 0");
        $stmt->execute();
        $count = $stmt->rowCount();
        echo "✓ Verified $count user(s).\n";
        exit(0);
    }
    
    // Check if it's an email or username
    if (strpos($identifier, '@') !== false) {
        // It's an email
        $stmt = $pdo->prepare("SELECT id, username, email, verified FROM users WHERE email = ?");
        $stmt->execute([$identifier]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "✗ User with email '$identifier' not found.\n";
            exit(1);
        }
        
        if ($user['verified']) {
            echo "✓ User '{$user['username']}' ({$user['email']}) is already verified.\n";
            exit(0);
        }
        
        // Verify the user
        $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE email = ?");
        $stmt->execute([$identifier]);
        
        echo "✓ Successfully verified user '{$user['username']}' ({$user['email']})!\n";
        
    } else {
        // It's a username
        $stmt = $pdo->prepare("SELECT id, username, email, verified FROM users WHERE username = ?");
        $stmt->execute([$identifier]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo "✗ User with username '$identifier' not found.\n";
            exit(1);
        }
        
        if ($user['verified']) {
            echo "✓ User '{$user['username']}' ({$user['email']}) is already verified.\n";
            exit(0);
        }
        
        // Verify the user
        $stmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE username = ?");
        $stmt->execute([$identifier]);
        
        echo "✓ Successfully verified user '{$user['username']}' ({$user['email']})!\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
