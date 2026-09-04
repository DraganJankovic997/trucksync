.PHONY: up down ps logs backend-shell frontend-shell backend-assign-admin assign-admin backend-test backend-lint backend-lint-check frontend-lint frontend-lint-check frontend-build lint test build ci

up:
	docker compose up -d --build

down:
	docker compose down

ps:
	docker compose ps

logs:
	docker compose logs -f

backend-shell:
	docker compose exec backend sh

backend-assign-admin:
	docker compose exec backend php artisan roles:assign-admin "$(EMAIL)"

assign-admin: backend-assign-admin

frontend-shell:
	docker compose exec frontend sh

backend-test:
	docker compose --profile test up -d --wait postgres-test
	docker compose run --rm --no-deps \
		-e APP_ENV=testing \
		-e DB_CONNECTION=pgsql \
		-e DB_HOST=postgres-test \
		-e DB_PORT=5432 \
		-e DB_DATABASE=trucksync_backend_test \
		-e DB_USERNAME=trucksync_test \
		-e DB_PASSWORD=trucksync_test \
		-e DB_URL= \
		backend composer test

backend-lint:
	docker compose exec backend composer lint

backend-lint-check:
	docker compose exec backend composer lint:check

frontend-lint:
	docker compose exec frontend npm run lint

frontend-lint-check:
	docker compose exec frontend npm run lint:check

frontend-build:
	docker compose exec frontend npm run build

lint: backend-lint-check frontend-lint-check

test: backend-test

build: frontend-build

ci: lint test build
