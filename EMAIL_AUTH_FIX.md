# 🔴 Email Authentication Issue - Action Required

## Current Status

✅ **Configuration is correct:**
- `.env` file mounted properly
- Password format correct (16 characters, no spaces)
- SMTP settings correct
- PHPMailer and dependencies installed

❌ **Still getting:** `SMTP Error: Could not authenticate`

## The Problem

Gmail is rejecting the app password. This happens when:

### 1. **The App Password is Incorrect/Expired**
   - Even though the format is correct, the password itself might be wrong
   - App passwords can expire or be revoked

### 2. **2-Step Verification Not Enabled**
   - App passwords ONLY work when 2-Step Verification is ON

### 3. **Gmail Security Block**
   - Gmail might be blocking the login attempt
   - Check: https://myaccount.google.com/notifications

## ✅ Solution: Generate a FRESH App Password

Follow these steps **EXACTLY**:

### Step 1: Verify 2-Step Verification is ON
1. Go to: https://myaccount.google.com/security
2. Look for "2-Step Verification"
3. If it says "Off", click it and enable it
4. Complete the setup process

### Step 2: Generate NEW App Password
1. Go to: **https://myaccount.google.com/apppasswords**
2. Sign in if prompted
3. Click "Select app" → Choose "Mail"
4. Click "Select device" → Choose "Other (Custom name)"
5. Enter name: **"Camagru Web App"**
6. Click **"Generate"**
7. You'll see a 16-character password like: `abcd efgh ijkl mnop`

### Step 3: Copy Password WITHOUT Spaces
**IMPORTANT:** When you copy the password:
- ❌ **Don't copy:** `abcd efgh ijkl mnop` (with spaces)
- ✅ **Copy as:** `abcdefghijklmnop` (no spaces!)

Or manually type it removing all spaces.

### Step 4: Update .env File
```bash
cd /sgoinfre/hkhrayza/camagrou
nano .env
```

Find the line:
```
SMTP_PASSWORD=kkauwcazpazipkbr
```

Replace with your NEW password (NO SPACES):
```
SMTP_PASSWORD=abcdefghijklmnop
```

Save and exit (Ctrl+O, Enter, Ctrl+X)

### Step 5: Restart Container
```bash
docker-compose restart web
```

### Step 6: Test
1. Go to: http://localhost:8080/index.php?page=register
2. Register with a NEW username/email
3. Check logs: `docker-compose logs -f web`
4. Check your email inbox

## 🚨 Still Not Working?

### Option A: Check Gmail Security
1. Go to: https://myaccount.google.com/notifications
2. Look for "Blocked sign-in attempt" 
3. If you see one, click "Review" and allow it
4. Try registering again

### Option B: Use Mailtrap (for Development)
If Gmail keeps blocking, use Mailtrap instead:

1. Sign up: https://mailtrap.io (free)
2. Get your SMTP credentials
3. Update `.env`:
```env
SMTP_HOST=smtp.mailtrap.io
SMTP_PORT=2525
SMTP_USERNAME=your_mailtrap_username
SMTP_PASSWORD=your_mailtrap_password
```
4. Emails won't actually send, but you can see them in Mailtrap inbox

### Option C: System Works WITHOUT Email!
Your OTP system already handles email failures:
- When email fails, OTP is displayed on screen
- You can still verify accounts
- Perfect for development/testing

## Quick Test Command

Check if your password is being loaded:
```bash
docker-compose exec web cat /var/www/html/.env | grep SMTP_PASSWORD
```

Should show 16 characters, no spaces.

## Current Logs Command

Watch live logs while registering:
```bash
docker-compose logs -f web
```

---

**Bottom Line:** The most likely issue is that the app password `kkauwcazpazipkbr` is not valid. Generate a fresh one from Google and update the `.env` file.
