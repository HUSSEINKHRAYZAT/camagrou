<?php 
$title = 'Forgot Password - Camagru';
include 'header.php'; 
?>

<div class="auth-container" style="max-width: 500px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 64px; margin-bottom: 10px;">🔑</div>
        <h2 style="color: #667eea; margin: 0;">Reset Your Password</h2>
        <p style="color: #666; margin-top: 10px;">Enter your email to receive a verification code</p>
    </div>

    <form method="POST" action="index.php?page=forgot_password">
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Email Address</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                required
                style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                placeholder="your@email.com"
            >
            <small style="color: #888; font-size: 12px; display: block; margin-top: 5px;">
                We'll send you a 6-digit verification code
            </small>
        </div>

        <button 
            type="submit" 
            class="btn btn-primary" 
            style="width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='scale(1.02)'"
            onmouseout="this.style.transform='scale(1)'"
        >
            Send Verification Code
        </button>
    </form>

    <!-- Info Box -->
    <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">
        <p style="margin: 0; font-size: 14px; color: #666;">
            <strong>ℹ️ How it works:</strong><br>
            1. Enter your email address<br>
            2. Receive a 6-digit code<br>
            3. Enter the code on the next page<br>
            4. Set your new password
        </p>
    </div>

    <!-- Back to Login -->
    <div style="text-align: center; margin-top: 30px;">
        <p style="color: #666; font-size: 14px;">
            Remembered your password? 
            <a href="index.php?page=login" style="color: #667eea; text-decoration: none; font-weight: bold;">Back to Login</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>
