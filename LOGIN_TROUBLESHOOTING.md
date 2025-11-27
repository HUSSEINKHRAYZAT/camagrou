# 🔐 Login Troubleshooting Guide

## Quick Fix Script

Run this interactive script to diagnose and fix login issues:

```bash
./quick_fix_login.sh
```

This script will:
- ✅ Check if your account exists
- ✅ Show verification status
- ✅ Test if your password is correct
- ✅ Offer to verify your account if needed
- ✅ Offer to reset your password if needed

---

## Common Issues & Solutions

### Issue 1: "Invalid email or password" Error

**Possible Causes:**
1. **Email doesn't exist** - You haven't registered yet
2. **Wrong password** - Password doesn't match what you registered with
3. **Typo** - Check for typos in email or password

**Solutions:**

#### Check if account exists:
```bash
docker exec camagru-mysql mysql -u root -prootpass camagru -e \
  "SELECT id, username, email, verified FROM users WHERE email = 'YOUR_EMAIL@example.com';"
```

#### Reset password to a known value:
```bash
NEW_PASSWORD="YourNewPassword123"
EMAIL="your@email.com"

docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$hash = password_hash('$NEW_PASSWORD', PASSWORD_DEFAULT);
\$pdo->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([\$hash, '$EMAIL']);
echo 'Password updated to: $NEW_PASSWORD\n';
"
```

---

### Issue 2: "Please verify your email before logging in"

**Cause:** Your account was created but not verified yet.

**Solution - Manual Verification:**
```bash
docker exec camagru-mysql mysql -u root -prootpass camagru -e \
  "UPDATE users SET verified = 1, verification_token = NULL WHERE email = 'YOUR_EMAIL@example.com';"
```

---

### Issue 3: Account doesn't exist

**Cause:** Registration failed or wasn't completed.

**Solution:** Register a new account:
1. Go to http://localhost:8080
2. Click "Register"
3. Fill in the form
4. After registration, run the verification command above

---

## Manual Commands

### List all users:
```bash
docker exec camagru-mysql mysql -u root -prootpass camagru -e \
  "SELECT id, username, email, verified, created_at FROM users ORDER BY id;"
```

### Create a test user manually:
```bash
docker exec camagru-web php -r "
require 'config/database.php';
require 'src/models/User.php';

\$user = new User();
\$token = \$user->create('testuser', 'test@example.com', 'Test123!');

// Verify immediately
\$pdo = getDBConnection();
\$pdo->prepare('UPDATE users SET verified = 1 WHERE email = ?')->execute(['test@example.com']);

echo 'Test user created and verified!\n';
echo 'Email: test@example.com\n';
echo 'Password: Test123!\n';
"
```

### Test a password:
```bash
EMAIL="your@email.com"
PASSWORD="yourpassword"

docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$stmt = \$pdo->prepare('SELECT password FROM users WHERE email = ?');
\$stmt->execute(['$EMAIL']);
\$user = \$stmt->fetch();

if (\$user && password_verify('$PASSWORD', \$user['password'])) {
    echo '✅ Password is correct!\n';
} else {
    echo '❌ Password is incorrect!\n';
}
"
```

---

## Understanding the Login Flow

1. **Registration** (`index.php?page=register`):
   - Username, email, password are validated
   - Password is hashed with `password_hash()`
   - User is created with `verified = 0`
   - Verification email is sent (may not work in development)
   - User is redirected to login page

2. **Login** (`index.php?page=login`):
   - Email and password are submitted
   - System looks up user by email
   - If email not found → "Invalid email or password"
   - If email found, password is checked with `password_verify()`
   - If password wrong → "Invalid email or password"
   - If password correct but `verified = 0` → "Please verify your email before logging in"
   - If password correct and `verified = 1` → Login successful! ✅

3. **Verification** (`index.php?page=verify&token=...`):
   - User clicks link in email
   - Token is validated
   - `verified` is set to `1`
   - User can now log in

---

## Quick Test Accounts

### Create admin account with known password:
```bash
docker exec camagru-web php -r "
require 'config/database.php';
require 'src/models/User.php';
\$user = new User();
\$user->create('admin', 'admin@admin.com', 'admin');
\$pdo = getDBConnection();
\$pdo->exec(\"UPDATE users SET verified = 1 WHERE email = 'admin@admin.com'\");
echo 'Admin account created!\nEmail: admin@admin.com\nPassword: admin\n';
"
```

Then login at http://localhost:8080 with:
- **Email:** admin@admin.com
- **Password:** admin

---

## Need More Help?

Run the interactive diagnostic tool:
```bash
./quick_fix_login.sh
```

This will walk you through identifying and fixing the exact issue with your login.
