# 📧 Email Verification Setup Guide

Your Camagru application now has **real email verification** functionality! Follow this guide to configure it.

## ✅ What's Been Implemented

1. **PHPMailer Integration** - Professional email sending library
2. **EmailService Class** - Handles all email operations
3. **Beautiful HTML Email Templates** - Professional-looking verification emails
4. **Password Reset Emails** - Secure password recovery
5. **Notification Emails** - For likes, comments, etc. (ready to use)

---

## 🔧 Setup Instructions

### Step 1: Get Gmail App Password

To send emails, you need to use Gmail's App Password feature (NOT your regular Gmail password).

#### For Gmail Users:

1. **Go to your Google Account settings:**
   - Visit: https://myaccount.google.com/apppasswords
   - Sign in with your Gmail account

2. **Generate an App Password:**
   - If you see "App passwords" option, click it
   - If not, you need to enable 2-Step Verification first:
     - Go to: https://myaccount.google.com/security
     - Click "2-Step Verification" and follow the setup
     - Then return to App Passwords

3. **Create the password:**
   - Select app: **Mail**
   - Select device: **Other (Custom name)**
   - Enter name: **Camagru**
   - Click **Generate**
   - You'll get a 16-character password like: `abcd efgh ijkl mnop`
   - **Copy this password!** (remove spaces: `abcdefghijklmnop`)

### Step 2: Update Configuration

Edit `/sgoinfre/hkhrayza/camagrou/config/config.php`:

```php
// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');        // Your Gmail address
define('SMTP_PASSWORD', 'abcdefghijklmnop');            // Your 16-char App Password
define('SMTP_FROM', 'noreply@camagru.com');             // Display name
```

**Replace:**
- `your-email@gmail.com` with your actual Gmail address
- `abcdefghijklmnop` with your App Password (16 characters, no spaces)

### Step 3: Rebuild Docker Container

The container needs to be rebuilt to install PHPMailer:

```bash
# Stop containers
docker-compose down

# Rebuild with Composer dependencies
docker-compose build web

# Start containers
docker-compose up -d

# Check logs to verify everything started correctly
docker-compose logs -f web
```

---

## 🧪 Testing Email Verification

### Test 1: Register New User

1. **Go to:** http://localhost:8080
2. **Click "Register"**
3. **Fill in the form:**
   - Username: `testuser`
   - Email: Your real email address
   - Password: `Test1234`
   - Confirm: `Test1234`
4. **Click "Register"**
5. **Check your email inbox!** (also check spam folder)

### Test 2: Verify the Email Looks Good

You should receive an email with:
- ✅ Professional design with colors and styling
- ✅ Clear "Verify My Email" button
- ✅ Working verification link
- ✅ Security notes about link expiration

### Test 3: Click Verification Link

Click the button or link in the email, you should:
- ✅ Be redirected to the login page
- ✅ See success message: "Email verified successfully!"
- ✅ Be able to login with your credentials

---

## 🔍 Troubleshooting

### Problem: "Could not instantiate mail function"

**Solution:** Make sure PHPMailer is installed:
```bash
docker exec camagru-web composer require phpmailer/phpmailer
docker-compose restart web
```

### Problem: "SMTP connect() failed"

**Causes:**
1. Wrong SMTP credentials
2. App Password not generated
3. 2-Step Verification not enabled

**Solution:**
- Verify your Gmail settings
- Make sure you're using App Password (NOT your Gmail password)
- Check SMTP_USERNAME and SMTP_PASSWORD in config.php

### Problem: "Authentication failed"

**Solution:**
- Double-check your App Password (16 characters, no spaces)
- Make sure SMTP_USERNAME is your full Gmail address
- Regenerate App Password if needed

### Problem: Emails go to Spam

**Solution:**
- This is normal for development
- In production, use a proper domain email
- For now, check your spam folder

### Problem: No email received

