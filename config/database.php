<?php

// Database configuration
// Use environment variables from .env file
define('DB_HOST', $_ENV['DB_HOST'] ?? 'db');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'camagru');
define('DB_USER', $_ENV['DB_USER'] ?? 'camagru_user');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? 'camagru_pass');
define('DB_CHARSET', 'utf8mb4');

// Database connection
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=3306;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
