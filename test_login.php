<?php
// Test script to debug login issues
require_once 'config/database.php';

// Test credentials
$test_email = 'admin@admin.com';
$test_password = 'admin'; // Replace with the actual password used during registration

echo "=== Login Test ===\n\n";

// Connect to database
try {
    $pdo = getDBConnection();
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Fetch user by email
echo "Testing email: $test_email\n";
$stmt = $pdo->prepare("SELECT id, username, email, password, verified FROM users WHERE email = ?");
$stmt->execute([$test_email]);
$user = $stmt->fetch();

if (!$user) {
    echo "✗ User not found with email: $test_email\n";
    exit(1);
}

echo "✓ User found:\n";
echo "  - ID: {$user['id']}\n";
echo "  - Username: {$user['username']}\n";
echo "  - Email: {$user['email']}\n";
echo "  - Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
echo "  - Password hash: " . substr($user['password'], 0, 30) . "...\n\n";

// Test password verification
echo "Testing password: '$test_password'\n";
if (password_verify($test_password, $user['password'])) {
    echo "✓ Password verification SUCCESSFUL\n";
    
    if (!$user['verified']) {
        echo "⚠ Account is NOT verified - login would be blocked\n";
    } else {
        echo "✓ Account is verified - login should work\n";
    }
} else {
    echo "✗ Password verification FAILED\n";
    echo "  This means the password doesn't match the stored hash\n";
    echo "  Common causes:\n";
    echo "  - Wrong password being tested\n";
    echo "  - Password was stored without hashing\n";
    echo "  - Different hashing algorithm was used\n";
}

echo "\n=== Testing different passwords ===\n";
$test_passwords = ['admin', 'Admin123', 'admin123', 'password', 'Admin@123'];
foreach ($test_passwords as $pwd) {
    $result = password_verify($pwd, $user['password']) ? '✓ MATCH' : '✗ No match';
    echo "Password '$pwd': $result\n";
}
