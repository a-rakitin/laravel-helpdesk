# AGENTS.md

## Project Overview

- This repository is a Laravel Helpdesk API for ticket, comment, notification, and role-based support workflows.
- Primary runtime is Docker Compose with PHP 8.4, PostgreSQL 18, Redis, and Nginx.
- The Composer constraint allows PHP 8.2+, but local and CI behavior should be aligned with the Docker/CI PHP 8.4 runtime when possible.
- The API uses Laravel Sanctum for token authentication and Scramble for OpenAPI documentation.

## Setup And Runtime

- Prefer Docker-based commands because the README and CI are built around the `helpdesk-app` container.
- Local bootstrap:
  - `cp .env.example .env`
  - `docker compose up -d --build`
  - `docker exec -it helpdesk-app php artisan key:generate`
  - `docker exec -it helpdesk-app php artisan migrate --seed`
  - `docker exec -it helpdesk-app php artisan optimize:clear`
- For stable local API/Postman checks, keep `APP_ENV=local` and `QUEUE_CONNECTION=sync` in `.env`.
- Do not commit `.env`, generated caches, or local IDE/runtime artifacts.

## Common Commands

- Run the backend test suite: `docker exec -it helpdesk-app php artisan test`
- Check PHP style: `docker exec -it helpdesk-app ./vendor/bin/pint --test`
- Format PHP code: `docker exec -it helpdesk-app ./vendor/bin/pint`
- Run migrations: `docker exec -it helpdesk-app php artisan migrate`
- Clear Laravel caches after config/route/env changes: `docker exec -it helpdesk-app php artisan optimize:clear`
- Build frontend assets when files under `resources/` or Vite config change: `npm run build`
- Non-Docker local helper scripts are available through Composer: `composer run dev`, `composer run test`, `composer run lint`, and `composer run format`.

## Quality Gates

- Before finishing PHP behavior changes, run `docker exec -it helpdesk-app php artisan test`.
- Before opening or updating a PR, run both `docker exec -it helpdesk-app ./vendor/bin/pint --test` and `docker exec -it helpdesk-app php artisan test`.
- If frontend asset files change, also run `npm run build`.
- Add or update feature tests for API behavior, authorization rules, notification behavior, validation changes, and database-backed workflow changes.
- Prefer focused tests in `tests/Feature/...` that cover the user-visible API contract and policy outcomes.

## Git And Commits

- Use conventional commit-style messages matching the repository history, such as `feat: ...`, `fix: ...`, `refactor: ...`, `test: ...`, `docs: ...`, `chore: ...`, and `perf: ...`.
- Before committing, inspect recent commit messages with `git log --oneline -8` and choose a message that fits the existing style.
- Keep commit messages short, imperative, and scoped to the actual change.

## Coding Guidelines

- Follow existing Laravel 12 conventions and the current project structure under `app/`, `routes/`, `database/`, and `tests/`.
- Keep controllers focused on HTTP concerns; put validation in request classes when validation grows beyond trivial inline rules.
- Use policies for authorization decisions, especially around tickets, comments, and role-sensitive actions.
- Keep role logic aligned with `App\Enums\UserRole`; avoid scattering raw role strings through new code.
- Keep ticket status and priority logic aligned with `App\Enums\TicketStatus` and `App\Enums\TicketPriority`.
- Use Eloquent relationships, factories, seeders, and migrations rather than ad hoc SQL unless there is a strong reason.
- For database changes, add migrations and keep seeders/factories in sync when test data needs to reflect the new shape.
- Do not introduce new production dependencies without a clear reason and user confirmation.

## API And Documentation

- API routes live in `routes/api.php`; public auth endpoints are `POST /api/auth/register` and `POST /api/auth/login`.
- Protected endpoints should continue to require a valid Sanctum Bearer token.
- Keep Scramble/OpenAPI annotations and route behavior in sync when API inputs, responses, or auth requirements change.
- Local docs endpoints are `http://localhost/docs/api` and `http://localhost/docs/api.json`.
- Postman assets live under `postman/`; update them when request/response contracts change.

## Deployment Notes

- Production deployment is documented in `DEPLOY.md` and handled by `./deploy.sh`.
- Do not change production deploy behavior casually; if deployment scripts or Compose files change, verify the documented deploy flow remains accurate.
- Queue workers should be restarted after production code changes; preserve this behavior in deploy-related edits.
