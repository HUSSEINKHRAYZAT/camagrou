# 🐳 Camagru Docker Guide

Complete guide for running Camagru with Docker.

## 📋 Prerequisites

- Docker installed (version 20.10+)
- Docker Compose installed (version 1.29+)
- At least 2GB of free disk space

## 🏗️ Architecture

The application is split into the following services:

1. **db** (MySQL 8.0) - Database service
   - Port: 3306
   - Database: `camagru`
   - User: `camagru_user`
   - Password: `camagru_pass`

2. **web** (PHP 8.1 + Apache) - Web application (Frontend + Backend)
   - Port: 8080
   - Serves the PHP application
   - Handles all HTTP requests

3. **phpmyadmin** (Optional) - Database management
   - Port: 8081
   - Web interface for MySQL

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

```bash
# Make the setup script executable
chmod +x docker-setup.sh

# Start the containers
docker-compose up -d

# Run the setup script
./docker-setup.sh
```

### Option 2: Manual Setup

```bash
# 1. Start the containers
docker-compose up -d

# 2. Wait for MySQL to be ready (about 10 seconds)
sleep 10

# 3. Set up the database
docker exec camagru-web php /var/www/html/setup.php

# 4. Verify the setup
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e "SHOW TABLES;"
```

## 🌐 Accessing the Application

Once the setup is complete, you can access:

- **Web Application**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
  - Server: `db`
  - Username: `camagru_user`
  - Password: `camagru_pass`

## 🔧 Useful Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart all containers
docker-compose restart

# View container logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f web
docker-compose logs -f db

# Check container status
docker-compose ps
```

### Database Operations

```bash
# Access MySQL CLI
docker exec -it camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru

# Run database setup
docker exec camagru-web php /var/www/html/setup.php

# Activate user accounts
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e \
  "UPDATE users SET verified = 1 WHERE email = 'your-email@example.com';"

# Backup database
docker exec camagru-mysql mysqldump -ucamagru_user -pcamagru_pass camagru > backup.sql

# Restore database
docker exec -i camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru < backup.sql
```

### Application Operations

```bash
# Access web container shell
docker exec -it camagru-web bash

# View Apache logs
docker exec camagru-web tail -f /var/log/apache2/error.log

# Check PHP version
docker exec camagru-web php -v

# Set upload permissions
docker exec camagru-web chmod -R 755 /var/www/html/public/uploads
```

## 🗑️ Cleanup

### Remove containers but keep data

```bash
docker-compose down
```

### Remove containers and volumes (WARNING: deletes all data)

```bash
docker-compose down -v
```

### Remove containers, volumes, and images

```bash
docker-compose down -v --rmi all
```

## 🐛 Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs

# Check if ports are already in use
netstat -tuln | grep -E '3306|8080|8081'

# Stop existing containers
docker stop camagru-mysql camagru-web camagru-phpmyadmin
docker rm camagru-mysql camagru-web camagru-phpmyadmin
```

### Database connection failed

```bash
# Wait for MySQL to be fully ready
sleep 10

# Check MySQL status
docker exec camagru-mysql mysqladmin ping -h localhost -u root -prootpass

# Restart database service
docker-compose restart db
```

### Permission issues with uploads

```bash
# Fix permissions
docker exec camagru-web chown -R www-data:www-data /var/www/html/public/uploads
docker exec camagru-web chmod -R 755 /var/www/html/public/uploads
```

### Cannot access the application

```bash
# Check if containers are running
docker-compose ps

# Check Apache status
docker exec camagru-web service apache2 status

# Restart web service
docker-compose restart web
```

## 📊 Environment Variables

You can customize the following environment variables in `docker-compose.yml`:

```yaml
# Database
MYSQL_ROOT_PASSWORD: rootpass
MYSQL_DATABASE: camagru
MYSQL_USER: camagru_user
MYSQL_PASSWORD: camagru_pass

# Web Application
DB_HOST: db
DB_NAME: camagru
DB_USER: camagru_user
DB_PASS: camagru_pass
```

## 🔄 Development Workflow

### Making changes to code

The web service uses volume mounting, so changes to your code are reflected immediately:

```bash
# Edit your PHP files
vim src/controllers/UserController.php

# Refresh your browser - changes are live!
```

### Rebuilding containers after Dockerfile changes

```bash
# Rebuild and restart
docker-compose up -d --build
```

### Running tests

```bash
# Run PHP tests
docker exec camagru-web php test_database.php

# Run verification
docker exec camagru-web php verify_database.php
```

## 📦 Production Deployment

For production, consider:

1. **Change default passwords** in `docker-compose.yml`
2. **Remove PHPMyAdmin** service (security)
3. **Add SSL/HTTPS** using nginx-proxy or traefik
4. **Set up backups** for database volumes
5. **Configure logging** and monitoring
6. **Use secrets** instead of environment variables

Example production `docker-compose.yml`:

```yaml
version: '3.8'

services:
  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD_FILE: /run/secrets/db_root_password
      MYSQL_DATABASE: camagru
      MYSQL_USER: camagru_user
      MYSQL_PASSWORD_FILE: /run/secrets/db_password
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - camagru-network
    secrets:
      - db_root_password
      - db_password

  web:
    build: .
    restart: always
    ports:
      - "8080:80"
    depends_on:
      - db
    networks:
      - camagru-network

secrets:
  db_root_password:
    file: ./secrets/db_root_password.txt
  db_password:
    file: ./secrets/db_password.txt

networks:
  camagru-network:
    driver: bridge

volumes:
  db_data:
    driver: local
```

## 📝 Notes

- First startup may take 30-60 seconds while images are downloaded
- Database initialization happens automatically on first run
- Uploaded images are stored in a Docker volume for persistence
- All services are on the same Docker network for internal communication

## 🆘 Support

If you encounter issues:

1. Check the logs: `docker-compose logs`
2. Verify container status: `docker-compose ps`
3. Restart services: `docker-compose restart`
4. For persistent issues, do a clean restart:
   ```bash
   docker-compose down -v
   docker-compose up -d
   ./docker-setup.sh
   ```
