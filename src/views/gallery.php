<?php 
$title = 'Gallery - Camagru';
include 'header.php'; 
require_once 'src/models/Comment.php';
require_once 'src/models/Like.php';
$commentModel = new Comment();
$likeModel = new Like();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<h1>Community Feed</h1>
<p class="page-subtitle">Fresh drops from the Camagru community. Show some love and keep the convo going.</p>

<?php if (empty($images)): ?>
    <div class="empty-feed">
        <p>No photos yet. Be the first to share a moment!</p>
        <?php if ($isLoggedIn): ?>
            <a href="index.php?page=create" class="btn btn-primary">Create a Post</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <section class="feed">
        <?php foreach ($images as $image): ?>
            <?php 
                $comments = $commentModel->getByImage($image['id']);
                $hasLiked = $isLoggedIn ? $likeModel->hasUserLiked($image['id'], $_SESSION['user_id']) : false;
                $userInitial = strtoupper(substr($image['username'], 0, 1));
            ?>
            <article class="feed-card">
                <header class="feed-card__header">
                    <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars($userInitial); ?></div>
                    <div class="author-meta">
                        <a class="username" href="index.php?page=profile&user_id=<?php echo $image['user_id']; ?>">
                            <?php echo htmlspecialchars($image['username']); ?>
                        </a>
                        <span class="timestamp"><?php echo date('M d, Y \a\t H:i', strtotime($image['created_at'])); ?></span>
                    </div>
                </header>

                <div class="feed-card__media">
                    <img src="public/uploads/<?php echo htmlspecialchars($image['filename']); ?>" 
                         alt="Image by <?php echo htmlspecialchars($image['username']); ?>">
                </div>

                <div class="feed-card__body">
                    <div class="feed-card__stats">
                        <span>❤️ <strong><?php echo $image['like_count']; ?></strong> likes</span>
                        <span>💬 <strong><?php echo $image['comment_count']; ?></strong> comments</span>
                    </div>

                    <?php if ($isLoggedIn): ?>
                        <div class="reaction-actions">
                            <form method="POST" action="index.php?page=create" class="inline-form">
                                <input type="hidden" name="action" value="toggle_like">
                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                <button type="submit" class="reaction-btn <?php echo $hasLiked ? 'active' : ''; ?>">
                                    <?php echo $hasLiked ? '💜 Liked' : '🤍 Like'; ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="comments-section">
                        <h4>Comments</h4>
                        <?php if (empty($comments)): ?>
                            <p class="no-comments">Be the first to comment.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment">
                                    <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                    <small><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($isLoggedIn): ?>
                            <form method="POST" action="index.php?page=create" class="comment-form">
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                <textarea name="comment" placeholder="Add a comment..." required></textarea>
                                <button type="submit" class="btn btn-small">Post</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=gallery&p=<?php echo $i; ?>" 
               class="<?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
