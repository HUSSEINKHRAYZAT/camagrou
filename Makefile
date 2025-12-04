# Makefile for Camagru Docker Environment
# Author: Hussein Khrayzat
# Date: November 27, 2025

# Colors for output
GREEN := \033[0;32m
YELLOW := \033[1;33m
RED := \033[0;31m
BLUE := \033[0;34m
NC := \033[0m # No Color

# Docker compose command
DOCKER_COMPOSE := docker-compose

# Container names
WEB_CONTAINER := camagru-web
DB_CONTAINER := camagru-mysql
PHPMYADMIN_CONTAINER := camagru-phpmyadmin

# Default target
.DEFAULT_GOAL := help

.PHONY: help
help: ## Show this help message
	@printf "\033[0;34m╔═══════════════════════════════════════════════════════╗\033[0m\n"
	@printf "\033[0;34m║\033[0m  \033[0;32mCamagru Docker Management - Available Commands\033[0m  \033[0;34m║\033[0m\n"
	@printf "\033[0;34m╚═══════════════════════════════════════════════════════╝\033[0m\n"
	@printf "\n"
	@printf "\033[1;33mSetup & Start:\033[0m\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep "Setup\|Start\|up\|build" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[0;32m%-15s\033[0m %s\n", $$1, $$2}'
	@printf "\n"
	@printf "\033[1;33mControl:\033[0m\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep "Stop\|Restart\|down\|pause" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[0;32m%-15s\033[0m %s\n", $$1, $$2}'
	@printf "\n"
	@printf "\033[1;33mDatabase:\033[0m\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -i "db\|database" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[0;32m%-15s\033[0m %s\n", $$1, $$2}'
	@printf "\n"
	@printf "\033[1;33mMonitoring:\033[0m\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -i "logs\|status\|ps\|health\|stats" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[0;32m%-15s\033[0m %s\n", $$1, $$2}'
	@printf "\n"
	@printf "\033[1;33mMaintenance:\033[0m\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -i "clean\|backup\|restore\|prune\|fix" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[0;32m%-15s\033[0m %s\n", $$1, $$2}'
	@printf "\n"
	@printf "\033[1;33mDevelopment:\033[0m\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | grep -i "shell\|exec\|verify\|urls\|test" | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[0;32m%-15s\033[0m %s\n", $$1, $$2}'
	@printf "\n"
	@printf "\033[0;34m═══════════════════════════════════════════════════════\033[0m\n"
	@printf "\033[1;33mExample:\033[0m make up      \033[0;32m# Start all services\033[0m\n"
	@printf "\033[1;33mExample:\033[0m make logs    \033[0;32m# View all logs\033[0m\n"
	@printf "\033[0;34m═══════════════════════════════════════════════════════\033[0m\n"

# ============================================================
# Setup & Start Commands
# ============================================================

.PHONY: install
install: ## Setup: Install and setup everything (first time)
	@echo "$(GREEN)🚀 Installing Camagru Docker Environment...$(NC)"
	@chmod +x docker-setup.sh
	@$(DOCKER_COMPOSE) build --no-cache
	@$(DOCKER_COMPOSE) up -d
	@echo "$(YELLOW)⏳ Waiting for MySQL to be ready...$(NC)"
	@sleep 10
	@./docker-setup.sh
	@echo "$(GREEN)✅ Installation complete!$(NC)"
	@make urls

.PHONY: up
up: ## Start: Start all Docker containers
	@echo "$(GREEN)🚀 Starting Camagru services...$(NC)"
	@$(DOCKER_COMPOSE) up -d
	@sleep 3
	@make status

.PHONY: build
build: ## Setup: Build/rebuild Docker images
	@echo "$(YELLOW)🔨 Building Docker images...$(NC)"
	@$(DOCKER_COMPOSE) build --no-cache
	@echo "$(GREEN)✅ Build complete!$(NC)"

.PHONY: rebuild
rebuild: ## Setup: Rebuild and restart web service (no cache)
	@echo "$(YELLOW)🔨 Rebuilding web service without cache...$(NC)"
	@$(DOCKER_COMPOSE) build --no-cache web
	@echo "$(GREEN)✅ Build complete! Restarting service...$(NC)"
	@$(DOCKER_COMPOSE) up -d web
	@sleep 2
	@echo "$(GREEN)✅ Web service rebuilt and restarted!$(NC)"
	@make status

