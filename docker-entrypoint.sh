#!/bin/bash
set -e

# Wait for database to be ready (Railway provides DATABASE_URL)
if [ -n "$DATABASE_URL" ] || [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection..."
    max_attempts=30
    attempt=0
    until php artisan migrate:status &> /dev/null || [ $attempt -ge $max_attempts ]; do
        attempt=$((attempt + 1))
        echo "Database connection attempt $attempt/$max_attempts..."
        sleep 2
    done
    
    if [ $attempt -lt $max_attempts ]; then
        echo "Database is up - executing migrations"
        php artisan migrate --force || true
    else
        echo "Warning: Could not connect to database. Migrations will be skipped."
    fi
fi

# Cache Laravel config if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Start PHP server
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

