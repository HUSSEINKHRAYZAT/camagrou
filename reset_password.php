<?php
require_once 'config/database.php';

$email = 'admin@admin.com';
$new_password = 'Admin123!';

echo "Resetting password for: $email\n";

try {
    $pdo = getDBConnection();
    
    // Generate password hash
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $pdo->prepare('UPDATE users SET password = ?, verified = 1 WHERE email = ?');
    $result = $stmt->execute([$hash, $email]);
    
    if ($result) {
        // Verify it worked
        $stmt = $pdo->prepare('SELECT username, email, verified, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($new_password, $user['password'])) {
            echo "\n";
            echo "========================================\n";
            echo "✅ SUCCESS! Password has been reset!\n";
            echo "========================================\n";
            echo "\n";
            echo "Login credentials:\n";
            echo "  Username: {$user['username']}\n";
            echo "  Email: $email\n";
            echo "  Password: $new_password\n";
            echo "  Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
            echo "\n";
            echo "🌐 Login at: http://localhost:8080\n";
            echo "\n";
        } else {
            echo "❌ Password update verification failed!\n";
        }
    } else {
        echo "❌ Failed to update password!\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
