<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing Environment Loading ===\n\n";

echo "1. File exists? ";
echo file_exists(__DIR__ . '/.env') ? "YES\n" : "NO\n";

echo "2. File readable? ";
echo is_readable(__DIR__ . '/.env') ? "YES\n" : "NO\n";

echo "3. Loading autoloader...\n";
require_once __DIR__ . '/vendor/autoload.php';
echo "   Autoloader loaded successfully\n";

echo "4. Creating Dotenv instance...\n";
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
echo "   Dotenv instance created\n";

echo "5. Loading .env file...\n";
try {
    $dotenv->load();
    echo "   .env file loaded successfully\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n6. Environment variables:\n";
echo "   DB_HOST from \$_ENV: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "   DB_HOST from getenv: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
echo "   SMTP_HOST from \$_ENV: " . ($_ENV['SMTP_HOST'] ?? 'NOT SET') . "\n";
echo "   SMTP_HOST from getenv: " . (getenv('SMTP_HOST') ?: 'NOT SET') . "\n";

echo "\n7. Testing via config.php...\n";
require_once __DIR__ . '/config/config.php';
echo "   DB_HOST constant: " . DB_HOST . "\n";
echo "   SMTP_HOST constant: " . SMTP_HOST . "\n";
echo "   SMTP_USERNAME constant: " . SMTP_USERNAME . "\n";

echo "\nTest complete!\n";
