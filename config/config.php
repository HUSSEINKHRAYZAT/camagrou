<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$envPath = __DIR__ . '/..';
try {
    if (file_exists($envPath . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable($envPath);
        $repository = $dotenv->load();
    }
} catch (Exception $e) {
    error_log('Failed to load .env file: ' . $e->getMessage());
}

// General configuration
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'Camagru');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost:8080');

// Security
session_start();
define('SESSION_LIFETIME', $_ENV['SESSION_LIFETIME'] ?? 3600);

// Email configuration
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_FROM', $_ENV['SMTP_FROM'] ?? 'noreply@camagru.com');

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Pagination
define('ITEMS_PER_PAGE', 5);

// Include database configuration
require_once __DIR__ . '/database.php';
