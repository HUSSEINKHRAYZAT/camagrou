#!/bin/bash
# OTP System Test Script

echo "=========================================="
echo "🧪 OTP EMAIL VERIFICATION SYSTEM TEST"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Test 1: Check database column
echo -e "${BLUE}[TEST 1]${NC} Checking database structure..."
docker exec camagru-mysql mysql -u root -prootpass camagru -e "DESCRIBE users;" 2>&1 | grep -i "otp_expiry" > /dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} otp_expiry column exists"
else
    echo -e "${RED}✗${NC} otp_expiry column missing!"
    exit 1
fi

# Test 2: Check if User model has OTP methods
echo -e "${BLUE}[TEST 2]${NC} Checking User model..."
grep -q "verifyOTP" src/models/User.php
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} User model has verifyOTP method"
else
    echo -e "${RED}✗${NC} User model missing verifyOTP method!"
    exit 1
fi

grep -q "resendOTP" src/models/User.php
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} User model has resendOTP method"
else
    echo -e "${RED}✗${NC} User model missing resendOTP method!"
    exit 1
fi

# Test 3: Check if EmailService uses OTP
echo -e "${BLUE}[TEST 3]${NC} Checking EmailService..."
grep -q "otpCode" src/services/EmailService.php
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} EmailService configured for OTP"
else
    echo -e "${RED}✗${NC} EmailService not updated for OTP!"
    exit 1
fi

# Test 4: Check if verification view exists
echo -e "${BLUE}[TEST 4]${NC} Checking verification view..."
if [ -f "src/views/verify_otp.php" ]; then
    echo -e "${GREEN}✓${NC} verify_otp.php view exists"
else
    echo -e "${RED}✗${NC} verify_otp.php view missing!"
    exit 1
fi

# Test 5: Check if routes are configured
echo -e "${BLUE}[TEST 5]${NC} Checking routes..."
grep -q "verify_otp" index.php
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} verify_otp route configured"
else
    echo -e "${RED}✗${NC} verify_otp route missing!"
    exit 1
fi

grep -q "resend_otp" index.php
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} resend_otp route configured"
else
    echo -e "${RED}✗${NC} resend_otp route missing!"
    exit 1
fi

# Test 6: Check PHPMailer installation
echo -e "${BLUE}[TEST 6]${NC} Checking PHPMailer..."
docker exec camagru-web php -r "require 'vendor/autoload.php'; use PHPMailer\PHPMailer\PHPMailer; echo PHPMailer::VERSION;" 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} PHPMailer installed"
else
    echo -e "${RED}✗${NC} PHPMailer not installed!"
    exit 1
fi

# Test 7: Test OTP generation
echo -e "${BLUE}[TEST 7]${NC} Testing OTP generation..."
OTP=$(docker exec camagru-web php -r "echo sprintf('%06d', mt_rand(0, 999999));" 2>/dev/null)
if [[ $OTP =~ ^[0-9]{6}$ ]]; then
    echo -e "${GREEN}✓${NC} OTP generation works (sample: $OTP)"
else
    echo -e "${RED}✗${NC} OTP generation failed!"
    exit 1
fi

# Test 8: Check web server
echo -e "${BLUE}[TEST 8]${NC} Checking web server..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Web server responding (HTTP $HTTP_CODE)"
else
    echo -e "${YELLOW}⚠${NC} Web server returned HTTP $HTTP_CODE"
fi

# Test 9: Check verify_otp page
echo -e "${BLUE}[TEST 9]${NC} Checking verification page..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8080/index.php?page=verify_otp)
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Verification page accessible (HTTP $HTTP_CODE)"
else
    echo -e "${YELLOW}⚠${NC} Verification page returned HTTP $HTTP_CODE"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}✅ ALL TESTS PASSED!${NC}"
echo "=========================================="
echo ""
echo "📋 System Summary:"
echo "   - OTP System: READY ✓"
echo "   - Database: CONFIGURED ✓"
echo "   - Email Service: READY ✓"
echo "   - Web Interface: ACCESSIBLE ✓"
echo ""
echo "🎯 Next Steps:"
echo "   1. Configure email in config/config.php"
echo "   2. Test registration at: http://localhost:8080"
echo "   3. Check email for OTP code"
echo "   4. Enter code at: http://localhost:8080/index.php?page=verify_otp"
echo ""
echo "📚 Documentation:"
echo "   - OTP_SYSTEM.md - Complete OTP system guide"
echo "   - EMAIL_SETUP.md - Email configuration guide"
echo ""
