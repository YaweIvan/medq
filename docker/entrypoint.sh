#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set — generating a temporary key." >&2
    echo "         Sessions will be invalidated on every restart." >&2
    echo "         Set APP_KEY in your environment (php artisan key:generate --show)." >&2
    export APP_KEY="$(php artisan key:generate --show)"
fi

php artisan config:cache
php artisan view:cache
php artisan storage:link --force
chown -R www-data:www-data storage public/sounds

tries=0
until php -r '$dsn = "mysql:host=" . getenv("DB_HOST") . ";port=" . (getenv("DB_PORT") ?: 3306); new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' 2>/dev/null; do
    tries=$((tries + 1))
    if [ "$tries" -ge 30 ]; then
        echo "Database is not reachable, giving up." >&2
        exit 1
    fi
    echo "Waiting for database..."
    sleep 2
done

php artisan migrate --force

exec apache2-foreground
