#!/bin/bash
set -e

PORT="${PORT:-10000}"

echo "[entrypoint] Configuring Apache for PORT=${PORT}..."

# Configure Apache listening ports safely
if [ "$PORT" = "80" ]; then
    echo "Listen 80" > /etc/apache2/ports.conf
else
    cat <<EOF > /etc/apache2/ports.conf
Listen 80
Listen ${PORT}
EOF
fi

# Configure default VirtualHost
cat <<EOF > /etc/apache2/sites-available/000-default.conf
<VirtualHost *:80 *:${PORT}>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

a2ensite 000-default.conf >/dev/null 2>&1 || true

# Suppress Apache FQDN warning
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null 2>&1 || true

# Seed SQLite database if not present and no external MySQL configured
if [ -z "$DB_HOST" ] && [ -z "$DATABASE_URL" ] && [ -z "$MYSQL_URL" ]; then
    echo "[entrypoint] Zero-config SQLite mode active."
    SQLITE_PATH="/var/www/html/database/ruiru_realestate.sqlite"
    if [ ! -f "$SQLITE_PATH" ] || [ ! -s "$SQLITE_PATH" ]; then
        php /var/www/html/database/init_sqlite.php || true
    fi
else
    echo "[entrypoint] External MySQL database configured."
fi

# Ensure web server has read/write permissions for SQLite database and uploads
mkdir -p /var/www/html/database /var/www/html/uploads/properties /var/www/html/uploads/agents /var/www/html/uploads/blog
chown -R www-data:www-data /var/www/html/database /var/www/html/uploads
chmod -R 775 /var/www/html/database /var/www/html/uploads

echo "[entrypoint] Apache configured. Starting server on ports 80 and ${PORT}..."
exec "$@"
