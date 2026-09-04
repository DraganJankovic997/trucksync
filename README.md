# TruckSync

TruckSync is a small Laravel + Quasar project.

## Projects

- Backend: `trucksync-backend`
- Frontend: `trucksync-frontend`
- Database: PostgreSQL
- Cache/queue support: Redis

## Commands

```sh
make up
make ps
make lint
make test
make build
make down
```

## Docker development

Run the full local stack in containers:

```sh
make up
```

Services:

- Backend: http://localhost:8000
- Frontend: http://localhost:9000
- PostgreSQL: localhost:5432
- Redis: localhost:6379

The backend container waits for PostgreSQL and Redis, runs Laravel migrations,
imports countries from `trucksync-backend/database/data/countries.json`, creates
the default admin role, and then starts `php artisan serve`. Backend and
database configuration is loaded from `trucksync-backend/.env`; keep
`DB_HOST=postgres` and `REDIS_HOST=redis` when running the app through Compose.

Useful commands:

```sh
make backend-test
make backend-seed
make backend-refresh-db
make backend-assign-admin EMAIL=admin@example.com
make frontend-lint-check
make frontend-build
make down
```

`make backend-test` starts a separate `postgres-test` service under the
Compose `test` profile and runs backend tests against `trucksync_backend_test`.
It does not use the development `postgres` service or `trucksync_backend`
database.

`make backend-seed` runs Laravel seeders in the backend container.
`make backend-refresh-db` runs `php artisan migrate:fresh --seed` in the
backend container and drops/recreates the configured development database.

Override app ports with environment variables before running Compose, for
example:

```sh
BACKEND_PORT=8080 FRONTEND_PORT=9001 docker compose up --build
```
