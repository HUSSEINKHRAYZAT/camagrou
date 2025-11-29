#!/bin/zsh

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║           CAMAGRU LOGIN DIAGNOSTIC TOOL                        ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Get user email
echo "Enter the email you're trying to login with:"
read EMAIL

if [[ -z "$EMAIL" ]]; then
    echo "❌ No email provided!"
    exit 1
fi

echo ""
echo "Checking user: $EMAIL"
echo "─────────────────────────────────────────────────────────────────"

# Check if user exists and get details
USER_INFO=$(docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$stmt = \$pdo->prepare('SELECT id, username, email, verified, password, created_at FROM users WHERE email = ?');
\$stmt->execute(['$EMAIL']);
\$user = \$stmt->fetch();

if (\$user) {
    echo json_encode(\$user);
} else {
    echo 'NOT_FOUND';
}
" 2>/dev/null)

if [[ "$USER_INFO" == "NOT_FOUND" ]]; then
    echo ""
    echo "❌ USER NOT FOUND"
    echo ""
    echo "This email is not registered in the database."
    echo "Please register first at: http://localhost:8080"
    exit 1
fi

# Parse JSON (basic extraction)
echo ""
echo "✅ USER FOUND"
echo ""

# Get verification status
IS_VERIFIED=$(echo $USER_INFO | grep -o '"verified":"[0-1]"' | cut -d'"' -f4)

if [[ "$IS_VERIFIED" == "0" ]]; then
    echo "⚠️  ACCOUNT IS NOT VERIFIED!"
    echo ""
    echo "This is why you can't login. The system requires email verification."
    echo ""
    echo "🔧 Quick fix - Manually verify this account:"
    read "VERIFY?Do you want to verify this account now? (y/n): "
    
    if [[ "$VERIFY" =~ ^[Yy]$ ]]; then
        docker exec camagru-mysql mysql -u root -prootpass camagru -e \
            "UPDATE users SET verified = 1, verification_token = NULL WHERE email = '$EMAIL';" 2>/dev/null
        echo ""
        echo "✅ Account verified!"
        echo ""
    fi
else
    echo "✅ Account is verified"
    echo ""
fi

# Test password
echo "Enter the password you're using to login:"
read -s PASSWORD
echo ""

# Verify password
RESULT=$(docker exec camagru-web php -r "
require 'config/database.php';
\$pdo = getDBConnection();
\$stmt = \$pdo->prepare('SELECT password, verified FROM users WHERE email = ?');
\$stmt->execute(['$EMAIL']);
\$user = \$stmt->fetch();

if (password_verify('$PASSWORD', \$user['password'])) {
    if (\$user['verified']) {
        echo 'SUCCESS';
    } else {
        echo 'UNVERIFIED';
    }
} else {
    echo 'WRONG_PASSWORD';
}
" 2>/dev/null)

echo ""
case "$RESULT" in
    "SUCCESS")
        echo "✅✅✅ EVERYTHING IS CORRECT! ✅✅✅"
        echo ""
        echo "Your login SHOULD work now!"
        echo ""
        echo "If it still doesn't work, try:"
        echo "  1. Clear your browser cookies"
        echo "  2. Try in incognito/private mode"
        echo "  3. Restart Docker containers: docker-compose restart"
        ;;
    "UNVERIFIED")
        echo "⚠️  PASSWORD IS CORRECT BUT ACCOUNT NOT VERIFIED"
        echo ""
        echo "Run this command to verify:"
        echo "docker exec camagru-mysql mysql -u root -prootpass camagru -e \"UPDATE users SET verified = 1 WHERE email = '$EMAIL';\""
        ;;
    "WRONG_PASSWORD")
        echo "❌ PASSWORD IS INCORRECT"
        echo ""
        echo "The password you entered doesn't match what's in the database."
        echo ""
        echo "🔧 Do you want to reset the password?"
        read "RESET?Enter 'y' to reset password: "
        
        if [[ "$RESET" =~ ^[Yy]$ ]]; then
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
            
            docker exec camagru-web php -r "
            require 'config/database.php';
            \$pdo = getDBConnection();
            \$hash = password_hash('$NEW_PASSWORD', PASSWORD_DEFAULT);
            \$stmt = \$pdo->prepare('UPDATE users SET password = ?, verified = 1 WHERE email = ?');
            \$stmt->execute([\$hash, '$EMAIL']);
            " 2>/dev/null
            
            echo "✅ Password reset successfully!"
            echo ""
            echo "New credentials:"
            echo "  Email: $EMAIL"
            echo "  Password: $NEW_PASSWORD"
        fi
        ;;
esac

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║                   DIAGNOSTIC COMPLETE                          ║"
echo "╚════════════════════════════════════════════════════════════════╝"
