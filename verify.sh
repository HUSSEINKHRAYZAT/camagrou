#!/bin/bash

# Camagru Project Verification Script

echo "========================================="
echo "   Camagru Project Verification"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check PHP
echo -n "Checking PHP... "
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2)
    echo -e "${GREEN}✓${NC} PHP $PHP_VERSION"
else
    echo -e "${RED}✗${NC} PHP not found"
fi

# Check MySQL
echo -n "Checking MySQL... "
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql --version | awk '{print $5}' | tr -d ',')
    echo -e "${GREEN}✓${NC} MySQL $MYSQL_VERSION"
else
    echo -e "${RED}✗${NC} MySQL not found"
fi

# Check directories
echo ""
echo "Checking directories..."
DIRS=("config" "public/css" "public/js" "public/stickers" "public/uploads" "src/controllers" "src/models" "src/views")
for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo -e "${GREEN}✓${NC} $dir/"
    else
        echo -e "${RED}✗${NC} $dir/ missing"
    fi
done

# Check key files
echo ""
echo "Checking key files..."
FILES=("index.php" "setup.php" "api.php" "config/config.php" "config/database.php" "README.md")
for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file missing"
    fi
done

# Check PHP files count
echo ""
PHP_COUNT=$(find . -name "*.php" -type f | wc -l)
echo "PHP files: $PHP_COUNT"

CSS_COUNT=$(find . -name "*.css" -type f | wc -l)
echo "CSS files: $CSS_COUNT"

JS_COUNT=$(find . -name "*.js" -type f | wc -l)
echo "JS files: $JS_COUNT"

# Check uploads directory permissions
echo ""
echo -n "Checking uploads directory permissions... "
if [ -w "public/uploads" ]; then
    echo -e "${GREEN}✓${NC} Writable"
else
    echo -e "${YELLOW}!${NC} Not writable (run: chmod 755 public/uploads)"
fi

# Final message
echo ""
echo "========================================="
echo "   Verification Complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Edit config/database.php with your MySQL credentials"
echo "2. Run: php setup.php"
echo "3. Run: php -S localhost:8080"
echo "4. Open: http://localhost:8080"
echo ""
echo "For more info, see README.md or QUICKSTART.md"
