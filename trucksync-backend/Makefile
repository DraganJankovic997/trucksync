.PHONY: install dev migrate import-countries test lint lint-check

install:
	composer install

dev:
	php artisan serve --host=0.0.0.0 --port=8000

migrate:
	php artisan migrate
	php artisan countries:import

import-countries:
	php artisan countries:import

test:
	composer test

lint:
	composer lint

lint-check:
	composer lint:check
