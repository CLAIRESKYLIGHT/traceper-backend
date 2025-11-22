#!/bin/bash
set -e

# Cache Laravel config if APP_KEY is set
if [ -n "$APP_KEY" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Start PHP server
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

