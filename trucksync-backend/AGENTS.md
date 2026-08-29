# Backend Agent Guide

## Scope

- Laravel backend lives in this directory.
- Prefer `composer` scripts and this directory's `Makefile`.
- Keep application configuration in `.env`; Docker uses `DB_*` values from it.

## Commands

- Install dependencies: `make install`
- Run migrations: `make migrate`
- Run tests: `make test`
- Fix style: `make lint`
- Check style: `make lint-check`

## Rules

- Add or update Pest tests for backend behavior changes.
- Use Laravel conventions for controllers, models, migrations, validation, and config.
- Follow the Controller - Services - Models pattern for implementing the business logic
- Do not edit `.env` unless the task is explicitly about local environment setup.
