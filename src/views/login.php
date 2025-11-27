<?php 
$title = 'Login - Camagru';
include 'header.php'; 
?>

<div class="auth-form">
    <h2>Login</h2>
    <form method="POST" action="index.php?page=login">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
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
</div>

<?php include 'footer.php'; ?>
