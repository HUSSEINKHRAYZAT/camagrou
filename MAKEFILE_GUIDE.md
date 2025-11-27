# 🎯 Camagru Makefile Guide

Complete reference for all available Makefile commands to manage your Camagru Docker environment.

## 📋 Quick Reference

```bash
make help          # Show all available commands
make install       # First-time setup
make up            # Start all services
make down          # Stop all services
make status        # Show container status
make logs          # View all logs
make urls          # Show access URLs
```

## 🚀 Getting Started

### First Time Setup
```bash
make install       # Install and setup everything
```

This command will:
1. ✅ Build Docker images
2. ✅ Start all containers
3. ✅ Wait for MySQL to be ready
4. ✅ Initialize the database
5. ✅ Show access URLs

### Quick Start (After Installation)
```bash
make up            # Start services
make quick-start   # Start + setup database + show URLs
```

---

## 📦 Setup & Start Commands

| Command | Description | Use Case |
|---------|-------------|----------|
| `make install` | First-time setup (builds + starts + initializes) | Initial setup |
| `make up` | Start all Docker containers | Daily start |
| `make build` | Build/rebuild Docker images | After code changes |
| `make start` | Alias for `up` | Alternative command |
| `make init` | Alias for `install` | Alternative command |

### Examples
```bash
# First time
make install

# Every day after
make up

# After pulling changes
make build && make up
```

---

## 🎮 Control Commands

| Command | Description | Data Loss |
|---------|-------------|-----------|
| `make down` | Stop and remove all containers | ❌ No |
| `make stop` | Alias for `down` | ❌ No |
| `make restart` | Restart all containers | ❌ No |
| `make pause` | Pause all containers | ❌ No |
| `make unpause` | Resume paused containers | ❌ No |
| `make recreate` | Recreate containers (keeps data) | ❌ No |

### Examples
```bash
# Normal stop
make down

# Quick restart
make restart

# Pause for system updates
make pause
# ... do system work ...
make unpause

# Recreate containers without losing data
make recreate
```

---

## 🗄️ Database Commands

| Command | Description | When to Use |
|---------|-------------|-------------|
| `make db-setup` | Initialize/reset database schema | First time or after schema changes |
| `make db-migrate` | Run database migrations | After adding migrations |
| `make db-shell` | Open MySQL shell | Manual database work |
| `make db-verify` | Verify database setup | Troubleshooting |
| `make db-tables` | Show all tables | Quick check |
| `make db-users` | List all registered users | User management |
| `make db-activate` | Activate all users | Testing without email |

### Database Shell Examples
```bash
# Open MySQL shell
make db-shell

# Inside the shell:
mysql> SHOW TABLES;
mysql> SELECT * FROM users;
mysql> DESCRIBE images;
mysql> exit
```

### Quick Database Operations
```bash
# List all tables
make db-tables

# Show all users and their status
make db-users

# Activate all users (bypass email verification)
make db-activate

# Verify everything is set up correctly
make db-verify
```

### Execute SQL Commands
```bash
# Execute a single SQL command
make exec-db SQL="SELECT * FROM users LIMIT 5;"

# More complex queries
make exec-db SQL="UPDATE users SET verified=1 WHERE email='test@example.com';"

# Check table structure
make exec-db SQL="DESCRIBE users;"
```

---

## 📊 Monitoring Commands

| Command | Description | Output |
|---------|-------------|--------|
| `make status` | Show status of all containers | Container list with status |
| `make ps` | Alias for `status` | Same as status |
| `make logs` | View logs from all containers | Live log stream (all) |
| `make logs-web` | View web container logs | Live log stream (web only) |
| `make logs-db` | View database logs | Live log stream (db only) |
| `make logs-phpmyadmin` | View PHPMyAdmin logs | Live log stream (phpmyadmin) |
| `make health` | Check health of all services | Health status report |
| `make stats` | Show container resource usage | CPU/Memory usage |

### Monitoring Examples
```bash
# Check if everything is running
make status

# Watch all logs in real-time (Ctrl+C to exit)
make logs

# Watch only web logs
make logs-web

# Check if services are responding
make health

# Monitor resource usage
make stats
```

### Health Check Output
```
🏥 Health Check:
Web Service:
  Status: 200
Database:
  ✅ Healthy
PHPMyAdmin:
  Status: 200
```

---

## 🧹 Maintenance Commands

