#!/bin/bash
set -e

PORT="${PORT:-80}"
echo "[entrypoint] Starting container on port ${PORT}..."

# Update Apache port
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Suppress FQDN warning
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

# Seed SQLite database if not present and no external MySQL configured
if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ] && [ -z "$MYSQL_URL" ]; then
    SQLITE_PATH="/var/www/html/database/ruiru_realestate.sqlite"
    if [ ! -f "$SQLITE_PATH" ] || [ ! -s "$SQLITE_PATH" ]; then
        php /var/www/html/database/init_sqlite.php || true
    fi
fi

# Ensure permissions
mkdir -p /var/www/html/database /var/www/html/uploads
chown -R www-data:www-data /var/www/html/database /var/www/html/uploads
chmod -R 775 /var/www/html/database /var/www/html/uploads

echo "[entrypoint] Executing: $@"
exec "$@"
