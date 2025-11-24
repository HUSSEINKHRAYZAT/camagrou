<?php 
$title = 'Profile - Camagru';
include 'header.php'; 
$isOwnProfile = ($user['id'] == $_SESSION['user_id']);
?>

<h1><?php echo htmlspecialchars($user['username']); ?>'s Profile</h1>

<?php if ($isOwnProfile): ?>
    <div class="profile-settings">
        <h3>Settings</h3>
        <form method="POST" action="index.php?page=profile">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="email_notifications" 
                           <?php echo $user['email_notifications'] ? 'checked' : ''; ?>>
                    Receive email notifications when someone comments on my images
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
<?php endif; ?>

<h3>Photos</h3>
<div class="user-gallery">
    <?php if (empty($images)): ?>
        <p>No images yet.</p>
    <?php else: ?>
        <?php foreach ($images as $image): ?>
            <div class="gallery-item">
                <img src="public/uploads/<?php echo htmlspecialchars($image['filename']); ?>" alt="User image">
                <div class="image-info">
                    <p class="date"><?php echo date('M d, Y', strtotime($image['created_at'])); ?></p>
                    <div class="image-stats">
                        <span>❤️ <?php echo $image['like_count']; ?></span>
                        <span>💬 <?php echo $image['comment_count']; ?></span>
                    </div>
                    
                    <?php if ($isOwnProfile): ?>
                        <form method="POST" action="index.php?page=create" class="inline-form">
                            <input type="hidden" name="action" value="delete_image">
                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-small" 
                                    onclick="return confirm('Are you sure you want to delete this image?')">
                                Delete
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
