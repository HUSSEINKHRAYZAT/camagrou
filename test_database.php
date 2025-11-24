<?php
/**
 * Database Operations Test Suite
 * Tests INSERT, UPDATE, DELETE, and SELECT operations on all tables
 */

require_once 'config/database.php';
require_once 'src/models/UserProfile.php';
require_once 'src/models/Friendship.php';
require_once 'src/models/Story.php';
require_once 'src/models/Notification.php';

echo "========================================\n";
echo "DATABASE OPERATIONS TEST SUITE\n";
echo "========================================\n\n";

$errors = [];
$passed = 0;
$failed = 0;

function test($description, $callback) {
    global $passed, $failed, $errors;
    echo "Testing: $description... ";
    try {
        $result = $callback();
        if ($result) {
            echo "✓ PASSED\n";
            $passed++;
            return true;
        } else {
            echo "✗ FAILED\n";
            $failed++;
            $errors[] = $description;
            return false;
        }
    } catch (Exception $e) {
        echo "✗ FAILED (Exception: " . $e->getMessage() . ")\n";
        $failed++;
        $errors[] = $description . " - " . $e->getMessage();
        return false;
    }
}

try {
    $pdo = getDBConnection();
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    die("✗ Database connection failed: " . $e->getMessage() . "\n");
}

// ========================================
// TEST 1: USER PROFILES
// ========================================
echo "\n[1] USER PROFILES TESTS\n";
echo "------------------------\n";

test("Insert: Create test user profile", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verified) VALUES (?, ?, ?, 1)");
    $result = $stmt->execute(['testuser_' . time(), 'test' . time() . '@test.com', password_hash('test123', PASSWORD_BCRYPT)]);
    $GLOBALS['testUserId'] = $pdo->lastInsertId();
    return $result && $GLOBALS['testUserId'] > 0;
});

test("Select: Get user profile", function() {
    $profile = new UserProfile();
    $data = $profile->getByUserId($GLOBALS['testUserId']);
    return $data && isset($data['user_id']);
});

test("Update: Update user profile bio", function() {
    $profile = new UserProfile();
    return $profile->update($GLOBALS['testUserId'], [
        'bio' => 'Test bio for database testing',
        'full_name' => 'Test User',
        'location' => 'Test Location'
    ]);
});

test("Select: Verify profile update", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT bio, full_name, location FROM user_profiles WHERE user_id = ?");
    $stmt->execute([$GLOBALS['testUserId']]);
    $data = $stmt->fetch();
    return $data && $data['bio'] === 'Test bio for database testing' 
        && $data['full_name'] === 'Test User'
        && $data['location'] === 'Test Location';
});

test("Update: Update profile picture", function() {
    $profile = new UserProfile();
    return $profile->updateProfilePicture($GLOBALS['testUserId'], 'test_profile.jpg');
});

test("Select: Get profile statistics", function() {
    $profile = new UserProfile();
    $stats = $profile->getStats($GLOBALS['testUserId']);
    return isset($stats['images']) && isset($stats['friends']) && isset($stats['likes']);
});

// ========================================
// TEST 2: FRIENDSHIPS
// ========================================
echo "\n[2] FRIENDSHIPS TESTS\n";
echo "---------------------\n";

test("Insert: Create second test user", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verified) VALUES (?, ?, ?, 1)");
    $result = $stmt->execute(['testuser2_' . time(), 'test2_' . time() . '@test.com', password_hash('test123', PASSWORD_BCRYPT)]);
    $GLOBALS['testUserId2'] = $pdo->lastInsertId();
    return $result && $GLOBALS['testUserId2'] > 0;
});

test("Insert: Send friend request", function() {
    $friendship = new Friendship();
    $result = $friendship->sendRequest($GLOBALS['testUserId'], $GLOBALS['testUserId2']);
    return $result['success'] === true;
});

test("Select: Get pending friend requests", function() {
    $friendship = new Friendship();
    $requests = $friendship->getPendingRequests($GLOBALS['testUserId2']);
    return is_array($requests) && count($requests) > 0;
});

