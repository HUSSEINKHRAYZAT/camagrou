#!/bin/bash

# Camagru Docker Setup Script
# This script initializes the database after containers are up

echo "🐳 Starting Camagru Docker Setup..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Wait for MySQL to be ready
echo -e "${YELLOW}⏳ Waiting for MySQL to be ready...${NC}"
sleep 10

# Run database setup
echo -e "${YELLOW}📊 Setting up database...${NC}"
docker exec camagru-web php /var/www/html/setup.php

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database setup completed successfully!${NC}"
else
    echo -e "${RED}❌ Database setup failed!${NC}"
    exit 1
fi

# Verify database
echo -e "${YELLOW}🔍 Verifying database...${NC}"
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e "SHOW TABLES;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Database verification successful!${NC}"
else
    echo -e "${RED}❌ Database verification failed!${NC}"
    exit 1
fi

# Set correct permissions
echo -e "${YELLOW}🔐 Setting file permissions...${NC}"
docker exec camagru-web chown -R www-data:www-data /var/www/html/public/uploads
docker exec camagru-web chmod -R 755 /var/www/html/public/uploads

echo -e "${GREEN}✨ Setup complete!${NC}"
echo ""
echo -e "${GREEN}🌐 Access your application at:${NC}"
echo -e "   ${YELLOW}Web App:${NC}      http://localhost:8080"
echo -e "   ${YELLOW}PHPMyAdmin:${NC}   http://localhost:8081"
echo ""
echo -e "${GREEN}📦 Container status:${NC}"
docker-compose ps
