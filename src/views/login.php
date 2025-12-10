<?php 
$title = 'Login - Camagru';
include 'header.php'; 
?>

<div class="auth-form">
    <h2>Login</h2>
    <form method="POST" action="index.php?page=login">
        <div class="form-group">
            <label for="identifier">Email or Username:</label>
            <input type="text" id="identifier" name="identifier" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    
    <p class="form-footer">
        Don't have an account? <a href="index.php?page=register">Register here</a>
    </p>
    <p class="form-footer">
        Forgot password? <a href="index.php?page=forgot_password">Reset it</a>
    </p>
</div>

<?php include 'footer.php'; ?>