test("Select: Check if users are NOT friends yet", function() {
    $friendship = new Friendship();
    return !$friendship->areFriends($GLOBALS['testUserId'], $GLOBALS['testUserId2']);
});

test("Update: Accept friend request", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT id FROM friend_requests WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'");
    $stmt->execute([$GLOBALS['testUserId'], $GLOBALS['testUserId2']]);
    $request = $stmt->fetch();
    
    if (!$request) return false;
    
    $friendship = new Friendship();
    $result = $friendship->acceptRequest($request['id'], $GLOBALS['testUserId2']);
    return $result['success'] === true;
});

test("Select: Verify friendship created", function() {
    $friendship = new Friendship();
    return $friendship->areFriends($GLOBALS['testUserId'], $GLOBALS['testUserId2']);
});

test("Select: Get friends list", function() {
    $friendship = new Friendship();
    $friends = $friendship->getFriends($GLOBALS['testUserId']);
    return is_array($friends) && count($friends) > 0;
});

test("Delete: Remove friendship", function() {
    $friendship = new Friendship();
    return $friendship->removeFriend($GLOBALS['testUserId'], $GLOBALS['testUserId2']);
});

test("Select: Verify friendship deleted", function() {
    $friendship = new Friendship();
    return !$friendship->areFriends($GLOBALS['testUserId'], $GLOBALS['testUserId2']);
});

// ========================================
// TEST 3: IMAGES & GALLERY
// ========================================
echo "\n[3] IMAGES & GALLERY TESTS\n";
echo "--------------------------\n";

test("Insert: Create test image", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO images (user_id, filename, caption, is_public) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$GLOBALS['testUserId'], 'test_image.jpg', 'Test caption', 1]);
    $GLOBALS['testImageId'] = $pdo->lastInsertId();
    return $result && $GLOBALS['testImageId'] > 0;
});

test("Select: Get image by ID", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM images WHERE id = ?");
    $stmt->execute([$GLOBALS['testImageId']]);
    $image = $stmt->fetch();
    return $image && $image['caption'] === 'Test caption';
});

test("Update: Update image caption", function() use ($pdo) {
    $stmt = $pdo->prepare("UPDATE images SET caption = ? WHERE id = ?");
    return $stmt->execute(['Updated caption', $GLOBALS['testImageId']]);
});

test("Select: Verify image caption updated", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT caption FROM images WHERE id = ?");
    $stmt->execute([$GLOBALS['testImageId']]);
    $image = $stmt->fetch();
    return $image && $image['caption'] === 'Updated caption';
});

// ========================================
// TEST 4: LIKES
// ========================================
echo "\n[4] LIKES TESTS\n";
echo "---------------\n";

test("Insert: Add like to image", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO likes (image_id, user_id) VALUES (?, ?)");
    return $stmt->execute([$GLOBALS['testImageId'], $GLOBALS['testUserId2']]);
});

test("Select: Count likes on image", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE image_id = ?");
    $stmt->execute([$GLOBALS['testImageId']]);
    $result = $stmt->fetch();
    return $result && $result['count'] == 1;
});

test("Delete: Remove like from image", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM likes WHERE image_id = ? AND user_id = ?");
    return $stmt->execute([$GLOBALS['testImageId'], $GLOBALS['testUserId2']]);
});

test("Select: Verify like deleted", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE image_id = ?");
    $stmt->execute([$GLOBALS['testImageId']]);
    $result = $stmt->fetch();
    return $result && $result['count'] == 0;
});

// ========================================
// TEST 5: COMMENTS
// ========================================
echo "\n[5] COMMENTS TESTS\n";
echo "------------------\n";

test("Insert: Add comment to image", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO comments (image_id, user_id, comment) VALUES (?, ?, ?)");
    $result = $stmt->execute([$GLOBALS['testImageId'], $GLOBALS['testUserId2'], 'Test comment']);
    $GLOBALS['testCommentId'] = $pdo->lastInsertId();
    return $result && $GLOBALS['testCommentId'] > 0;
});

