#!/bin/bash

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║          CAMAGRU LOGIN ISSUE DIAGNOSTIC & FIX TOOL             ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Check if containers are running
if ! docker ps | grep -q camagru-web; then
    echo "❌ Docker containers are not running!"
    echo "   Please start them with: docker-compose up -d"
    exit 1
fi

echo "✓ Containers are running"
echo ""

# List all users
echo "📋 Current users in database:"
echo "─────────────────────────────────────────────────────────────────"
docker exec camagru-mysql mysql -u root -prootpass camagru -se "
SELECT 
    CONCAT('ID: ', id, ' | Username: ', RPAD(username, 15, ' '), ' | Email: ', RPAD(email, 25, ' '), ' | Verified: ', IF(verified, '✓', '✗')) as info
FROM users 
ORDER BY id;
" 2>/dev/null
echo ""

# Ask which user to test
echo "Enter the email address you're trying to log in with:"
read -r EMAIL

if [[ -z "$EMAIL" ]]; then
    echo "❌ No email provided. Exiting."
    exit 1
fi

# Check if user exists
USER_EXISTS=$(docker exec camagru-mysql mysql -u root -prootpass camagru -se "SELECT COUNT(*) FROM users WHERE email = '$EMAIL';" 2>/dev/null)

if [[ "$USER_EXISTS" -eq 0 ]]; then
    echo ""
    echo "❌ ERROR: No user found with email: $EMAIL"
    echo ""
    echo "📝 This means:"
    echo "   • You haven't registered with this email yet"
    echo "   • Or there was an error during registration"
    echo ""
    echo "💡 Solution:"
    echo "   1. Go to http://localhost:8080"
    echo "   2. Click 'Register' and create an account"
    echo "   3. Then run this script again"
    exit 1
fi

echo ""
echo "✓ User found!"
echo ""

# Get user details
USER_INFO=$(docker exec camagru-mysql mysql -u root -prootpass camagru -se "
SELECT id, username, verified, created_at 
FROM users 
WHERE email = '$EMAIL';
" 2>/dev/null)

echo "👤 User Details:"
echo "$USER_INFO" | awk '{print "   " $0}'
echo ""

# Check verification status
IS_VERIFIED=$(docker exec camagru-mysql mysql -u root -prootpass camagru -se "SELECT verified FROM users WHERE email = '$EMAIL';" 2>/dev/null)

if [[ "$IS_VERIFIED" -eq 0 ]]; then
    echo "⚠️  WARNING: Account is NOT verified!"
    echo ""
    echo "   This is likely why you can't log in."
    echo "   After registration, you need to verify your email."
    echo ""
    echo "🔧 Do you want to manually verify this account now? (y/n)"
    read -r VERIFY_NOW
    
    if [[ "$VERIFY_NOW" =~ ^[Yy]$ ]]; then
        docker exec camagru-mysql mysql -u root -prootpass camagru -e "UPDATE users SET verified = 1, verification_token = NULL WHERE email = '$EMAIL';" 2>/dev/null
        echo "   ✅ Account verified!"
        echo "   You can now try logging in."
        echo ""
    else
        echo "   Account remains unverified. Login will fail."
        echo ""
    fi
else
    echo "✅ Account is verified."
    echo ""
fi

# Now test password
echo "🔐 Password Test:"
echo "Enter the password you're trying to use:"
read -s PASSWORD
echo ""

# Test password
RESULT=$(docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$stmt = \$pdo->prepare('SELECT password, verified FROM users WHERE email = ?');
\$stmt->execute(['$EMAIL']);
\$user = \$stmt->fetch();

if (password_verify('$PASSWORD', \$user['password'])) {
    echo 'MATCH';
    if (!\$user['verified']) {
        echo '|UNVERIFIED';
    }
} else {
    echo 'NOMATCH';
}
" 2>/dev/null)

if [[ "$RESULT" == "MATCH" ]] || [[ "$RESULT" == "MATCH|UNVERIFIED" ]]; then
    echo "✅ PASSWORD IS CORRECT!"
    echo ""
    if [[ "$RESULT" == "MATCH|UNVERIFIED" ]]; then
        echo "⚠️  But account needs verification (see above)."
    else
        echo "✅ Login should work now!"
        echo ""
        echo "🌐 Try logging in at: http://localhost:8080"
    fi
else
    echo "❌ PASSWORD IS INCORRECT!"
    echo ""
    echo "   This is why you see 'Invalid email or password.'"
    echo ""
    echo "📝 Common issues:"
    echo "   • Typo in password"
    echo "   • Caps Lock is on"
    echo "   • Password is different from what you think"
    echo ""
    echo "🔧 Would you like to set a NEW password? (y/n)"
    read -r RESET_PASSWORD
    
    if [[ "$RESET_PASSWORD" =~ ^[Yy]$ ]]; then
        echo ""
        echo "Enter new password (min 8 characters):"
        read -s NEW_PASSWORD
        echo ""
        echo "Confirm new password:"
        read -s NEW_PASSWORD_CONFIRM
        echo ""
        
        if [[ "$NEW_PASSWORD" != "$NEW_PASSWORD_CONFIRM" ]]; then
            echo "❌ Passwords don't match!"
            exit 1
        fi
        
        if [[ ${#NEW_PASSWORD} -lt 8 ]]; then
            echo "❌ Password must be at least 8 characters!"
            exit 1
        fi
        
        # Update password
        docker exec camagru-web php -r "
        require 'config/database.php';
        \$pdo = getDBConnection();
        \$hash = password_hash('$NEW_PASSWORD', PASSWORD_DEFAULT);
        \$stmt = \$pdo->prepare('UPDATE users SET password = ? WHERE email = ?');
        \$stmt->execute([\$hash, '$EMAIL']);
        echo 'Password updated successfully!';
        " 2>/dev/null
        
        echo ""
        echo "✅ Password has been reset!"
        echo "   You can now log in with your new password."
    fi
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                    DIAGNOSTIC COMPLETE                          ║"
echo "╚════════════════════════════════════════════════════════════════╝"
