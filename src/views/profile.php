<?php 
$title = 'Profile - Camagru';
include 'header.php'; 
$isOwnProfile = ($user['id'] == $_SESSION['user_id']);
$relationship = $relationship ?? ['status' => 'none'];
$postCount = count($images);
$friendCount = $friendCount ?? ($user['friend_count'] ?? 0);
$bio = $user['bio'] ?? 'Add a short bio to tell the world about you.';
$avatarLetter = strtoupper(substr($user['username'], 0, 1));
$hasStory = false;
$avatarSrc = $user['avatar'] ?? '';
$suggestions = $suggestions ?? [];
$pendingRequests = $pendingRequests ?? [];
$activeStory = $activeStory ?? null;
$canViewStory = $isOwnProfile || (($relationship['status'] ?? '') === 'friends');
?>

<section class="profile-hero">
    <div class="profile-avatar">
        <img id="profileAvatarImg" class="profile-avatar__img" src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="Profile avatar" <?php echo empty($avatarSrc) ? 'style="display:none;"' : ''; ?>>
        <span class="profile-avatar__initial" aria-hidden="true"><?php echo htmlspecialchars($avatarLetter); ?></span>
        <?php if ($isOwnProfile): ?>
            <button type="button" class="btn btn-outline btn-small profile-avatar__btn" id="openEditProfile">Edit Profile</button>
        <?php endif; ?>
    </div>
    <div class="profile-meta">
        <div class="profile-header-row">
            <div>
                <h1 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="profile-bio" id="profileBio"><?php echo htmlspecialchars($bio); ?></p>
            </div>
            <div class="profile-actions">
                <?php if ($isOwnProfile): ?>
                    <a href="index.php?page=create" class="btn btn-primary">Create</a>
                <?php else: ?>
                    <?php if ($relationship['status'] === 'friends'): ?>
                        <form method="POST" action="index.php?page=profile&user_id=<?php echo $user['id']; ?>" class="inline-form">
                            <input type="hidden" name="action" value="unfriend">
                            <input type="hidden" name="friend_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-outline">Unfriend</button>
                        </form>
                    <?php elseif ($relationship['status'] === 'outgoing_pending'): ?>
                        <button type="button" class="btn btn-outline" disabled>Request Sent</button>
                    <?php elseif ($relationship['status'] === 'incoming_pending'): ?>
                        <form method="POST" action="index.php?page=profile" class="inline-form">
                            <input type="hidden" name="action" value="respond_friend_request">
                            <input type="hidden" name="request_id" value="<?php echo $relationship['request_id'] ?? 0; ?>">
                            <input type="hidden" name="decision" value="accept">
                            <button type="submit" class="btn btn-primary">Accept Request</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="index.php?page=profile&user_id=<?php echo $user['id']; ?>" class="inline-form">
                            <input type="hidden" name="action" value="send_friend_request">
                            <input type="hidden" name="recipient_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-primary">Add Friend</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="profile-stats">
            <div><strong><?php echo $postCount; ?></strong><span>Posts</span></div>
            <div><strong><?php echo $friendCount; ?></strong><span>Friends</span></div>
            <div><strong><?php echo $user['like_total'] ?? 0; ?></strong><span>Likes</span></div>
        </div>
    </div>
</section>

<?php if ($isOwnProfile): ?>
<div class="profile-edit-modal" id="profileEditModal" aria-hidden="true">
    <div class="profile-edit-backdrop" id="profileEditBackdrop"></div>
    <div class="profile-edit-card">
        <form id="profileEditForm" method="POST" action="index.php?page=profile" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            <div class="profile-edit-header">
                <h3>Edit Profile</h3>
                <button type="button" class="btn btn-outline btn-small" id="closeEditProfile" type="button">Close</button>
            </div>
            <div class="profile-edit-body">
                <div class="form-group">
                    <label for="bioInput">Bio</label>
                    <textarea id="bioInput" name="bio" rows="3" placeholder="Share a short bio"><?php echo htmlspecialchars($bio); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Profile Photo</label>
                    <div class="avatar-picker">
                        <div class="avatar-preview">
                            <img id="avatarPreviewImg" src="<?php echo htmlspecialchars($avatarSrc); ?>" alt="Avatar preview" <?php echo empty($avatarSrc) ? 'style="display:none;"' : ''; ?>>
                            <span class="profile-avatar__initial"><?php echo htmlspecialchars($avatarLetter); ?></span>
                        </div>
                        <div class="avatar-options">
                            <div class="avatar-option-block">
                                <p class="tool-hint">Choose from your posts</p>
                                <div class="avatar-grid" id="avatarGrid">
                                    <?php foreach ($images as $image): ?>
                                        <button type="button" class="avatar-grid__item" data-src="public/uploads/<?php echo htmlspecialchars($image['filename']); ?>">
                                            <img src="public/uploads/<?php echo htmlspecialchars($image['filename']); ?>" alt="Choose avatar">
                                        </button>
                                    <?php endforeach; ?>
                                    <?php if (empty($images)): ?>
                                        <p class="tool-hint">No posts yet to pick from.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="avatar-option-block">
                                <p class="tool-hint">Upload a new photo</p>
                                <label class="btn btn-outline btn-small">
                                    Upload
                                    <input type="file" id="avatarUpload" name="avatar_file" accept="image/*" style="display:none;">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="profile-edit-footer">
                <button type="submit" class="btn btn-primary" id="saveProfileUi">Save</button>
            </div>
            <p class="tool-hint">Saved changes persist to your profile.</p>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($suggestions)): ?>