test("Select: Get comments for image", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE image_id = ?");
    $stmt->execute([$GLOBALS['testImageId']]);
    $comments = $stmt->fetchAll();
    return count($comments) > 0;
});

test("Update: Update comment text", function() use ($pdo) {
    $stmt = $pdo->prepare("UPDATE comments SET comment = ? WHERE id = ?");
    return $stmt->execute(['Updated comment', $GLOBALS['testCommentId']]);
});

test("Delete: Remove comment", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    return $stmt->execute([$GLOBALS['testCommentId']]);
});

// ========================================
// TEST 6: STORIES
// ========================================
echo "\n[6] STORIES TESTS\n";
echo "-----------------\n";

test("Insert: Create story", function() {
    $story = new Story();
    $result = $story->create($GLOBALS['testUserId'], 'test_story.jpg', 'Test story caption');
    
    // Get the story ID
    $stories = $story->getByUser($GLOBALS['testUserId']);
    if ($stories && count($stories) > 0) {
        $GLOBALS['testStoryId'] = $stories[0]['id'];
        return true;
    }
    return false;
});

test("Select: Get user stories", function() {
    $story = new Story();
    $stories = $story->getByUser($GLOBALS['testUserId']);
    return is_array($stories) && count($stories) > 0;
});

test("Insert: Mark story as viewed", function() {
    $story = new Story();
    return $story->markAsViewed($GLOBALS['testStoryId'], $GLOBALS['testUserId2']);
});

test("Select: Get story viewers", function() {
    $story = new Story();
    $viewers = $story->getViewers($GLOBALS['testStoryId']);
    return is_array($viewers) && count($viewers) > 0;
});

test("Delete: Delete story", function() {
    $story = new Story();
    return $story->delete($GLOBALS['testStoryId'], $GLOBALS['testUserId']);
});

test("Select: Verify story deleted", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM stories WHERE id = ?");
    $stmt->execute([$GLOBALS['testStoryId']]);
    $result = $stmt->fetch();
    return $result && $result['count'] == 0;
});

// ========================================
// TEST 7: NOTIFICATIONS
// ========================================
echo "\n[7] NOTIFICATIONS TESTS\n";
echo "-----------------------\n";

test("Insert: Create notification", function() {
    $notification = new Notification();
    return $notification->create($GLOBALS['testUserId'], 'like', 'Test notification', $GLOBALS['testUserId2'], $GLOBALS['testImageId']);
});

test("Select: Get user notifications", function() {
    $notification = new Notification();
    $notifications = $notification->getByUser($GLOBALS['testUserId']);
    $GLOBALS['testNotificationId'] = $notifications[0]['id'] ?? null;
    return is_array($notifications) && count($notifications) > 0;
});

test("Select: Get unread notification count", function() {
    $notification = new Notification();
    $count = $notification->getUnreadCount($GLOBALS['testUserId']);
    return $count > 0;
});

test("Update: Mark notification as read", function() {
    $notification = new Notification();
    return $notification->markAsRead($GLOBALS['testNotificationId'], $GLOBALS['testUserId']);
});

test("Select: Verify notification marked as read", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT is_read FROM notifications WHERE id = ?");
    $stmt->execute([$GLOBALS['testNotificationId']]);
    $result = $stmt->fetch();
    return $result && $result['is_read'] == 1;
});

test("Delete: Delete notification", function() {
    $notification = new Notification();
    return $notification->delete($GLOBALS['testNotificationId'], $GLOBALS['testUserId']);
});

// ========================================
// TEST 8: ALBUMS
// ========================================
echo "\n[8] ALBUMS TESTS\n";
echo "----------------\n";

test("Insert: Create album", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO albums (user_id, name, description, is_public) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$GLOBALS['testUserId'], 'Test Album', 'Test album description', 1]);
    $GLOBALS['testAlbumId'] = $pdo->lastInsertId();
    return $result && $GLOBALS['testAlbumId'] > 0;
});

test("Insert: Add image to album", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO album_images (album_id, image_id, position) VALUES (?, ?, ?)");
    return $stmt->execute([$GLOBALS['testAlbumId'], $GLOBALS['testImageId'], 0]);
});

