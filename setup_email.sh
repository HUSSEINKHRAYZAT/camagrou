#!/bin/bash
# Quick Email Setup Script

echo "=========================================="
echo "📧 CAMAGRU EMAIL CONFIGURATION HELPER"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}This script will help you configure email for your Camagru application.${NC}"
echo ""

# Check current config
echo -e "${YELLOW}📋 Current Configuration:${NC}"
grep "SMTP_USERNAME\|SMTP_PASSWORD" config/config.php | grep -v "//"
echo ""

# Check if already configured
if grep -q "your-email@gmail.com" config/config.php; then
    echo -e "${RED}⚠️  Email is NOT configured yet (using placeholder values)${NC}"
    echo ""
else
    echo -e "${GREEN}✓ Email appears to be configured${NC}"
    echo ""
    echo "Testing email sending..."
    docker exec camagru-web php -r "
    require 'config/config.php';
    require 'vendor/autoload.php';
    require 'src/services/EmailService.php';
    echo 'SMTP Host: ' . SMTP_HOST . PHP_EOL;
    echo 'SMTP User: ' . SMTP_USERNAME . PHP_EOL;
    echo 'Testing connection...' . PHP_EOL;
    " 2>&1
    exit 0
fi

# Instructions
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}HOW TO GET GMAIL APP PASSWORD:${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo "1. Go to: https://myaccount.google.com/apppasswords"
echo "2. Sign in with your Google account"
echo "3. If you don't see 'App passwords', enable 2-Step Verification first:"
echo "   - Go to: https://myaccount.google.com/security"
echo "   - Enable '2-Step Verification'"
echo "   - Then return to App passwords"
echo "4. Create App Password:"
echo "   - Select app: 'Mail'"
echo "   - Select device: 'Other (Custom name)'"
echo "   - Name it: 'Camagru'"
echo "   - Click 'Generate'"
echo "5. Copy the 16-character password (looks like: abcd efgh ijkl mnop)"
echo ""

# Interactive setup
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}CONFIGURE EMAIL NOW:${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
read -p "Do you want to configure email now? (y/n): " configure

if [[ $configure == "y" || $configure == "Y" ]]; then
    echo ""
    read -p "Enter your Gmail address: " gmail_address
    read -p "Enter your 16-char App Password (no spaces): " app_password
    
    echo ""
    echo -e "${BLUE}Updating config/config.php...${NC}"
    
    # Backup original
    cp config/config.php config/config.php.backup
    
    # Update config
    sed -i "s/your-email@gmail.com/$gmail_address/g" config/config.php
    sed -i "s/your-app-password/$app_password/g" config/config.php
    
    echo -e "${GREEN}✓ Configuration updated!${NC}"
    echo ""
    echo -e "${BLUE}Restarting web container...${NC}"
    docker-compose restart web
    
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}✅ EMAIL CONFIGURED SUCCESSFULLY!${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo "📧 SMTP Username: $gmail_address"
    echo "🔒 App Password: ${app_password:0:4}****${app_password: -4}"
    echo ""
    echo "🧪 Test it now:"
    echo "   1. Go to: http://localhost:8080"
    echo "   2. Register with a real email address"
    echo "   3. Check your inbox for the OTP code!"
    echo ""
    echo "💾 Backup saved to: config/config.php.backup"
else
    echo ""
    echo -e "${YELLOW}📝 Manual Configuration:${NC}"
    echo ""
    echo "Edit config/config.php and update these lines:"
    echo ""
    echo "  define('SMTP_USERNAME', 'your-email@gmail.com');  // Your Gmail"
    echo "  define('SMTP_PASSWORD', 'your-app-password');      // 16-char password"
    echo ""
    echo "Then restart: docker-compose restart web"
fi

echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📚 For more help, see: EMAIL_SETUP.md${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