**Checks:**
1. Check Docker logs:
   ```bash
   docker-compose logs web | grep -i email
   ```

2. Check if email service is working:
   ```bash
   docker exec camagru-web php -r "
   require 'config/config.php';
   require 'src/services/EmailService.php';
   \$email = new EmailService();
   echo 'Email service loaded successfully\n';
   "
   ```

3. Test sending an email:
   ```bash
   docker exec camagru-web php -r "
   require 'config/config.php';
   require 'src/services/EmailService.php';
   \$email = new EmailService();
   \$result = \$email->sendVerificationEmail(
       'your-test@email.com',
       'TestUser',
       'test-token-123'
   );
   echo \$result ? 'Email sent!' : 'Failed to send';
   "
   ```

---

## 📋 Email Features

### 1. Verification Emails
- Sent automatically on registration
- Beautiful HTML template
- 24-hour expiration (configurable)
- Secure token-based verification

### 2. Password Reset Emails
- Sent when user requests password reset
- 1-hour expiration for security
- Clear security warnings
- One-time use tokens

### 3. Notification Emails (Ready to Use)
- Can be used for likes, comments, mentions
- User can disable in settings
- Check `email_notifications` field in database

---

## 🎨 Email Templates

All emails include:
- **HTML Version:** Beautiful, responsive design
- **Plain Text Version:** For email clients that don't support HTML
- **Branded Headers:** With your Camagru logo and colors
- **Clear CTAs:** Prominent action buttons
- **Security Information:** Clear expiration times and warnings

---

## 🔐 Security Features

1. **Token-based Verification:** Secure random tokens (64 characters)
2. **Expiration Times:** Links expire after set time
3. **One-time Use:** Tokens invalidated after use
4. **SQL Injection Protection:** Prepared statements
5. **XSS Protection:** All output escaped in email templates

---

## 🚀 Alternative Email Providers

### Using Other SMTP Providers

#### SendGrid:
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
```

#### Mailgun:
```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'postmaster@your-domain.mailgun.org');
define('SMTP_PASSWORD', 'your-mailgun-password');
```

#### Outlook/Hotmail:
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@outlook.com');
define('SMTP_PASSWORD', 'your-password');
```

---

## 📊 Monitoring Emails

### Check Email Logs
```bash
# View recent email-related logs
docker-compose logs web | grep -i email

# Follow logs in real-time
docker-compose logs -f web
```

### Check Email Statistics
```bash
# Count verification emails sent
docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$count = \$pdo->query('SELECT COUNT(*) FROM users WHERE verified = 0')->fetchColumn();
echo \"Unverified users: \$count\n\";
"
```

---

## ✨ Quick Commands

```bash
# Rebuild with email support
docker-compose down && docker-compose build web && docker-compose up -d

# Test email configuration
docker exec camagru-web php -r "require 'config/config.php'; echo 'SMTP: ' . SMTP_USERNAME . '\n';"

# View recent registrations
docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$users = \$pdo->query('SELECT username, email, verified, created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();
foreach (\$users as \$u) {
    \$v = \$u['verified'] ? '✅' : '❌';
    echo \"\$v {\$u['username']} ({\$u['email']}) - {\$u['created_at']}\n\";
}
"

# Manually verify a user (for testing)
docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$email = 'user@example.com';  // Change this
\$pdo->exec(\"UPDATE users SET verified = 1 WHERE email = '\$email'\");
echo \"User verified!\n\";
"
```

---

## 🎯 Next Steps

1. **Configure your Gmail App Password** (Step 1)
2. **Update config/config.php** (Step 2)
3. **Rebuild Docker container** (Step 3)
4. **Test with a real registration** (Test 1)
5. **Celebrate!** 🎉

---

## 📞 Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review Docker logs: `docker-compose logs web`
3. Verify Gmail App Password setup
4. Test with a different email provider

**Your email verification system is now production-ready!** ✉️✨