<section class="profile-suggestions">
    <div class="profile-section-header">
        <h3>Suggested Friends</h3>
        <p class="page-subtitle">People you might know, pulled from the community.</p>
    </div>
    <div class="suggestions-grid">
        <?php foreach ($suggestions as $suggestion): 
            $sAvatar = $suggestion['avatar'] ?? '';
            $sLetter = strtoupper(substr($suggestion['username'], 0, 1));
            ?>
            <div class="suggestion-card">
                <div class="suggestion-avatar">
                    <?php if (!empty($sAvatar)): ?>
                        <img src="<?php echo htmlspecialchars($sAvatar); ?>" alt="Profile">
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($sLetter); ?></span>
                    <?php endif; ?>
                </div>
                <div class="suggestion-meta">
                    <strong><?php echo htmlspecialchars($suggestion['username']); ?></strong>
                    <p class="page-subtitle"><?php echo htmlspecialchars($suggestion['bio'] ?? ''); ?></p>
                </div>
                <div class="suggestion-actions">
                    <a class="btn btn-outline btn-small" href="index.php?page=profile&user_id=<?php echo $suggestion['id']; ?>">View</a>
                    <form method="POST" action="index.php?page=profile&user_id=<?php echo $suggestion['id']; ?>" class="inline-form">
                        <input type="hidden" name="action" value="send_friend_request">
                        <input type="hidden" name="recipient_id" value="<?php echo $suggestion['id']; ?>">
                        <button type="submit" class="btn btn-primary btn-small">Add Friend</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="profile-stories">
    <h3>Stories</h3>
    <div class="stories-row">
        <?php if ($isOwnProfile): ?>
            <button type="button" class="story-ring story-ring--add" id="addStoryBtn">
                <span class="story-plus">+</span>
                <small id="addStoryLabel">Add Story</small>
            </button>
            <form method="POST" action="index.php?page=profile" enctype="multipart/form-data" id="storyForm">
                <input type="hidden" name="action" value="update_story">
                <input type="file" id="storyUpload" name="story_file" accept="image/*" style="display:none;">
            </form>
        <?php endif; ?>
        <button type="button" class="story-ring story-ring--active <?php echo ($activeStory && $canViewStory) ? 'has-story' : ''; ?>" id="storyActiveRing" data-story="<?php echo ($activeStory && $canViewStory) ? htmlspecialchars($activeStory['story_path']) : ''; ?>" aria-hidden="<?php echo ($activeStory && $canViewStory) ? 'false' : 'true'; ?>">
            <span class="story-thumb" id="storyThumb" style="<?php echo ($activeStory && $canViewStory) ? \"background-image:url('\".$activeStory['story_path'].\"');\" : 'display:none;'; ?>"></span>
            <span class="story-dot"></span>
            <small id="storyLabel">
                <?php 
                if ($activeStory && $canViewStory) {
                    echo 'View Story';
                } elseif ($activeStory) {
                    echo 'Stories are only visible to friends.';
                } else {
                    echo 'No story yet';
                }
                ?>
            </small>
        </button>
        <p class="story-empty">Stories last 24 hours.</p>
    </div>
    <div class="story-viewer" id="storyViewer" aria-hidden="true">
        <div class="story-viewer__backdrop"></div>
        <div class="story-viewer__content">
            <div class="story-progress"><div class="story-progress__bar" id="storyProgressBar"></div></div>
            <img id="storyViewerImg" alt="Story">
        </div>
    </div>
