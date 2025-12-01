<?php
require_once 'config/config.php';
require_once 'src/services/EmailService.php';

echo "=== Testing Email Service ===\n\n";

echo "1. SMTP Configuration:\n";
echo "   Host: " . SMTP_HOST . "\n";
echo "   Port: " . SMTP_PORT . "\n";
echo "   Username: " . SMTP_USERNAME . "\n";
echo "   Password: " . (strlen(SMTP_PASSWORD) > 0 ? "[SET - " . strlen(SMTP_PASSWORD) . " chars]" : "[NOT SET]") . "\n\n";

echo "2. Sending test email...\n";
$emailService = new EmailService();
$result = $emailService->sendVerificationEmail(
    'test@example.com',
    'TestUser',
    '123456'
);

echo "\n3. Result: " . ($result ? "✅ SUCCESS" : "❌ FAILED") . "\n";
echo "\nCheck logs above for SMTP debug output.\n";
