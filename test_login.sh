#!/bin/bash
# Quick login test script

echo "=== Camagru Login Tester ==="
echo ""
echo "Enter email:"
read EMAIL

echo "Enter password:"
read -s PASSWORD

echo ""
echo "Testing login..."
echo ""

docker exec camagru-web php -r "
require 'config/database.php';
require 'src/models/User.php';

\$userModel = new User();
\$user = \$userModel->findByEmail('$EMAIL');

if (!\$user) {
    echo '❌ ERROR: Email not found in database\n';
    echo '   Solution: Make sure you registered with this email\n';
    exit(1);
}

echo '✓ Email found: ' . \$user['email'] . '\n';
echo '  Username: ' . \$user['username'] . '\n';
echo '  Verified: ' . (\$user['verified'] ? 'Yes' : 'No') . '\n\n';

if (password_verify('$PASSWORD', \$user['password'])) {
    echo '✅ PASSWORD CORRECT!\n\n';
    if (!\$user['verified']) {
        echo '⚠ But account is NOT verified.\n';
        echo '  You will see: \"Please verify your email before logging in.\"\n\n';
        echo '  To fix, run:\n';
        echo '  docker exec camagru-mysql mysql -u root -prootpass camagru -e \"UPDATE users SET verified = 1 WHERE email = \'$EMAIL\';\"\n';
    } else {
        echo '✅ Account is verified. Login should work!\n';
    }
} else {
    echo '❌ PASSWORD INCORRECT!\n';
    echo '   This is why you see: \"Invalid email or password.\"\n\n';
    echo '   To reset password to \"Test123!\", run:\n';
    echo '   docker exec camagru-web php -r \"require config/database.php; \\$pdo = getDBConnection(); \\$hash = password_hash(Test123!, PASSWORD_DEFAULT); \\$pdo->prepare(UPDATE users SET password = ? WHERE email = ?)->execute([\\$hash, $EMAIL]); echo Password updated\\\\n;\"\n';
}
"
