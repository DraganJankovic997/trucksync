.PHONY: install dev migrate import-countries create-roles assign-admin seed refresh-db test lint lint-check

install:
	composer install

dev:
	php artisan serve --host=0.0.0.0 --port=8000

migrate:
	php artisan migrate
	php artisan countries:import
	php artisan roles:create

import-countries:
	php artisan countries:import

create-roles:
	php artisan roles:create

assign-admin:
	php artisan roles:assign-admin "$(EMAIL)"

seed:
	php artisan db:seed

refresh-db:
	php artisan migrate:fresh --seed

test:
	composer test

lint:
	composer lint

lint-check:
	composer lint:check
