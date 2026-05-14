# Laravel Helpdesk API

[![CI](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml)

Helpdesk ticket workflow API built with Laravel 12. The project includes Sanctum token authentication, role-based access control, PostgreSQL, Redis, Docker, automated tests, CI, and OpenAPI documentation generated with Scramble.

## Links

- Live API root: https://helpdesk.rakitin.tech
- Live interactive docs: https://helpdesk.rakitin.tech/api-docs.html
- Live OpenAPI JSON: https://helpdesk.rakitin.tech/docs/api.json
- Local interactive docs: http://localhost/docs/api
- Local OpenAPI JSON: http://localhost/docs/api.json

## Project Highlights

- Authentication with Laravel Sanctum
- Roles for `admin`, `agent`, and `customer`
- Policy-based authorization for tickets, comments, and notifications
- Ticket workflow: create, list, view, assign, update status, comment
- Notification endpoints for helpdesk events
- PostgreSQL-backed persistence with migrations, factories, and seeders
- Redis-ready cache, queue, and session configuration
- Dockerized PHP 8.4 runtime with PostgreSQL 18, Redis, and Nginx
- Basic API rate limiting for auth and protected endpoints
- Scramble-generated interactive docs and OpenAPI JSON
- PHPUnit feature tests, Laravel Pint, and GitHub Actions CI

## Quick Demo Flow

### Live docs

1. Open https://helpdesk.rakitin.tech/api-docs.html.
2. Review the public auth endpoints:
   - `POST /api/auth/register`
   - `POST /api/auth/login`
3. Register or log in to get a Sanctum token.
4. Use `Authorization: Bearer <token>` for protected ticket, comment, notification, and logout endpoints.
5. Check the generated OpenAPI contract at https://helpdesk.rakitin.tech/docs/api.json.

### Local Postman check

Run the project locally for the complete API flow. The included Postman collection creates fresh customer users, logs in seeded agent/admin users, creates tickets, checks validation errors, authorization boundaries, not-found responses, comments, notifications, and verifies that logout invalidates the current token.

Postman assets:

- `postman/helpdesk-api.local.collection.json`
- `postman/helpdesk-api.local.environment.json`

Local demo users created by seeders use password `password`:

- `qa-admin@example.com`
- `qa-agent@example.com`

The Postman environment uses:

- `base_url`: `http://localhost`
- `admin`: `qa-admin@example.com` / `password`
- `agent`: `qa-agent@example.com` / `password`

## Tech Stack

- Laravel 12
- PHP 8.4 in Docker and CI, with Composer compatibility set to PHP 8.2+
- PostgreSQL 18
- Redis 7
- Nginx
- Laravel Sanctum
- Scramble for OpenAPI docs
- PHPUnit feature tests
- Laravel Pint
- GitHub Actions

## Local Setup

Requirements:

- Docker + Docker Compose
- Postman for collection-based API checks

```bash
git clone https://github.com/a-rakitin/laravel-helpdesk.git
cd laravel-helpdesk

cp .env.example .env
docker compose up -d --build

docker exec -it helpdesk-app php artisan key:generate
docker exec -it helpdesk-app php artisan migrate --seed
docker exec -it helpdesk-app php artisan optimize:clear
```

For stable local API and Postman checks, keep these values in `.env`:

```env
APP_ENV=local
QUEUE_CONNECTION=sync
```

The Docker PostgreSQL service reads `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`
from `.env`. The example password is local-only; use a strong unique value in
production before creating or recreating the database container.

Open the local API at http://localhost.

## API Documentation

Scramble generates the API documentation from the Laravel application.

Documentation endpoints:

- Local interactive docs: `http://localhost/docs/api`
- Local OpenAPI JSON: `http://localhost/docs/api.json`
- Production interactive docs: `https://helpdesk.rakitin.tech/api-docs.html`
- Production OpenAPI JSON: `https://helpdesk.rakitin.tech/docs/api.json`

Authentication model:

- Public endpoints: `POST /api/auth/register`, `POST /api/auth/login`
- Protected endpoints: tickets, comments, notifications, and logout
- Protected requests require a valid Sanctum Bearer token
- Auth endpoints are rate limited more strictly than authenticated API endpoints

Example header:

```http
Authorization: Bearer YOUR_TOKEN
```

## Verification Commands

Composer metadata:

```bash
composer validate
```

PHP style:

```bash
docker exec -it helpdesk-app ./vendor/bin/pint --test
```

Backend tests:

```bash
docker exec -it helpdesk-app php artisan test
```

Frontend assets only need rebuilding when files under `resources/` or Vite configuration change:

```bash
npm run build
```

## Deployment

Production deployment is documented in [`DEPLOY.md`](DEPLOY.md).

Recommended production deploy:

```bash
./deploy.sh
```
