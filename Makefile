.PHONY: up down ps logs backend-shell frontend-shell backend-test backend-lint backend-lint-check frontend-lint frontend-lint-check frontend-build lint test build ci

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

frontend-shell:
	docker compose exec frontend sh

backend-test:
	docker compose exec backend composer test

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