.PHONY: start
start: up ## Start: Alias for 'up'

.PHONY: init
init: install ## Setup: Alias for 'install'

# ============================================================
# Control Commands
# ============================================================

.PHONY: down
down: ## Stop: Stop and remove all containers
	@echo "$(YELLOW)🛑 Stopping Camagru services...$(NC)"
	@$(DOCKER_COMPOSE) down
	@echo "$(GREEN)✅ Services stopped$(NC)"

.PHONY: stop
stop: down ## Stop: Alias for 'down'

.PHONY: restart
restart: ## Restart: Restart all containers
	@echo "$(YELLOW)🔄 Restarting Camagru services...$(NC)"
	@$(DOCKER_COMPOSE) restart
	@sleep 3
	@make status

.PHONY: pause
pause: ## Control: Pause all containers
	@echo "$(YELLOW)⏸️  Pausing containers...$(NC)"
	@$(DOCKER_COMPOSE) pause
	@echo "$(GREEN)✅ Containers paused$(NC)"

.PHONY: unpause
unpause: ## Control: Unpause all containers
	@echo "$(GREEN)▶️  Resuming containers...$(NC)"
	@$(DOCKER_COMPOSE) unpause
	@echo "$(GREEN)✅ Containers resumed$(NC)"

.PHONY: recreate
recreate: ## Control: Recreate containers (keep data)
	@echo "$(YELLOW)🔄 Recreating containers...$(NC)"
	@$(DOCKER_COMPOSE) up -d --force-recreate
	@sleep 3
	@make status

# ============================================================
# Database Commands
# ============================================================

.PHONY: db-setup
db-setup: ## Database: Initialize/reset database schema
	@echo "$(GREEN)📊 Setting up database...$(NC)"
	@docker exec $(WEB_CONTAINER) php /var/www/html/setup.php
	@echo "$(GREEN)✅ Database setup complete!$(NC)"

.PHONY: db-migrate
db-migrate: ## Database: Run database migrations
	@echo "$(GREEN)📊 Running migrations...$(NC)"
	@docker exec $(WEB_CONTAINER) php /var/www/html/migrate.php
	@echo "$(GREEN)✅ Migrations complete!$(NC)"

.PHONY: db-shell
db-shell: ## Database: Open MySQL shell
	@echo "$(BLUE)🔌 Connecting to MySQL...$(NC)"
	@docker exec -it $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru

.PHONY: db-verify
db-verify: ## Database: Verify database setup
	@echo "$(BLUE)🔍 Verifying database...$(NC)"
	@docker exec $(WEB_CONTAINER) php /var/www/html/verify_database.php

.PHONY: db-tables
db-tables: ## Database: Show all tables
	@echo "$(BLUE)📋 Database Tables:$(NC)"
	@docker exec $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru -e "SHOW TABLES;" 2>&1 | grep -v "Warning" || true

.PHONY: db-users
db-users: ## Database: List all users
	@echo "$(BLUE)👥 Registered Users:$(NC)"
	@docker exec $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru -e "SELECT id, username, email, verified, created_at FROM users;" 2>&1 | grep -v "Warning" || true

.PHONY: db-activate
db-activate: ## Database: Activate all users
	@echo "$(GREEN)✅ Activating all users...$(NC)"
	@docker exec $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru -e "UPDATE users SET verified = 1;" 2>&1 | grep -v "Warning" || true
	@make db-users

# ============================================================
# Monitoring Commands
# ============================================================

.PHONY: status
status: ## Monitoring: Show status of all containers
	@echo "$(BLUE)📊 Container Status:$(NC)"
	@$(DOCKER_COMPOSE) ps

.PHONY: ps
ps: status ## Monitoring: Alias for 'status'

.PHONY: logs
logs: ## Monitoring: View logs from all containers
	@$(DOCKER_COMPOSE) logs -f

.PHONY: logs-web
logs-web: ## Monitoring: View web container logs
	@docker logs -f $(WEB_CONTAINER)

