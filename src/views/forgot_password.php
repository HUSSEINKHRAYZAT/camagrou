<?php 
$title = 'Forgot Password - Camagru';
include 'header.php'; 
?>

<div class="auth-form">
    <h2>Reset password</h2>
    <form method="POST" action="index.php?page=forgot_password">
        <div class="form-group">
            <label for="fp_email">Email:</label>
            <input type="email" id="fp_email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary">Send reset link</button>
    </form>
    <p class="form-footer">
        Remembered? <a href="index.php?page=login">Go back to login</a>
    </p>
</div>

<?php include 'footer.php'; ?>
