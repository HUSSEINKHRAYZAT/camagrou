<?php
/**
 * Debug Login Issues
 * This script helps identify why login might be failing
 */

require_once 'config/database.php';

echo "=== CAMAGRU LOGIN DEBUGGER ===\n\n";

// Get all users
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT id, username, email, verified FROM users ORDER BY id");
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "❌ No users found in database!\n";
    echo "   Please register a user first through the web interface.\n";
    exit(1);
}

echo "📋 USERS IN DATABASE:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s %-20s %-30s %-10s\n", "ID", "Username", "Email", "Verified");
echo str_repeat("-", 80) . "\n";

foreach ($users as $user) {
    $verified = $user['verified'] ? '✓ Yes' : '✗ No';
    printf("%-5d %-20s %-30s %-10s\n", 
        $user['id'], 
        $user['username'], 
        $user['email'], 
        $verified
    );
}

echo str_repeat("-", 80) . "\n\n";

// Check for unverified users
$unverified = array_filter($users, function($u) { return !$u['verified']; });
if (!empty($unverified)) {
    echo "⚠ WARNING: " . count($unverified) . " unverified user(s) found!\n";
    echo "   These users cannot log in until verified.\n";
    echo "   To manually verify a user, run:\n";
    echo "   docker exec camagru-web php verify_user.php <email>\n\n";
}

// Password test section
echo "\n🔐 PASSWORD TESTING:\n";
echo "To test if a password is correct, we need to check the hash.\n";
echo "Enter an email to test: ";
$test_email = trim(fgets(STDIN));

if (empty($test_email)) {
    echo "No email provided. Exiting.\n";
    exit(0);
}

$stmt = $pdo->prepare("SELECT id, username, email, password, verified FROM users WHERE email = ?");
$stmt->execute([$test_email]);
$user = $stmt->fetch();

if (!$user) {
    echo "❌ User not found: $test_email\n";
    exit(1);
}

echo "\n✓ User found: {$user['username']} ({$user['email']})\n";
echo "  Verified: " . ($user['verified'] ? 'Yes' : 'No') . "\n";
echo "  Hash: " . substr($user['password'], 0, 40) . "...\n\n";

echo "Enter password to test: ";
$test_password = trim(fgets(STDIN));

if (password_verify($test_password, $user['password'])) {
    echo "\n✅ PASSWORD MATCHES!\n";
    if ($user['verified']) {
        echo "✅ Account is verified. Login should work!\n";
    } else {
        echo "⚠ Account is NOT verified. You'll see: 'Please verify your email before logging in.'\n";
    }
} else {
    echo "\n❌ PASSWORD DOES NOT MATCH!\n";
    echo "This is why you're getting: 'Invalid email or password.'\n";
    echo "\nPossible reasons:\n";
    echo "1. You're entering the wrong password\n";
    echo "2. There was an error during registration\n";
    echo "3. Password was not properly hashed\n";
}
