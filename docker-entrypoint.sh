#!/bin/bash
# Docker entrypoint script to fix permissions

echo "Configuring permissions for mounted volumes..."

# Make all files and directories world-readable and executable
# This is necessary because Docker volume mounts can prevent proper permission changes
chmod -R 777 /var/www/html 2>/dev/null || true

# Ensure upload directories exist with proper permissions
mkdir -p /var/www/html/public/uploads/albums
mkdir -p /var/www/html/public/uploads/profiles
mkdir -p /var/www/html/public/uploads/collages
mkdir -p /var/www/html/public/uploads/stories

# Ensure uploads are writable
chmod -R 777 /var/www/html/public/uploads 2>/dev/null || true

echo "Permissions configured. Starting Apache..."

# Execute the CMD passed to the container
exec "$@"