</section>

<?php if (!$isOwnProfile && ($relationship['status'] ?? '') === 'friends'): ?>
<section class="profile-messages">
    <div class="profile-section-header">
        <h3>Messages</h3>
        <p class="page-subtitle">Chat with <?php echo htmlspecialchars($user['username']); ?></p>
    </div>
    <div class="messages-thread">
        <?php if (empty($messages)): ?>
            <p class="tool-hint">No messages yet. Say hello!</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): 
                $isMine = ($msg['sender_id'] == $_SESSION['user_id']);
            ?>
                <div class="message-row <?php echo $isMine ? 'message-row--mine' : 'message-row--theirs'; ?>">
                    <div class="message-bubble">
                        <p><?php echo nl2br(htmlspecialchars($msg['body'])); ?></p>
                        <small><?php echo htmlspecialchars(date('M d, H:i', strtotime($msg['created_at']))); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <form method="POST" action="index.php?page=profile&user_id=<?php echo $user['id']; ?>" class="message-form">
        <input type="hidden" name="action" value="send_message">
        <input type="hidden" name="recipient_id" value="<?php echo $user['id']; ?>">
        <textarea name="message_body" rows="2" placeholder="Type a message" required></textarea>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</section>
<?php endif; ?>

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
    <div class="profile-settings">
        <h3>Account</h3>
        <form method="POST" action="index.php?page=profile">
            <input type="hidden" name="action" value="update_account">
            <div class="form-group">
                <label for="new_username">Username</label>
                <input type="text" id="new_username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_email">Email</label>
                <input type="email" id="new_email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="new_password">New password (optional)</label>
                <input type="password" id="new_password" name="password" placeholder="Leave blank to keep current">
            </div>
            <button type="submit" class="btn btn-primary">Update Account</button>
        </form>
    </div>
    <?php if (!empty($pendingRequests)): ?>
    <div class="profile-settings">
        <h3>Friend Requests</h3>
        <div class="requests-list">
            <?php foreach ($pendingRequests as $req): ?>
                <div class="request-item">
                    <div class="suggestion-avatar">
                        <?php if (!empty($req['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($req['avatar']); ?>" alt="Profile">
                        <?php else: ?>
                            <span><?php echo strtoupper(substr($req['username'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="request-meta">
                        <strong><?php echo htmlspecialchars($req['username']); ?></strong>
                        <small><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($req['created_at']))); ?></small>
                    </div>
                    <div class="request-actions">
                        <form method="POST" action="index.php?page=profile">
                            <input type="hidden" name="action" value="respond_friend_request">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <input type="hidden" name="decision" value="accept">
                            <button type="submit" class="btn btn-primary btn-small">Accept</button>
                        </form>
                        <form method="POST" action="index.php?page=profile">
                            <input type="hidden" name="action" value="respond_friend_request">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <input type="hidden" name="decision" value="reject">
                            <button type="submit" class="btn btn-outline btn-small">Reject</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<div class="profile-section-header">
    <h3>Posts</h3>
    <p class="page-subtitle">Grid view of your latest moments.</p>
</div>

<?php if (!empty($friends)): ?>
<div class="profile-section-header">
    <h3>Friends</h3>
    <a class="btn btn-outline btn-small" href="#friends-list">View all</a>
</div>
<div class="suggestions-grid" id="friends-list">
    <?php foreach ($friends as $friend): ?>
        <div class="suggestion-card">
            <div class="suggestion-avatar">
                <?php if (!empty($friend['avatar'])): ?>
                    <img src="<?php echo htmlspecialchars($friend['avatar']); ?>" alt="Profile">
                <?php else: ?>
                    <span><?php echo strtoupper(substr($friend['username'], 0, 1)); ?></span>
                <?php endif; ?>
            </div>
            <div class="suggestion-meta">
                <strong><?php echo htmlspecialchars($friend['username']); ?></strong>
                <p class="page-subtitle"><?php echo htmlspecialchars($friend['bio'] ?? ''); ?></p>
            </div>
            <div class="suggestion-actions">
                <a class="btn btn-outline btn-small" href="index.php?page=profile&user_id=<?php echo $friend['id']; ?>">View</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

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
<?php if ($isOwnProfile): ?>
<script src="public/js/profile.js"></script>
<?php endif; ?>
