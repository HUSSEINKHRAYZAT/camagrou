<?php

// General configuration
define('SITE_NAME', 'Camagru');
define('BASE_URL', 'http://localhost:8080');

// Security
session_start();
define('SESSION_LIFETIME', 3600); // 1 hour

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM', 'noreply@camagru.com');

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Pagination
define('ITEMS_PER_PAGE', 5);

// Include database configuration
require_once __DIR__ . '/database.php';
