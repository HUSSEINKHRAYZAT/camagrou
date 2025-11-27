<?php
require_once 'config/database.php';

$email = 'admin@admin.com';
$new_password = 'Admin123!';

echo "=== Password Update Verification ===\n\n";

$pdo = getDBConnection();

// Update password
$hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
$result = $stmt->execute([$hash, $email]);

if ($result) {
    echo "✅ Password updated successfully!\n\n";
    
    // Verify it works
    $stmt = $pdo->prepare('SELECT password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (password_verify($new_password, $user['password'])) {
        echo "✅ Password verification successful!\n\n";
        echo "You can now login with:\n";
        echo "  📧 Email: $email\n";
        echo "  🔑 Password: $new_password\n\n";
        echo "🌐 Login at: http://localhost:8080\n";
    } else {
        echo "❌ Verification failed!\n";
    }
} else {
    echo "❌ Failed to update password!\n";
}