.PHONY: logs-db
logs-db: ## Monitoring: View database container logs
	@docker logs -f $(DB_CONTAINER)

.PHONY: logs-phpmyadmin
logs-phpmyadmin: ## Monitoring: View PHPMyAdmin logs
	@docker logs -f $(PHPMYADMIN_CONTAINER)

.PHONY: health
health: ## Monitoring: Check health of all services
	@echo "$(BLUE)🏥 Health Check:$(NC)"
	@echo "$(YELLOW)Web Service:$(NC)"
	@curl -s -o /dev/null -w "  Status: %{http_code}\n" http://localhost:8080 || echo "  $(RED)❌ Not responding$(NC)"
	@echo "$(YELLOW)Database:$(NC)"
	@docker exec $(DB_CONTAINER) mysqladmin ping -h localhost -u root -prootpass 2>&1 | grep -q "alive" && echo "  $(GREEN)✅ Healthy$(NC)" || echo "  $(RED)❌ Unhealthy$(NC)"
	@echo "$(YELLOW)PHPMyAdmin:$(NC)"
	@curl -s -o /dev/null -w "  Status: %{http_code}\n" http://localhost:8081 || echo "  $(RED)❌ Not responding$(NC)"

.PHONY: stats
stats: ## Monitoring: Show container resource usage
	@docker stats --no-stream $(WEB_CONTAINER) $(DB_CONTAINER) $(PHPMYADMIN_CONTAINER)

# ============================================================
# Maintenance Commands
# ============================================================

.PHONY: clean
clean: ## Maintenance: Stop and remove containers (keep data)
	@echo "$(YELLOW)🧹 Cleaning up containers...$(NC)"
	@$(DOCKER_COMPOSE) down
	@echo "$(GREEN)✅ Cleanup complete!$(NC)"

.PHONY: clean-all
clean-all: ## Maintenance: Remove containers and volumes (⚠️ DELETES DATA)
	@echo "$(RED)⚠️  WARNING: This will delete all data!$(NC)"
	@read -p "Are you sure? (yes/no): " confirm && [ "$$confirm" = "yes" ] || exit 1
	@echo "$(YELLOW)🧹 Removing containers and volumes...$(NC)"
	@$(DOCKER_COMPOSE) down -v
	@echo "$(GREEN)✅ All data removed!$(NC)"

.PHONY: prune
prune: ## Maintenance: Remove unused Docker resources
	@echo "$(YELLOW)🧹 Pruning unused Docker resources...$(NC)"
	@docker system prune -f
	@echo "$(GREEN)✅ Prune complete!$(NC)"

.PHONY: backup
backup: ## Maintenance: Backup database to file
	@echo "$(GREEN)💾 Creating database backup...$(NC)"
	@mkdir -p backups
	@docker exec $(DB_CONTAINER) mysqldump -ucamagru_user -pcamagru_pass camagru > backups/camagru_backup_$$(date +%Y%m%d_%H%M%S).sql 2>/dev/null
	@echo "$(GREEN)✅ Backup saved to backups/$(NC)"
	@ls -lh backups/ | tail -1

