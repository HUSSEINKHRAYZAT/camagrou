<?php
/**
 * Database Migration - Add OTP Expiry Column
 * Run this to add OTP expiry support to the users table
 */

require_once 'config/database.php';

echo "========================================\n";
echo "DATABASE MIGRATION: Add OTP Support\n";
echo "========================================\n\n";

try {
    $pdo = getDBConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "[1/2] Checking if otp_expiry column exists...\n";
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'otp_expiry'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "   ⚠️  Column 'otp_expiry' already exists. Skipping...\n";
    } else {
        echo "[2/2] Adding otp_expiry column to users table...\n";
        $pdo->exec("
            ALTER TABLE users 
            ADD COLUMN otp_expiry DATETIME NULL AFTER verification_token
        ");
        echo "   ✓ Column added successfully\n";
    }
    
    echo "\n========================================\n";
    echo "✅ Migration completed successfully!\n";
    echo "========================================\n";
    
} catch (PDOException $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
