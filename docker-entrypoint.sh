#!/bin/bash
set -e

# Configure Apache to listen on the PORT provided by Render (default 10000)
PORT="${PORT:-10000}"

sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-enabled/000-default.conf

# Seed the SQLite database if MySQL is not configured
if [ -z "$DB_HOST" ]; then
    echo "[entrypoint] No DB_HOST set — initialising SQLite database..."
    # Copy the bundled SQLite DB to the persistent volume if not already there
    SQLITE_PATH="/var/www/html/database/ruiru_realestate.sqlite"
    if [ ! -f "$SQLITE_PATH" ]; then
        php /var/www/html/database/init_sqlite.php && echo "[entrypoint] SQLite DB created."
    else
        echo "[entrypoint] SQLite DB already exists, skipping seed."
    fi
else
    echo "[entrypoint] DB_HOST=${DB_HOST} — will use MySQL."
fi

exec "$@"
