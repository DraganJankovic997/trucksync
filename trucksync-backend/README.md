# TruckSync Backend

Laravel backend for TruckSync.

## Setup

From the repository root, start the Docker stack:

```sh
make up
```

For local-only development:

```sh
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

If you are not using Docker, set `DB_HOST=127.0.0.1` and
`REDIS_HOST=127.0.0.1` in `.env`.

## Commands

```sh
make dev
make migrate
make test
make lint-check
make lint
```

## API Documentation

Swagger UI is available at:

```txt
/api/documentation
```

The OpenAPI JSON document is served from:

```txt
/api/documentation/openapi.json
```

## Docker

From the repository root:

```sh
make up
make backend-test
make backend-lint-check
```

The Docker stack uses `trucksync-backend/.env`. For Compose, keep:

```env
DB_HOST=postgres
REDIS_HOST=redis
```
