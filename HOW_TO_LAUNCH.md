# 🚀 HOW TO LAUNCH CAMAGRU WITH DOCKER

## ✅ Complete Docker Setup Created!

Your Camagru application is now fully dockerized with separate containers for each service.

---

## 📦 Services Architecture

### 🗄️ **Database Service** (`camagru-mysql`)
- **Image**: MySQL 8.0
- **Port**: 3306
- **Database**: camagru
- **User**: camagru_user
- **Password**: camagru_pass
- **Volume**: Persistent data storage

### 🌐 **Web Service** (`camagru-web`)
- **Image**: Custom PHP 8.1 + Apache
- **Port**: 8080
- **Features**: 
  - PHP with all required extensions
  - Apache with mod_rewrite enabled
  - Auto-configured for the application
- **Volume**: Live code updates (no rebuild needed)

### 💾 **PHPMyAdmin Service** (`camagru-phpmyadmin`)
- **Image**: PHPMyAdmin Latest
- **Port**: 8081
- **Purpose**: Database management interface

---

## 🎯 QUICK START (3 Commands)

```bash
# Navigate to project
cd /sgoinfre/hkhrayza/camagrou

# Start all services
docker-compose up -d

# Wait 10 seconds and setup database
sleep 10 && docker exec camagru-web php /var/www/html/setup.php
```

### Or use the automated script:

```bash
docker-compose up -d && sleep 10 && ./docker-setup.sh
```

---

## 🌐 Access Your Application

Once started, access these URLs:

- **📱 Web Application**: http://localhost:8080
  - Main Camagru application
  - Register, login, create photos, gallery, profile

- **🔧 PHPMyAdmin**: http://localhost:8081
  - Database management interface
  - Login with: `camagru_user` / `camagru_pass`

---

## 🛠️ Common Commands

### Start Services
```bash
cd /sgoinfre/hkhrayza/camagrou
docker-compose up -d
```

### Stop Services
```bash
docker-compose down
```

### Restart Services
```bash
docker-compose restart
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f web
docker-compose logs -f db
```

### Check Status
```bash
docker-compose ps
```

### Access Container Shell
```bash
# Web container
docker exec -it camagru-web bash

# Database container
docker exec -it camagru-mysql bash

# MySQL CLI
docker exec -it camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru
```

---

## 🔐 Activate User Accounts

After registration, activate user accounts:

```bash
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e \
  "UPDATE users SET verified = 1 WHERE email = 'your-email@example.com';"
```

Or activate specific accounts:

```bash
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e \
  "UPDATE users SET verified = 1 WHERE email IN ('husseinkhrayzat@hotmail.com', 'admin@admin.com');"
```

---

## 📊 Database Operations

### Setup/Reset Database
```bash
docker exec camagru-web php /var/www/html/setup.php
```

### Backup Database
```bash
docker exec camagru-mysql mysqldump -ucamagru_user -pcamagru_pass camagru > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
docker exec -i camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru < backup.sql
```

### View Tables
```bash
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e "SHOW TABLES;"
```

---

## 🔄 Development Workflow

### Code Changes
Your code changes are immediately reflected - no rebuild needed!
- Edit files in your project directory
- Refresh browser to see changes

### Rebuild After Dockerfile Changes
```bash
docker-compose up -d --build
```

### Fix Permissions
```bash
docker exec camagru-web chown -R www-data:www-data /var/www/html/public/uploads
docker exec camagru-web chmod -R 755 /var/www/html/public/uploads
```

---

## 🗑️ Cleanup Commands

### Stop and Remove Containers (Keep Data)
```bash
docker-compose down
```

### Remove Everything Including Data (⚠️ WARNING)
```bash
docker-compose down -v
```

### Remove Images Too
```bash
docker-compose down -v --rmi all
```

---

## 🐛 Troubleshooting

### Port Already in Use
```bash
# Check what's using the port
sudo lsof -i :8080
sudo lsof -i :3306

# Stop conflicting services
pkill -f "php -S localhost:8080"
docker stop $(docker ps -q)
```

### Database Connection Failed
```bash
# Wait for MySQL to be ready
sleep 10

# Check MySQL status
docker exec camagru-mysql mysqladmin ping -h localhost -u root -prootpass

# Restart database
docker-compose restart db
```

### Container Won't Start
```bash
# View detailed logs
docker-compose logs web
docker-compose logs db

# Remove and recreate
docker-compose down
docker-compose up -d
```

### Permission Denied on Uploads
```bash
docker exec camagru-web chmod -R 755 /var/www/html/public/uploads
docker exec camagru-web chown -R www-data:www-data /var/www/html/public/uploads
```

---

## 📁 Docker Files Created

- **`Dockerfile`** - Web service configuration
- **`docker-compose.yml`** - Multi-container orchestration
- **`docker-setup.sh`** - Automated setup script
- **`.dockerignore`** - Files to exclude from Docker
- **`.htaccess`** - Apache configuration
- **`DOCKER_GUIDE.md`** - Detailed documentation
- **`DOCKER_QUICKSTART.md`** - Quick reference
- **`HOW_TO_LAUNCH.md`** - This file!

---

## 🎓 Understanding the Setup

### Network
All containers are on the same Docker network (`camagru-network`), allowing them to communicate using service names:
- Web container connects to database using hostname `db`
- Environment variables automatically configured

### Volumes
Two types of volumes:
1. **Code Volume** (bind mount): `/sgoinfre/hkhrayza/camagrou` → `/var/www/html`
   - Live code updates
2. **Data Volumes** (named volumes):
   - `db_data` - MySQL persistent storage
   - `uploads_data` - User uploaded files

### Healthchecks
Database has a health check - web service waits for it to be healthy before starting.

---

## 📝 Notes

- **First startup** may take 30-60 seconds
- **Database setup** happens automatically on first run
- **Code changes** are live (no rebuild needed)
- **Data persists** even when containers are stopped
- **Port 8080** must be free (not used by other services)

---

## 🆘 Need Help?

1. Check logs: `docker-compose logs -f`
2. Verify status: `docker-compose ps`
3. Restart: `docker-compose restart`
4. Clean slate: `docker-compose down -v && docker-compose up -d`

---

## ✨ That's It!

Your Camagru application is now running in a professional Docker environment with:
- ✅ Isolated services
- ✅ Persistent data
- ✅ Easy management
- ✅ Production-ready setup

**Enjoy your Dockerized Camagru! 🎉**
