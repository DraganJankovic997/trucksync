#!/usr/bin/env sh
set -e

php artisan config:clear --ansi
php artisan migrate --force
php artisan countries:import --ansi
php artisan roles:create --ansi

exec php artisan serve --host=0.0.0.0 --port=8000
