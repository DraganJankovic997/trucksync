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
and then starts `php artisan serve`. Backend and database configuration is
loaded from `trucksync-backend/.env`; keep `DB_HOST=postgres` and
`REDIS_HOST=redis` when running the app through Compose.

Useful commands:

```sh
make backend-test
make frontend-lint-check
make frontend-build
make down
```

Override app ports with environment variables before running Compose, for
example:

```sh
BACKEND_PORT=8080 FRONTEND_PORT=9001 docker compose up --build
```
