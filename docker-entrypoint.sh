#!/bin/bash
set -e

# Configure Apache to listen on the PORT provided by Render (default 10000)
PORT="${PORT:-10000}"

echo "[entrypoint] Configuring Apache on port ${PORT}..."

# Replace Apache listening port across all configuration files
sed -i -e "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i -e "s/:80/:${PORT}/g" /etc/apache2/sites-available/*.conf 2>/dev/null || true
sed -i -e "s/:80/:${PORT}/g" /etc/apache2/sites-enabled/*.conf 2>/dev/null || true

# Suppress FQDN warning
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

# Seed SQLite database if not present and no external MySQL configured
if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ] && [ -z "$MYSQL_URL" ]; then
    echo "[entrypoint] Zero-config SQLite mode active."
    SQLITE_PATH="/var/www/html/database/ruiru_realestate.sqlite"
    if [ ! -f "$SQLITE_PATH" ] || [ ! -s "$SQLITE_PATH" ]; then
        php /var/www/html/database/init_sqlite.php
    fi
else
    echo "[entrypoint] External MySQL database configured."
fi

# Ensure web server has read/write permissions for SQLite database and uploads
mkdir -p /var/www/html/database /var/www/html/uploads/properties /var/www/html/uploads/agents /var/www/html/uploads/blog
chown -R www-data:www-data /var/www/html/database /var/www/html/uploads
chmod -R 775 /var/www/html/database /var/www/html/uploads

echo "[entrypoint] Ready. Starting Apache..."
exec "$@"
