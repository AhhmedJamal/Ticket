#!/bin/bash
set -e

# Install composer dependencies if vendor is missing
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure storage directories exist and have proper permissions
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Generate app key if missing
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    php artisan key:generate --force || true
fi

# Run migrations if database is reachable
echo "Waiting for database connection..."
php artisan migrate --force --no-interaction || echo "Database not ready yet or migrations already up to date."

exec "$@"

