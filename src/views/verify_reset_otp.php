<?php 
$title = 'Verify Reset Code - Camagru';
require_once 'src/views/header.php'; 
?>

<div class="auth-container" style="max-width: 500px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1);">
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 64px; margin-bottom: 10px;">🔐</div>
        <h2 style="color: #667eea; margin: 0;">Verify Reset Code</h2>
        <p style="color: #666; margin-top: 10px;">Enter the 6-digit code sent to your email</p>
    </div>

    <!-- OTP Verification Form -->
    <form method="POST" action="index.php?page=verify_reset_otp" id="otpForm" style="margin-bottom: 30px;">
        <div class="form-group" style="margin-bottom: 20px;">
            <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Email Address</label>
            <input 
                type="email" 
                name="email" 
                id="email" 
                class="form-control" 
                value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>"
                required
                style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box;"
                placeholder="your@email.com"
            >
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="otp_code" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Verification Code</label>
            <input 
                type="text" 
                name="otp_code" 
                id="otp_code" 
                class="form-control" 
                required
                maxlength="6"
                pattern="\d{6}"
                style="width: 100%; padding: 20px; border: 2px solid #667eea; border-radius: 6px; font-size: 32px; text-align: center; letter-spacing: 10px; font-family: 'Courier New', monospace; box-sizing: border-box;"
                placeholder="000000"
                autocomplete="off"
            >
            <small style="color: #888; font-size: 12px; display: block; margin-top: 5px;">Enter the 6-digit code from your email</small>
        </div>

        <button 
            type="submit" 
            class="btn btn-primary" 
            style="width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s;"
            onmouseover="this.style.transform='scale(1.02)'"
            onmouseout="this.style.transform='scale(1)'"
        >
            Verify Code
        </button>
    </form>

    <!-- Resend Code Section -->
    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 6px; margin-top: 20px;">
        <p style="margin: 0 0 15px 0; color: #666;">Didn't receive the code?</p>
        <form method="POST" action="index.php?page=resend_reset_otp" style="display: inline;">
            <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?>">
            <button 
                type="submit" 
                class="btn btn-secondary"
                style="padding: 10px 30px; background: white; color: #667eea; border: 2px solid #667eea; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; transition: all 0.2s;"
                onmouseover="this.style.background='#667eea'; this.style.color='white'"
                onmouseout="this.style.background='white'; this.style.color='#667eea'"
            >
                Resend Code
            </button>
        </form>
    </div>

    <!-- Info Box -->
    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
        <p style="margin: 0; font-size: 14px; color: #666;"><strong>⏰ Note:</strong> Verification codes expire after 15 minutes for security.</p>
    </div>

    <!-- Back to Login -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="index.php?page=login" style="color: #667eea; text-decoration: none; font-size: 14px;">← Back to Login</a>
    </div>
</div>

<script>
// Auto-focus on OTP input
document.getElementById('otp_code').focus();

// Auto-submit when 6 digits entered
document.getElementById('otp_code').addEventListener('input', function(e) {
    if (this.value.length === 6) {
        // Small delay to show the last digit
        setTimeout(() => {
            document.getElementById('otpForm').submit();
        }, 300);
    }
});
</script>

<?php require_once 'src/views/footer.php'; ?>
