<?php
require_once 'config/config.php';
require_once 'src/services/EmailService.php';

echo "Testing Email Service\n\n";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "SMTP Password: " . (strlen(SMTP_PASSWORD) > 0 ? "[SET]" : "[NOT SET]") . "\n\n";

echo "Sending test email...\n";
$emailService = new EmailService();
$result = $emailService->sendVerificationEmail('test@example.com', 'TestUser', '123456');
echo "Result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
