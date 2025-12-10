<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = SMTP_PORT;
            
            // Enable debug output (0=off, 1=client, 2=client+server)
            $this->mailer->SMTPDebug  = 2; // Set to 2 for debugging
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("SMTP Debug (level $level): $str");
            };
            
            // Sender info
            $this->mailer->setFrom(SMTP_FROM, SITE_NAME);
            // Add Reply-To to improve deliverability
            $this->mailer->addReplyTo(SMTP_USERNAME, SITE_NAME . ' Support');
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Email configuration error: " . $e->getMessage());
        }
    }
    
    /**
     * Send verification email with OTP code to new users
     */
    public function sendVerificationEmail($email, $username, $otpCode) {
        try {
            // Log configuration for debugging
            error_log("=== EMAIL DEBUG ===");
            error_log("SMTP Host: " . SMTP_HOST);
            error_log("SMTP Port: " . SMTP_PORT);
            error_log("SMTP Username: " . SMTP_USERNAME);
            error_log("SMTP Password length: " . strlen(SMTP_PASSWORD));
            error_log("Sending to: " . $email);
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $username);
            
            $this->mailer->Subject = 'Verify Your ' . SITE_NAME . ' Account - OTP Code';
            
            $htmlBody = $this->getVerificationEmailTemplate($username, $otpCode);
            $textBody = $this->getVerificationEmailTextVersion($username, $otpCode);
            
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $result = $this->mailer->send();
            error_log("✅ Verification OTP email sent successfully to: " . $email . " with code: " . $otpCode);
            return $result;
        } catch (Exception $e) {
            error_log("❌ Failed to send verification email!");
            error_log("Error: " . $e->getMessage());
            error_log("SMTP ErrorInfo: " . $this->mailer->ErrorInfo);
            error_log("Exception trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $username, $token) {
        try {
            $resetUrl = BASE_URL . "/index.php?page=reset_password&token=" . $token;
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $username);
            
            $this->mailer->Subject = 'Reset Your ' . SITE_NAME . ' Password';
            
            $htmlBody = $this->getPasswordResetEmailTemplate($username, $resetUrl);
            $textBody = $this->getPasswordResetEmailTextVersion($username, $resetUrl);
            
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $result = $this->mailer->send();
            error_log("Password reset email sent to: " . $email);
            return $result;
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send password reset OTP code
     */
    public function sendPasswordResetOTP($email, $username, $otpCode) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $username);
            
            $this->mailer->Subject = 'Password Reset Code for ' . SITE_NAME;
            
            $htmlBody = $this->getPasswordResetOTPTemplate($username, $otpCode);
            $textBody = $this->getPasswordResetOTPTextVersion($username, $otpCode);
            
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $result = $this->mailer->send();
            error_log("✅ Password reset OTP sent successfully to: " . $email . " with code: " . $otpCode);
            return $result;
        } catch (Exception $e) {
            error_log("❌ Failed to send password reset OTP: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send notification email (for likes, comments, etc.)
     */
    public function sendNotificationEmail($email, $username, $subject, $message) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $username);
            
            $this->mailer->Subject = $subject;
            
            $htmlBody = $this->getNotificationEmailTemplate($username, $subject, $message);
            $textBody = strip_tags($message);
            
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Failed to send notification email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send comment notification to image owner
     * MANDATORY FEATURE from subject requirements
     */
    public function sendCommentNotification($toEmail, $imageOwnerName, $commenterName, $commentText, $imageId) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $imageOwnerName);
            
            $this->mailer->Subject = '💬 New Comment on Your Photo - ' . SITE_NAME;
            
            $imageUrl = BASE_URL . "/index.php?page=gallery#image-" . $imageId;
            
            $htmlBody = $this->getCommentNotificationTemplate(
                $imageOwnerName, 
                $commenterName, 
                $commentText, 
                $imageUrl
            );
            $textBody = $this->getCommentNotificationTextVersion(
                $imageOwnerName, 
                $commenterName, 
                $commentText, 
                $imageUrl
            );
            
            $this->mailer->Body    = $htmlBody;
            $this->mailer->AltBody = $textBody;
            
            $result = $this->mailer->send();
            error_log("✅ Comment notification sent to: " . $toEmail . " (commenter: " . $commenterName . ")");
            return $result;
        } catch (Exception $e) {
            error_log("❌ Failed to send comment notification: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    // ========================================
    // EMAIL TEMPLATES
    // ========================================
    
    private function getVerificationEmailTemplate($username, $otpCode) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .otp-box { background: #f8f9fa; border: 2px dashed #667eea; border-radius: 8px; padding: 30px; text-align: center; margin: 30px 0; }
        .otp-code { font-size: 42px; font-weight: bold; color: #667eea; letter-spacing: 8px; font-family: 'Courier New', monospace; margin: 20px 0; }
        .highlight { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; border-radius: 4px; }
        .footer { background: #f8f9fa; text-align: center; padding: 20px; color: #888; font-size: 12px; }
        .emoji { font-size: 48px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="emoji">📧</div>
            <h1>Verify Your Email</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$this->escapeHtml($username)}</strong>,</p>
            <p>Welcome to <strong>{$this->escapeHtml(SITE_NAME)}</strong>! We're excited to have you join our photo-sharing community.</p>
            <p>To complete your registration, please enter the verification code below:</p>
            
            <div class="otp-box">
                <p style="margin: 0; font-size: 14px; color: #666;">Your Verification Code</p>
                <div class="otp-code">{$this->escapeHtml($otpCode)}</div>
                <p style="margin: 0; font-size: 12px; color: #888;">Enter this code on the verification page</p>
            </div>
            
            <div class="highlight">
                <strong>⏰ Important:</strong> This code will expire in <strong>15 minutes</strong>.
            </div>
            
            <p><strong>Security Tips:</strong></p>
            <ul style="color: #666;">
                <li>Never share this code with anyone</li>
                <li>We'll never ask for your password via email</li>
                <li>If you didn't request this code, please ignore this email</li>
            </ul>
            
            <p style="margin-top: 30px;">Need help? Contact our support team at <a href="mailto:support@camagru.com" style="color: #667eea;">support@camagru.com</a></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 {$this->escapeHtml(SITE_NAME)}. All rights reserved.</p>
            <p>This is an automated message, please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getVerificationEmailTextVersion($username, $otpCode) {
        return <<<TEXT
Welcome to {$this->escapeHtml(SITE_NAME)}!

Hi {$this->escapeHtml($username)},

Thank you for registering! To complete your registration, please use the verification code below:

YOUR VERIFICATION CODE: {$otpCode}

This code will expire in 15 minutes.

Enter this code on the verification page to activate your account.

SECURITY TIPS:
- Never share this code with anyone
- We'll never ask for your password via email
- If you didn't request this code, please ignore this email

Best regards,
The {$this->escapeHtml(SITE_NAME)} Team
TEXT;
    }
    
    private function getPasswordResetEmailTemplate($username, $resetUrl) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .button:hover { background: #5568d3; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Reset Your Password</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$this->escapeHtml($username)}</strong>,</p>
            <p>We received a request to reset your password for your {$this->escapeHtml(SITE_NAME)} account.</p>
            <p>Click the button below to create a new password:</p>
            <div style="text-align: center;">
                <a href="{$this->escapeHtml($resetUrl)}" class="button">Reset My Password</a>
            </div>
            <p>Or copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #667eea;">{$this->escapeHtml($resetUrl)}</p>
            <div class="warning">
                <strong>⚠️ Security Notice:</strong>
                <ul style="margin: 10px 0;">
                    <li>This link will expire in 1 hour</li>
                    <li>If you didn't request this, please ignore this email</li>
                    <li>Your password will remain unchanged</li>
                </ul>
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2025 {$this->escapeHtml(SITE_NAME)}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function getPasswordResetEmailTextVersion($username, $resetUrl) {
        return <<<TEXT
Reset Your Password

Hi {$this->escapeHtml($username)},

We received a request to reset your password for your {$this->escapeHtml(SITE_NAME)} account.

Click the link below to create a new password:

{$resetUrl}

This link will expire in 1 hour.

If you didn't request this password reset, please ignore this email. Your password will remain unchanged.

Best regards,
The {$this->escapeHtml(SITE_NAME)} Team
TEXT;
    }
    
    private function getNotificationEmailTemplate($username, $subject, $message) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 {$this->escapeHtml($subject)}</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$this->escapeHtml($username)}</strong>,</p>
            {$message}
            <p><a href="{$this->escapeHtml(BASE_URL)}" style="color: #667eea;">Visit {$this->escapeHtml(SITE_NAME)}</a></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 {$this->escapeHtml(SITE_NAME)}. All rights reserved.</p>
            <p>You can manage your notification preferences in your account settings.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    private function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Password Reset OTP Email Template (HTML)
     */
    private function getPasswordResetOTPTemplate($username, $otpCode) {
        $siteName = $this->escapeHtml(SITE_NAME);
        $escapedUsername = $this->escapeHtml($username);
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .otp-box { background: #f8f9fa; border: 2px solid #667eea; border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0; }
        .otp-code { font-size: 48px; font-weight: bold; color: #667eea; letter-spacing: 10px; font-family: 'Courier New', monospace; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
        .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Code</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$escapedUsername}</strong>,</p>
            <p>You requested to reset your password for your {$siteName} account. Use the verification code below to continue:</p>
            
            <div class="otp-box">
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">Your Verification Code:</p>
                <div class="otp-code">{$otpCode}</div>
                <p style="margin: 10px 0 0 0; color: #888; font-size: 12px;">Valid for 15 minutes</p>
            </div>
            
            <p style="font-size: 14px; color: #666;">
                <strong>How to use this code:</strong><br>
                1. Go to the password reset page<br>
                2. Enter this 6-digit code<br>
                3. Create your new password
            </p>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                If you didn't request this password reset, please ignore this email. Your password will remain unchanged.
            </div>
            
            <p style="color: #888; font-size: 12px;">
                This code will expire in 15 minutes for security reasons. If you need a new code, you can request one from the password reset page.
            </p>
        </div>
        <div class="footer">
            <p>This is an automated message from {$siteName}. Please do not reply to this email.</p>
            <p>&copy; 2025 {$siteName}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Password Reset OTP Email Template (Plain Text)
     */
    private function getPasswordResetOTPTextVersion($username, $otpCode) {
        return <<<TEXT
Password Reset Code for {$this->escapeHtml(SITE_NAME)}

Hi {$this->escapeHtml($username)},

You requested to reset your password. Use the verification code below:

YOUR CODE: {$otpCode}

This code is valid for 15 minutes.

How to use:
1. Go to the password reset page
2. Enter this 6-digit code
3. Create your new password

If you didn't request this password reset, please ignore this email.

Best regards,
The {$this->escapeHtml(SITE_NAME)} Team
TEXT;
    }
    
    /**
     * Get comment notification email template (HTML version)
     */
    private function getCommentNotificationTemplate($imageOwnerName, $commenterName, $commentText, $imageUrl) {
        $escapedOwnerName = htmlspecialchars($imageOwnerName);
        $escapedCommenterName = htmlspecialchars($commenterName);
        $escapedComment = htmlspecialchars($commentText);
        $escapedImageUrl = htmlspecialchars($imageUrl);
        $siteName = htmlspecialchars(SITE_NAME);
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 40px 30px; }
        .comment-box { background: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .comment-text { font-style: italic; color: #555; margin: 15px 0; padding: 15px; background: white; border-radius: 4px; }
        .commenter { font-weight: bold; color: #667eea; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; border-top: 1px solid #eee; }
        .button { display: inline-block; padding: 15px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 20px 0; }
        .info { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💬 New Comment on Your Photo!</h1>
        </div>
        <div class="content">
            <p>Hi <strong>{$escapedOwnerName}</strong>,</p>
            <p><span class="commenter">{$escapedCommenterName}</span> just commented on your photo:</p>
            
            <div class="comment-box">
                <div class="comment-text">
                    "{$escapedComment}"
                </div>
                <small style="color: #888;">— {$escapedCommenterName}</small>
            </div>
            
            <div style="text-align: center;">
                <a href="{$escapedImageUrl}" class="button">View Photo &amp; Reply</a>
            </div>
            
            <div class="info">
                <strong>💡 Stay Connected:</strong><br>
                Keep the conversation going! Reply to comments and engage with your community on {$siteName}.
            </div>
            
            <p style="color: #888; font-size: 12px; margin-top: 30px;">
                <strong>Don't want these notifications?</strong><br>
                You can disable comment notifications in your <a href="{$escapedImageUrl}" style="color: #667eea;">profile settings</a>.
            </p>
        </div>
        <div class="footer">
            <p>&copy; 2025 {$siteName}. All rights reserved.</p>
            <p>This email was sent because someone commented on your photo.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get comment notification email (plain text version)
     */
    private function getCommentNotificationTextVersion($imageOwnerName, $commenterName, $commentText, $imageUrl) {
        $siteName = SITE_NAME;
        
        return <<<TEXT
💬 New Comment on Your Photo!

Hi {$imageOwnerName},

{$commenterName} just commented on your photo:

"{$commentText}"

View your photo and reply:
{$imageUrl}

---

Don't want these notifications?
You can disable comment notifications in your profile settings.

© 2025 {$siteName}. All rights reserved.
TEXT;
    }
}