| Command | Description | Data Loss | Confirmation |
|---------|-------------|-----------|--------------|
| `make clean` | Stop and remove containers | ❌ No | ❌ No |
| `make clean-all` | Remove containers and volumes | ⚠️ YES | ✅ Required |
| `make prune` | Remove unused Docker resources | 🔸 Old images | ❌ No |
| `make backup` | Backup database to file | ❌ No | ❌ No |
| `make restore` | Restore from latest backup | ⚠️ Current data | ❌ No |
| `make fix-permissions` | Fix upload directory permissions | ❌ No | ❌ No |

### Backup & Restore
```bash
# Create a backup (saved in backups/ folder)
make backup
# Output: backups/camagru_backup_20251127_143022.sql

# View available backups
ls -lh backups/

# Restore from latest backup
make restore

# Restore from specific backup
docker exec -i camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru < backups/camagru_backup_20251127_143022.sql
```

### Cleanup Operations
```bash
# Stop services (keeps data)
make clean

# Remove everything including data (⚠️ requires confirmation)
make clean-all
# Are you sure? (yes/no): yes

# Remove unused Docker resources (free up space)
make prune

# Fix file permissions for uploads
make fix-permissions
```

---

## 🔧 Development Commands

| Command | Description | Use Case |
|---------|-------------|----------|
| `make shell` | Open shell in web container | Debug web issues |
| `make shell-db` | Open shell in database container | Debug database issues |
| `make exec-web` | Execute command in web container | Run PHP scripts |
| `make exec-db` | Execute SQL command | Quick SQL queries |
| `make verify` | Run all verification scripts | Check setup |
| `make urls` | Show access URLs | Find connection info |

### Shell Access
```bash
# Open bash shell in web container
make shell

# Inside the shell:
root@container:/var/www/html# ls -la
root@container:/var/www/html# php -v
root@container:/var/www/html# cat config/database.php
root@container:/var/www/html# exit

# Open bash shell in database container
make shell-db
```

### Execute Commands
```bash
# Execute a command in web container
make exec-web CMD="ls -la /var/www/html"

# Run a PHP script
make exec-web CMD="php /var/www/html/test_database.php"

# Check PHP version
make exec-web CMD="php -v"

# Execute SQL command
make exec-db SQL="SELECT COUNT(*) FROM users;"
```

### Verification
```bash
# Run all verification scripts
make verify

# Show access URLs and credentials
make urls
```

---

## ⚡ Quick Actions

Pre-configured command chains for common workflows:

| Command | What It Does | When to Use |
|---------|--------------|-------------|
| `make quick-start` | `up` + `db-setup` + `urls` | Daily start with DB check |
| `make full-restart` | `down` + `up` + `db-setup` | After major changes |
| `make fresh` | `clean-all` + `install` | Complete fresh start |

### Examples
```bash
# Start everything and show URLs
make quick-start

# Complete restart with database reset
make full-restart

# Nuclear option - fresh installation (⚠️ deletes all data)
make fresh
```

---

## ℹ️ Information Commands

| Command | Description |
|---------|-------------|
| `make help` | Show all available commands with categories |
| `make info` | Show project information and status |
| `make version` | Show Docker and docker-compose versions |
| `make urls` | Show access URLs and credentials |

### Information Display
```bash
# Show all commands organized by category
make help

# Show project info
make info

# Check Docker versions
make version

# Get access URLs
make urls
```

---

## 🧪 Testing Commands

| Command | Description |
|---------|-------------|
| `make test` | Run all tests |
| `make test-db` | Test database connection |

```bash
# Run all tests
make test

# Test just database connection
make test-db
```

---

## 🌐 Access Information

After starting services with `make up` or `make install`:

### Web Application
- **URL**: http://localhost:8080
- **Description**: Main Camagru application

### PHPMyAdmin
- **URL**: http://localhost:8081
- **Username**: `camagru_user`
- **Password**: `camagru_pass`
- **Description**: Database management interface

### MySQL Database
- **Host**: `localhost`
- **Port**: `3306`
- **Database**: `camagru`
- **Username**: `camagru_user`
- **Password**: `camagru_pass`

**Quick Access**: Run `make urls` to see this information anytime.

---

## 📝 Common Workflows

### 1️⃣ Daily Development
```bash
# Morning
make up            # Start services
make logs-web      # Watch web logs in separate terminal

# Work on your code...

# Evening
make down          # Stop when done
```

### 2️⃣ After Pulling Git Changes
```bash
make down          # Stop services
make build         # Rebuild images with new code
make up            # Start services
make db-setup      # Update database if schema changed
```

### 3️⃣ Database Reset for Testing
```bash
make backup        # Backup current data first!
make db-setup      # Reset database schema
make db-activate   # Activate all test users
make db-users      # Verify users are active
```

