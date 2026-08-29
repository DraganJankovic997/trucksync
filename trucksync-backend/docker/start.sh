#!/usr/bin/env sh
set -e

php artisan config:clear --ansi
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port=8000
