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
php artisan countries:import
php artisan roles:create
```

If you are not using Docker, set `DB_HOST=127.0.0.1` and
`REDIS_HOST=127.0.0.1` in `.env`.

For local-only tests, use a separate PostgreSQL test database. The test suite
is configured for `trucksync_backend_test` and refuses to run against databases
whose names do not end in `_test`.

## Commands

```sh
make dev
make migrate
make import-countries
make create-roles
php artisan roles:assign-admin admin@example.com
make assign-admin EMAIL=admin@example.com
make seed
make refresh-db
make test
make lint-check
make lint
```

`make refresh-db` runs `php artisan migrate:fresh --seed` and drops/recreates
the database configured in `.env`.

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

`make backend-test` runs the backend suite against the isolated
`postgres-test` Compose service and `trucksync_backend_test` database, not the
development database.

The Docker stack uses `trucksync-backend/.env`. For Compose, keep:

```env
DB_HOST=postgres
REDIS_HOST=redis
```
