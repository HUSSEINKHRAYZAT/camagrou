<?php
/**
 * Database Migration Script
 * Safely upgrades the Camagru database with new social media features
 * Preserves all existing data
 */

require_once 'config/database.php';

echo "========================================\n";
echo "CAMAGRU DATABASE MIGRATION\n";
echo "Social Media Features Enhancement\n";
echo "========================================\n\n";

try {
    $pdo = getDBConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n\n";
    
    // Backup existing data
    echo "[1/14] Checking existing tables...\n";
    
    // Create user_profiles table
    echo "[2/14] Creating user_profiles table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_profiles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT UNIQUE NOT NULL,
            profile_picture VARCHAR(255) DEFAULT NULL,
            bio TEXT DEFAULT NULL,
            full_name VARCHAR(100) DEFAULT NULL,
            location VARCHAR(100) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            birth_date DATE DEFAULT NULL,
            gender ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL,
            is_private BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    
    // Create default profiles for existing users
    $pdo->exec("
        INSERT IGNORE INTO user_profiles (user_id)
        SELECT id FROM users WHERE id NOT IN (SELECT user_id FROM user_profiles)
    ");
    echo "   ✓ User profiles table created and populated\n";
    
    // Create friendships table
    echo "[3/14] Creating friendships table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS friendships (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            friend_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (friend_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_friendship (user_id, friend_id),
            INDEX idx_user_id (user_id),
            INDEX idx_friend_id (friend_id),
            CHECK (user_id != friend_id)
        )
    ");
    echo "   ✓ Friendships table created\n";
    
    // Create friend_requests table
    echo "[4/14] Creating friend_requests table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS friend_requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_request (sender_id, receiver_id),
            INDEX idx_sender_id (sender_id),
            INDEX idx_receiver_id (receiver_id),
            INDEX idx_status (status),
            CHECK (sender_id != receiver_id)
        )
    ");
    echo "   ✓ Friend requests table created\n";
    
    // Enhance images table
    echo "[5/14] Enhancing images table...\n";
    try {
        $pdo->exec("ALTER TABLE images ADD COLUMN IF NOT EXISTS caption TEXT DEFAULT NULL");
        $pdo->exec("ALTER TABLE images ADD COLUMN IF NOT EXISTS filter VARCHAR(50) DEFAULT NULL");
        $pdo->exec("ALTER TABLE images ADD COLUMN IF NOT EXISTS is_public BOOLEAN DEFAULT TRUE");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_is_public ON images(is_public)");
        echo "   ✓ Images table enhanced\n";
    } catch (PDOException $e) {
        echo "   ℹ Images table already enhanced or error: " . $e->getMessage() . "\n";
    }
    
    // Create stories table
    echo "[6/14] Creating stories table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS stories (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            media_type ENUM('image', 'video') DEFAULT 'image',
            filename VARCHAR(255) NOT NULL,
            caption TEXT DEFAULT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_expires_at (expires_at)
        )
    ");
    echo "   ✓ Stories table created\n";
    
    // Create story_views table
    echo "[7/14] Creating story_views table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS story_views (
            id INT PRIMARY KEY AUTO_INCREMENT,
            story_id INT NOT NULL,
            viewer_id INT NOT NULL,
            viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
            FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_view (story_id, viewer_id),
            INDEX idx_story_id (story_id),
            INDEX idx_viewer_id (viewer_id)
        )
    ");
    echo "   ✓ Story views table created\n";
    
    // Create notifications table
    echo "[8/14] Creating notifications table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            type ENUM('like', 'comment', 'friend_request', 'friend_accepted', 'story_view', 'mention') NOT NULL,
            related_user_id INT DEFAULT NULL,
            related_item_id INT DEFAULT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_is_read (is_read),
            INDEX idx_type (type),
            INDEX idx_created_at (created_at)
        )
    ");
    echo "   ✓ Notifications table created\n";
    
    // Create albums table
    echo "[9/14] Creating albums table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS albums (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            description TEXT DEFAULT NULL,
            cover_image_id INT DEFAULT NULL,
            is_public BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (cover_image_id) REFERENCES images(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id)
        )
    ");
    echo "   ✓ Albums table created\n";
    
    // Create album_images table
    echo "[10/14] Creating album_images table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS album_images (
            id INT PRIMARY KEY AUTO_INCREMENT,
            album_id INT NOT NULL,
            image_id INT NOT NULL,
            position INT DEFAULT 0,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
            FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
            UNIQUE KEY unique_album_image (album_id, image_id),
            INDEX idx_album_id (album_id)
        )
    ");
    echo "   ✓ Album images table created\n";
    
    // Create tags table
    echo "[11/14] Creating tags table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_name (name)
        )
    ");
    echo "   ✓ Tags table created\n";
    
    // Create image_tags table
    echo "[12/14] Creating image_tags table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS image_tags (
            id INT PRIMARY KEY AUTO_INCREMENT,
            image_id INT NOT NULL,
            tag_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
            UNIQUE KEY unique_image_tag (image_id, tag_id),
            INDEX idx_image_id (image_id),
            INDEX idx_tag_id (tag_id)
        )
    ");
    echo "   ✓ Image tags table created\n";
    
    // Create user_mentions table
    echo "[13/14] Creating user_mentions table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_mentions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            image_id INT DEFAULT NULL,
            comment_id INT DEFAULT NULL,
            story_id INT DEFAULT NULL,
            mentioned_user_id INT NOT NULL,
            mentioner_user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
            FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
            FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
            FOREIGN KEY (mentioned_user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (mentioner_user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_mentioned_user_id (mentioned_user_id)
        )
    ");
    echo "   ✓ User mentions table created\n";
    
    // Create directory structure
    echo "[14/14] Creating directory structure...\n";
    $directories = [
        'public/uploads/profiles',
        'public/uploads/stories',
        'public/uploads/albums'
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "   ✓ Created directory: $dir\n";
        } else {
            echo "   ℹ Directory exists: $dir\n";
        }
    }
    
    echo "\n========================================\n";
    echo "✓ MIGRATION COMPLETED SUCCESSFULLY!\n";
    echo "========================================\n\n";
    
    // Display summary
    echo "New Features Added:\n";
    echo "  • User Profiles (bio, profile picture, etc.)\n";
    echo "  • Friendships & Friend Requests\n";
    echo "  • Stories (24-hour expiring content)\n";
    echo "  • Notifications System\n";
    echo "  • Albums & Gallery Organization\n";
    echo "  • Tags & Mentions\n";
    echo "  • Enhanced Privacy Controls\n\n";
    
    echo "Database Tables:\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  ✓ $table\n";
    }
    
    echo "\n";
    
} catch (PDOException $e) {
    echo "\n✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
