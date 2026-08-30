# Backend Agent Guide

## Important

- MUST create a contract/interface for every service in `app/Contracts`.
- MUST bind each service contract to its implementation in a service provider.
- MUST inject service contracts, not concrete service classes, into controllers and other consumers.
- MUST use explicit, readable service method parameters instead of passing generic `array $data` payloads.
- MUST validate request data before calling services, then pass the validated values explicitly.

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
- Do not edit `.env` unless the task is explicitly about local environment setup (and report env changes loudly)

## Backend Architecture

- Follow the Controller -> Service -> Model flow for business features.
- Controllers handle auth/authorization, request validation, request unpacking, service calls, and HTTP responses.
- Services hold business logic and call Eloquent models to read or change data.
- Models represent persistence and should not contain request/response handling.
- Prefer constructor injection for dependencies.

## Folder Conventions

- Controllers: `app/Http/Controllers`
- Form requests: `app/Http/Requests`
- Services: `app/Services`
- Service contracts/interfaces: `app/Contracts`
- Business exceptions: `app/Exceptions`
- Eloquent models: `app/Models`

## Services

- Define a contract/interface for each service and type against the contract.
- Keep service method signatures and return types strongly typed.
- Services should work with original Eloquent models and explicit validated method arguments, not DTOs or generic data arrays.
- Do not introduce DTOs for service input/output or service-to-service communication.
- If a service creates a model, pass only already-validated values needed for that model creation.
- Validate and normalize input rigorously before querying or creating records so service flows can trust required fields and avoid defensive null checks.
- Throw custom business exceptions for expected business faults.
- Do not return HTTP responses from services.

## Controllers

- Validate input in the controller. Use custom Form Request classes for reusable or complex validation.
- Keep controller request handling focused on validation, authorization, service calls, and response shaping.
- Catch expected custom business exceptions and map them to appropriate HTTP status codes.
- Keep a final catch-all for unexpected `Throwable` values.
- Log only unexpected exceptions in the catch-all path.
- Do not log expected failures such as invalid credentials, not-found business cases, invalid state transitions, or validation-style business faults.
- If an exception represents a broken invariant that should be impossible, treat it as unexpected and log it in the catch-all path.

## Exceptions

- Use custom exception classes for business logic faults.
- Expected business exceptions should carry enough context for the controller to choose the response without exposing sensitive data.
- Unexpected exceptions should be logged with useful context and return a generic 500 response.

## API Documentation

- Update Swagger/OpenAPI documentation whenever API routes, request payloads, response payloads, or status codes change.
- Make Swagger/OpenAPI match the actual implementation, including validation rules and error responses.
- If no Swagger/OpenAPI source exists yet, ask before introducing a package or documentation structure.

## Tests

- Use Pest for backend tests.
- Add feature tests for controller behavior, validation, status codes, and API response shapes.
- Add service tests for non-trivial business rules and custom business exceptions.
- Assert that expected business exceptions are handled without logging as errors when practical.
- Use factories for test data; avoid depending on seeders unless the test is explicitly about seeded data.

## Migrations

- Use migrations to enforce data integrity with required columns, defaults, foreign keys, unique constraints, and indexes where appropriate.
- Mirror important validation assumptions at the database level when practical.
- Keep migrations reversible.
- Ask before adding destructive migrations or irreversible data changes.
