<?php 
$title = 'Set New Password - Camagru';
include 'header.php'; 
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
?>

<div class="auth-form">
    <h2>Choose a new password</h2>
    <form method="POST" action="index.php?page=reset_password">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="new_password">New password:</label>
            <input type="password" id="new_password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</div>

<?php include 'footer.php'; ?>
