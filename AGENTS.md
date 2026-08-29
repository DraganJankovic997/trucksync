# TruckSync Agent Guide

## Project

- Backend: Laravel app in `trucksync-backend`.
- Frontend: Quasar/Vue app in `trucksync-frontend`.
- Prefer small, reviewable changes.
- Use Docker Compose for full-stack checks when possible.

## Common Commands

- Start the stack: `make up`
- Stop the stack: `make down`
- Check status: `make ps`
- Run lint checks: `make lint`
- Run tests/build checks: `make ci`

## Working Rules

- Do not commit or push unless explicitly asked.
- Keep unrelated refactors out of feature work.
- Update the relevant README or Makefile when changing local workflow commands.
- Update Swagger documentation when making API changes
- Make sure all env files are synced (including .env.example)
- Report verification commands and results before finishing.
