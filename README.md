# TruckSync

## Docker development

Run the full local stack in containers:

```sh
docker compose up --build
```

Services:

- Backend: http://localhost:8000
- Frontend: http://localhost:9000
- PostgreSQL: localhost:5432
- Redis: localhost:6379

The backend container waits for PostgreSQL and Redis, runs Laravel migrations,
and then starts `php artisan serve`. Backend configuration is loaded from
`trucksync-backend/.env`; keep `DB_HOST=postgres` and `REDIS_HOST=redis` when
running the app through Compose.

Useful commands:

```sh
docker compose exec backend php artisan test
docker compose exec frontend npm run lint:check
docker compose exec frontend npm run build
docker compose down
```

Override ports or database credentials with environment variables before running
Compose, for example:

```sh
BACKEND_PORT=8080 FRONTEND_PORT=9001 docker compose up --build
```