.PHONY: restore
restore: ## Maintenance: Restore database from latest backup
	@echo "$(YELLOW)📥 Restoring database from backup...$(NC)"
	@LATEST=$$(ls -t backups/*.sql 2>/dev/null | head -1); \
	if [ -z "$$LATEST" ]; then \
		echo "$(RED)❌ No backup files found!$(NC)"; \
		exit 1; \
	fi; \
	echo "$(BLUE)Restoring from: $$LATEST$(NC)"; \
	docker exec -i $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru < "$$LATEST" && \
	echo "$(GREEN)✅ Database restored!$(NC)"

.PHONY: fix-permissions
fix-permissions: ## Maintenance: Fix upload directory permissions
	@echo "$(YELLOW)🔐 Fixing permissions...$(NC)"
	@docker exec $(WEB_CONTAINER) chown -R www-data:www-data /var/www/html/public/uploads
	@docker exec $(WEB_CONTAINER) chmod -R 755 /var/www/html/public/uploads
	@echo "$(GREEN)✅ Permissions fixed!$(NC)"

# ============================================================
# Development Commands
# ============================================================

.PHONY: shell
shell: ## Development: Open shell in web container
	@echo "$(BLUE)🐚 Opening shell in web container...$(NC)"
	@docker exec -it $(WEB_CONTAINER) bash

.PHONY: shell-db
shell-db: ## Development: Open shell in database container
	@echo "$(BLUE)🐚 Opening shell in database container...$(NC)"
	@docker exec -it $(DB_CONTAINER) bash

.PHONY: exec-web
exec-web: ## Development: Execute command in web container (use CMD="...")
	@docker exec $(WEB_CONTAINER) $(CMD)

.PHONY: exec-db
exec-db: ## Development: Execute SQL command (use SQL="...")
	@docker exec $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru -e "$(SQL)" 2>&1 | grep -v "Warning" || true

.PHONY: verify
verify: ## Development: Run all verification scripts
	@echo "$(BLUE)🔍 Running verifications...$(NC)"
	@docker exec $(WEB_CONTAINER) php /var/www/html/verify_database.php
	@docker exec $(WEB_CONTAINER) php /var/www/html/test_database.php

.PHONY: urls
urls: ## Development: Show access URLs
	@echo "$(BLUE)╔═══════════════════════════════════════════════════╗$(NC)"
	@echo "$(BLUE)║$(NC)         $(GREEN)Camagru Access URLs$(NC)                    $(BLUE)║$(NC)"
	@echo "$(BLUE)╠═══════════════════════════════════════════════════╣$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)📱 Web App:$(NC)      http://localhost:8080          $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)🔧 PHPMyAdmin:$(NC)   http://localhost:8081          $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)🗄️  MySQL:$(NC)       localhost:3306                $(BLUE)║$(NC)"
	@echo "$(BLUE)╠═══════════════════════════════════════════════════╣$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)User:$(NC) camagru_user  $(YELLOW)Pass:$(NC) camagru_pass      $(BLUE)║$(NC)"
	@echo "$(BLUE)╚═══════════════════════════════════════════════════╝$(NC)"

# ============================================================
# Quick Actions
# ============================================================

.PHONY: quick-start
quick-start: up db-setup urls ## Quick: Complete start with database setup
	@echo "$(GREEN)✅ Camagru is ready!$(NC)"

.PHONY: full-restart
full-restart: down up db-setup ## Quick: Full restart with database reset
	@echo "$(GREEN)✅ Full restart complete!$(NC)"

.PHONY: fresh
fresh: clean-all install ## Quick: Fresh installation (⚠️ DELETES DATA)

# ============================================================
# Information
# ============================================================

.PHONY: info
info: ## Show project information
	@echo "$(BLUE)╔═══════════════════════════════════════════════════╗$(NC)"
	@echo "$(BLUE)║$(NC)         $(GREEN)Camagru Project Information$(NC)           $(BLUE)║$(NC)"
	@echo "$(BLUE)╠═══════════════════════════════════════════════════╣$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)Project:$(NC)      Camagru                          $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)Version:$(NC)      Docker 1.0                       $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)PHP:$(NC)          8.1 + Apache                     $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)Database:$(NC)     MySQL 8.0                        $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)Author:$(NC)       Hussein Khrayzat                 $(BLUE)║$(NC)"
	@echo "$(BLUE)║$(NC) $(YELLOW)Date:$(NC)         November 27, 2025                $(BLUE)║$(NC)"
	@echo "$(BLUE)╚═══════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@make status

.PHONY: version
version: ## Show Docker versions
	@echo "$(BLUE)📦 Docker Versions:$(NC)"
	@docker --version
	@docker-compose --version

# ============================================================
# Testing
# ============================================================

.PHONY: test
test: ## Run all tests
	@echo "$(BLUE)🧪 Running tests...$(NC)"
	@docker exec $(WEB_CONTAINER) php /var/www/html/test_database.php

.PHONY: test-db
test-db: ## Test database connection
	@echo "$(BLUE)🧪 Testing database connection...$(NC)"
	@docker exec $(DB_CONTAINER) mysql -ucamagru_user -pcamagru_pass camagru -e "SELECT 'Connection OK' as status;" 2>&1 | grep -v "Warning" || true
