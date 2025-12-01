<?php
// Test email configuration
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== Email Configuration Test ===\n\n";

echo "SMTP_HOST: " . ($_ENV['SMTP_HOST'] ?? 'NOT SET') . "\n";
echo "SMTP_PORT: " . ($_ENV['SMTP_PORT'] ?? 'NOT SET') . "\n";
echo "SMTP_USERNAME: " . ($_ENV['SMTP_USERNAME'] ?? 'NOT SET') . "\n";
echo "SMTP_PASSWORD: " . (isset($_ENV['SMTP_PASSWORD']) ? str_repeat('*', strlen($_ENV['SMTP_PASSWORD'])) : 'NOT SET') . "\n";
echo "SMTP_PASSWORD length: " . (isset($_ENV['SMTP_PASSWORD']) ? strlen($_ENV['SMTP_PASSWORD']) : 0) . " characters\n";
echo "SMTP_FROM: " . ($_ENV['SMTP_FROM'] ?? 'NOT SET') . "\n\n";

echo "=== Testing SMTP Connection ===\n";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['SMTP_PORT'];
    
    // Try to send a test email
    $mail->setFrom($_ENV['SMTP_FROM'], 'Camagru Test');
    $mail->addAddress($_ENV['SMTP_USERNAME']); // Send to yourself
    
    $mail->Subject = 'Test Email from Camagru';
    $mail->Body = 'This is a test email. If you received this, email is working!';
    
    $mail->send();
    echo "\n\n✅ SUCCESS! Email sent successfully!\n";
} catch (Exception $e) {
    echo "\n\n❌ ERROR: Could not send email.\n";
    echo "Error: {$mail->ErrorInfo}\n";
    echo "\nException: " . $e->getMessage() . "\n";
}

echo "\n=== Troubleshooting Tips ===\n";
echo "1. Make sure you're using an App Password, not your regular Gmail password\n";
echo "2. Go to: https://myaccount.google.com/apppasswords\n";
echo "3. Generate a NEW app password\n";
echo "4. Copy it WITHOUT spaces: ezrlrgjiaupgkaki (16 characters)\n";
echo "5. Update .env file with the new password\n";
echo "6. Make sure 2-Step Verification is enabled on your Google account\n";
