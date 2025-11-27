# 🐳 QUICK START WITH DOCKER

## Prerequisites
- Docker installed
- Docker Compose installed

## Launch the Application (3 Steps)

### Step 1: Build and Start Containers
```bash
cd /sgoinfre/hkhrayza/camagrou
docker-compose up -d
```

### Step 2: Wait for MySQL (10 seconds)
```bash
sleep 10
```

### Step 3: Initialize Database
```bash
./docker-setup.sh
```

## OR Use One Command

```bash
docker-compose up -d && sleep 10 && ./docker-setup.sh
```

## Access Your Application

- **Web App**: http://localhost:8080
- **PHPMyAdmin**: http://localhost:8081
  - Username: `camagru_user`
  - Password: `camagru_pass`

## Services

### 🗄️ Database (MySQL 8.0)
- Container: `camagru-mysql`
- Port: 3306
- Database: `camagru`
- User: `camagru_user`
- Password: `camagru_pass`

### 🌐 Web Application (PHP 8.1 + Apache)
- Container: `camagru-web`
- Port: 8080
- Serves frontend and backend

### 💾 PHPMyAdmin (Optional)
- Container: `camagru-phpmyadmin`
- Port: 8081
- Web interface for database

## Stop the Application

```bash
docker-compose down
```

## Stop and Remove All Data

```bash
docker-compose down -v
```

## View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f web
docker-compose logs -f db
```

## Troubleshooting

### Ports already in use?
```bash
# Stop other services
docker stop $(docker ps -aq)

# Or change ports in docker-compose.yml
```

### Database not ready?
```bash
# Wait longer
sleep 15

# Check status
docker exec camagru-mysql mysqladmin ping -h localhost -u root -prootpass
```

### Permission errors?
```bash
docker exec camagru-web chown -R www-data:www-data /var/www/html/public/uploads
```

## Activate User Accounts

```bash
docker exec camagru-mysql mysql -ucamagru_user -pcamagru_pass camagru -e \
  "UPDATE users SET verified = 1 WHERE email IN ('husseinkhrayzat@hotmail.com', 'admin@admin.com');"
```

## Development

Code changes are reflected immediately (no rebuild needed).
Only rebuild if you change the Dockerfile:

```bash
docker-compose up -d --build
```

---

For detailed documentation, see [DOCKER_GUIDE.md](DOCKER_GUIDE.md)