### 4️⃣ Troubleshooting Issues
```bash
make health        # Check service health
make logs          # View all logs
make status        # Check container status
make verify        # Run verification scripts
make restart       # Try restarting everything
```

### 5️⃣ Fresh Start (Nuclear Option)
```bash
make backup        # Backup data first! (optional)
make clean-all     # Remove everything (⚠️ deletes data)
make install       # Fresh installation
```

### 6️⃣ Working with Uploads
```bash
# If upload errors occur
make fix-permissions

# Check upload directory
make exec-web CMD="ls -la /var/www/html/public/uploads"

# Create test upload
make shell
# Inside container:
touch /var/www/html/public/uploads/test.txt
exit
```

### 7️⃣ Database Backup Routine
```bash
# Weekly backup
make backup

# View all backups
ls -lh backups/

# Keep only last 5 backups
cd backups && ls -t | tail -n +6 | xargs rm --

# Restore if needed
make restore
```

---

## 🎨 Output Format

The Makefile uses color-coded output for better readability:

- 🟢 **Green**: Success messages, command names
- 🟡 **Yellow**: Warnings, information, section headers
- 🔴 **Red**: Errors, dangerous operations
- 🔵 **Blue**: Headers, borders, titles
- ⚪ **White**: Regular text

---

## ⚠️ Important Warnings

### 🔴 Commands That Delete Data

**These commands will permanently delete your data:**

1. **`make clean-all`** - Removes all containers AND volumes
2. **`make fresh`** - Calls `clean-all` + reinstalls everything

**Always backup first:**
```bash
make backup        # Create backup
make clean-all     # Now safe to delete
```

### 🟡 Commands That Require Confirmation

These commands will ask for confirmation:
- `make clean-all` - Requires typing "yes"
- `make fresh` - Requires typing "yes"

### 🟢 Safe Commands

These commands never delete data:
- `make down` - Just stops containers
- `make clean` - Removes containers but keeps volumes
- `make restart` - Restarts without removing anything
- `make backup` - Only creates backups

---

## 🆘 Quick Troubleshooting

### Problem: Containers won't start
```bash
make down
make prune
make up
make health
```

### Problem: Database connection failed
```bash
make logs-db       # Check database logs
make db-shell      # Try connecting manually
make db-verify     # Run verification
```

### Problem: Web server not responding
```bash
make logs-web      # Check web logs
make health        # Check service health
make restart       # Restart services
```

### Problem: Upload not working
```bash
make fix-permissions
make exec-web CMD="ls -la /var/www/html/public/uploads"
```

### Problem: Port already in use
```bash
# Check what's using the port
lsof -i :8080
lsof -i :3306
lsof -i :8081

# Stop conflicting services or change ports in docker-compose.yml
```

---

## 📚 Additional Documentation

- **`DOCKER_GUIDE.md`** - Comprehensive Docker setup guide (300+ lines)
- **`DOCKER_QUICKSTART.md`** - Quick start guide
- **`HOW_TO_LAUNCH.md`** - Detailed launch instructions
- **`DOCKER_SETUP_COMPLETE.md`** - Setup completion summary
- **`README.md`** - Project overview

---

## 💡 Pro Tips

1. **Alias Frequently Used Commands**
   ```bash
   echo "alias cup='cd /sgoinfre/hkhrayza/camagrou && make up'" >> ~/.zshrc
   echo "alias cdown='cd /sgoinfre/hkhrayza/camagrou && make down'" >> ~/.zshrc
   source ~/.zshrc
   ```

2. **Watch Logs in Separate Terminal**
   ```bash
   # Terminal 1: Work here
   make up
   
   # Terminal 2: Watch logs
   make logs-web
   ```

3. **Regular Backups**
   ```bash
   # Add to crontab for weekly backups
   0 0 * * 0 cd /sgoinfre/hkhrayza/camagrou && make backup
   ```

4. **Quick Status Check**
   ```bash
   make status && make health
   ```

5. **Combined Operations**
   ```bash
   make down && make build && make up && make db-setup
   # Or use: make full-restart
   ```

---

## 🎓 Learning More

### View a Command's Code
```bash
# Open the Makefile
cat Makefile | grep -A 5 "^your-command:"

# Example: See what 'install' does
cat Makefile | grep -A 10 "^install:"
```

### Understand Docker Compose
```bash
# View the docker-compose.yml
cat docker-compose.yml

# See what docker-compose is doing
docker-compose config
```

---

## 📞 Need Help?

Run `make help` anytime to see all available commands grouped by category!

```bash
make help
```

---

**Last Updated**: November 27, 2025  
**Version**: 1.0  
**Author**: Hussein Khrayzat