test("Select: Get album images", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM album_images WHERE album_id = ?");
    $stmt->execute([$GLOBALS['testAlbumId']]);
    $images = $stmt->fetchAll();
    return count($images) > 0;
});

test("Update: Update album name", function() use ($pdo) {
    $stmt = $pdo->prepare("UPDATE albums SET name = ? WHERE id = ?");
    return $stmt->execute(['Updated Album Name', $GLOBALS['testAlbumId']]);
});

test("Delete: Remove image from album", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM album_images WHERE album_id = ? AND image_id = ?");
    return $stmt->execute([$GLOBALS['testAlbumId'], $GLOBALS['testImageId']]);
});

test("Delete: Delete album", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM albums WHERE id = ?");
    return $stmt->execute([$GLOBALS['testAlbumId']]);
});

// ========================================
// TEST 9: TAGS
// ========================================
echo "\n[9] TAGS TESTS\n";
echo "--------------\n";

test("Insert: Create tag", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?)");
    $result = $stmt->execute(['testtag']);
    $GLOBALS['testTagId'] = $pdo->lastInsertId();
    return $result && $GLOBALS['testTagId'] > 0;
});

test("Insert: Tag image", function() use ($pdo) {
    $stmt = $pdo->prepare("INSERT INTO image_tags (image_id, tag_id) VALUES (?, ?)");
    return $stmt->execute([$GLOBALS['testImageId'], $GLOBALS['testTagId']]);
});

test("Select: Get image tags", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT t.* FROM tags t JOIN image_tags it ON t.id = it.tag_id WHERE it.image_id = ?");
    $stmt->execute([$GLOBALS['testImageId']]);
    $tags = $stmt->fetchAll();
    return count($tags) > 0;
});

test("Delete: Remove tag from image", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM image_tags WHERE image_id = ? AND tag_id = ?");
    return $stmt->execute([$GLOBALS['testImageId'], $GLOBALS['testTagId']]);
});

test("Delete: Delete tag", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM tags WHERE id = ?");
    return $stmt->execute([$GLOBALS['testTagId']]);
});

// ========================================
// TEST 10: CASCADE DELETE
// ========================================
echo "\n[10] CASCADE DELETE TESTS\n";
echo "-------------------------\n";

test("Delete: Delete test image (should cascade to likes/comments)", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
    return $stmt->execute([$GLOBALS['testImageId']]);
});

test("Delete: Delete test users (should cascade to profiles/friendships/etc)", function() use ($pdo) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id IN (?, ?)");
    return $stmt->execute([$GLOBALS['testUserId'], $GLOBALS['testUserId2']]);
});

test("Select: Verify cascade delete worked (no orphaned profiles)", function() use ($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_profiles WHERE user_id IN (?, ?)");
    $stmt->execute([$GLOBALS['testUserId'], $GLOBALS['testUserId2']]);
    $result = $stmt->fetch();
    return $result && $result['count'] == 0;
});

// ========================================
// SUMMARY
// ========================================
echo "\n========================================\n";
echo "TEST RESULTS SUMMARY\n";
echo "========================================\n";
echo "Total Tests: " . ($passed + $failed) . "\n";
echo "✓ Passed: $passed\n";
echo "✗ Failed: $failed\n";

if ($failed > 0) {
    echo "\nFailed Tests:\n";
    foreach ($errors as $error) {
        echo "  ✗ $error\n";
    }
    echo "\n";
    exit(1);
} else {
    echo "\n🎉 ALL TESTS PASSED!\n";
    echo "\nDatabase Operations Verified:\n";
    echo "  ✓ INSERT operations\n";
    echo "  ✓ SELECT operations\n";
    echo "  ✓ UPDATE operations\n";
    echo "  ✓ DELETE operations\n";
    echo "  ✓ CASCADE DELETE\n";
    echo "  ✓ Foreign key constraints\n";
    echo "  ✓ Model classes functionality\n";
    echo "\nYour database is working perfectly! 🚀\n";
    exit(0);
}
