<?php 
$title = 'Set New Password - Camagru';
include 'header.php'; 
?>

<div class="auth-container" style="max-width: 500px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 64px; margin-bottom: 10px;">🔒</div>
        <h2 style="color: #667eea; margin: 0;">Set New Password</h2>
        <p style="color: #666; margin-top: 10px;">Choose a strong password for your account</p>
    </div>

    <form method="POST" action="index.php?page=reset_password">
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="password" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">New Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required
                style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                placeholder="Enter new password"
            >
            <small style="color: #888; font-size: 12px; display: block; margin-top: 5px;">
                At least 8 characters with uppercase, lowercase, and number
            </small>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="confirm_password" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Confirm Password</label>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required
                style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                placeholder="Confirm new password"
            >
        </div>

        <button 
            type="submit" 
            class="btn btn-primary" 
            style="width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='scale(1.02)'"
            onmouseout="this.style.transform='scale(1)'"
        >
            Update Password
        </button>
    </form>

    <!-- Back to Login -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="index.php?page=login" style="color: #667eea; text-decoration: none; font-size: 14px;">← Back to Login</a>
    </div>
</div>

<?php include 'footer.php'; ?>
