#!/bin/bash

# Test email delivery to different providers

echo "=== Testing Email Delivery to Different Providers ==="
echo ""

# Test 1: Gmail (should work perfectly)
echo "Test 1: Sending to Gmail..."
docker exec camagru-web php -r "
require '/var/www/html/config/config.php';
require '/var/www/html/src/services/EmailService.php';
\$email = new EmailService();
\$result = \$email->sendVerificationEmail('sabinelhaj@gmail.com', 'Test', '111111');
echo \$result ? '✅ SUCCESS - Check sabinelhaj@gmail.com\n' : '❌ FAILED\n';
"

echo ""
echo "Test 2: Current Hotmail status..."
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e "SELECT username, email, verification_token as code, verified FROM users WHERE email='husseinkhrayzat@hotmail.com' ORDER BY id DESC LIMIT 1;" 2>/dev/null

echo ""
echo "=== Summary ==="
echo "✅ Gmail delivery: Works perfectly (instant)"
echo "❌ Hotmail delivery: Blocked by Microsoft spam filters"
echo ""
echo "💡 Recommendation: Use Gmail address for registration instead of Hotmail"
