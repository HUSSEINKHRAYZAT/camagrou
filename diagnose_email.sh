#!/bin/bash

echo "==================================="
echo "Gmail SMTP Troubleshooting Guide"
echo "==================================="
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ ERROR: .env file not found!"
    exit 1
fi

# Load and display config (masking password)
echo "📧 Current Email Configuration:"
echo "--------------------------------"
grep "SMTP_" .env | while read line; do
    if [[ $line == *"PASSWORD"* ]]; then
        echo "SMTP_PASSWORD=**** (${#line} chars total)"
    else
        echo "$line"
    fi
done
echo ""

# Check password format
password=$(grep "SMTP_PASSWORD=" .env | cut -d'=' -f2)
password_len=${#password}

echo "🔍 Password Analysis:"
echo "--------------------------------"
echo "Password length: $password_len characters"

if [ $password_len -eq 16 ]; then
    echo "✅ Length is correct (16 characters)"
else
    echo "⚠️  Expected 16 characters, got $password_len"
    echo "   Gmail app passwords should be exactly 16 characters"
fi

if [[ $password =~ ^[a-zA-Z]{16}$ ]]; then
    echo "✅ Format is correct (letters only, no spaces/underscores)"
elif [[ $password =~ [_\ ] ]]; then
    echo "❌ ERROR: Password contains spaces or underscores!"
    echo "   Remove all spaces and underscores from the app password"
    echo "   Example: 'abcd efgh ijkl mnop' should be 'abcdefghijklmnop'"
else
    echo "⚠️  Password format might be incorrect"
fi
echo ""

echo "📋 Common Issues & Solutions:"
echo "--------------------------------"
echo "1. App Password Format:"
echo "   ❌ Wrong: kkau_wcaz_pazi_pkbr (with underscores)"
echo "   ❌ Wrong: kkau wcaz pazi pkbr (with spaces)"
echo "   ✅ Correct: kkauwcazpazipkbr (16 letters, no separators)"
echo ""
echo "2. Generate New App Password:"
echo "   → https://myaccount.google.com/apppasswords"
echo "   → Select 'Mail' and 'Other (Custom name)'"
echo "   → Copy the password WITHOUT spaces"
echo ""
echo "3. Check 2-Step Verification:"
echo "   → https://myaccount.google.com/security"
echo "   → Ensure '2-Step Verification' is ENABLED"
echo ""
echo "4. Check for Gmail Security Blocks:"
echo "   → https://myaccount.google.com/notifications"
echo "   → Look for blocked sign-in attempts"
echo "   → Approve the Camagru app if blocked"
echo ""

echo "🔧 Quick Fixes:"
echo "--------------------------------"
echo "If still not working, try these steps IN ORDER:"
echo ""
echo "1. Generate a FRESH app password:"
echo "   nano .env"
echo "   # Update SMTP_PASSWORD with NEW 16-char password (no spaces!)"
echo ""
echo "2. Restart the container:"
echo "   docker-compose restart web"
echo ""
echo "3. Try registering again:"
echo "   http://localhost:8080/index.php?page=register"
echo ""
echo "4. Check logs for detailed SMTP output:"
echo "   docker-compose logs -f web"
echo ""

echo "🚨 Alternative: Use a Different Email Provider"
echo "------------------------------------------------"
echo "If Gmail continues to fail, consider using:"
echo "• SendGrid (free tier: 100 emails/day)"
echo "• Mailtrap (for development/testing)"
echo "• Your hosting provider's SMTP"
echo ""

echo "📞 Need Help?"
echo "--------------------------------"
echo "If you see 'Could not authenticate' error:"
echo "1. The app password is likely wrong"
echo "2. Or 2-Step Verification is not enabled"
echo "3. Or Gmail blocked the login attempt"
echo ""
echo "Check your Gmail account for security alerts!"
echo "==================================="
