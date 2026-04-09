# Laravel Helpdesk API

Ticket system for a helpdesk workflow built with Laravel.

Dockerized setup with PostgreSQL, Redis, and Nginx. Includes Sanctum authentication, role-based access control, automated tests, CI, and interactive API documentation generated with Scramble.

## Project highlights

- Authentication with Laravel Sanctum
- Ticket management workflow
- Ticket comments
- Notifications
- Role-based access control (`admin`, `agent`, `customer`)
- Policy-based authorization
- Interactive API documentation with OpenAPI JSON
- Feature tests and CI checks

## Tech stack

- Laravel 12
- PHP **8.4** (Docker runtime), compatible with PHP **8.2+** (composer constraint)
- PostgreSQL 18
- Redis (cache/queues)
- Nginx
- Laravel Sanctum
- Scramble (OpenAPI docs)
- PHPUnit feature tests
- Laravel Pint (code style)
- GitHub Actions (CI)

## Requirements

- Docker + Docker Compose
- Postman

## Local setup

```bash
git clone https://github.com/a-rakitin/laravel-helpdesk.git
cd laravel-helpdesk

cp .env.example .env
docker compose up -d --build

docker exec -it helpdesk-app php artisan key:generate
docker exec -it helpdesk-app php artisan migrate --seed
docker exec -it helpdesk-app php artisan optimize:clear
```

For stable local Postman checks, make sure your `.env` uses:

```env
APP_ENV=local
QUEUE_CONNECTION=sync
```

Open: http://localhost

## Deployment

Production deploy instructions are documented in [`DEPLOY.md`](DEPLOY.md).

Recommended production deploy:

```bash
./deploy.sh
```

## API documentation

The project includes API documentation generated from Laravel code with Scramble.

Available documentation endpoints:

- Local interactive docs: `http://localhost/docs/api`
- Local OpenAPI JSON: `http://localhost/docs/api.json`
- Production interactive docs: `https://helpdesk.rakitin.tech/api-docs.html`
- Production OpenAPI JSON: `https://helpdesk.rakitin.tech/docs/api.json`

### Authentication in docs

Public endpoints:

- `POST /api/auth/register`
- `POST /api/auth/login`

Protected endpoints require a Bearer token.

How to test authenticated requests:

1. Call `POST /api/auth/register` or `POST /api/auth/login`
2. Copy the returned token
3. Use it as a Bearer token for protected endpoints

Example authorization header:

```http
Authorization: Bearer YOUR_TOKEN
```

### Notes

- The API uses Laravel Sanctum for token authentication
- Public auth endpoints are available without authentication
- Protected endpoints such as tickets and notifications require a valid token

## Quality

- Feature tests for core API flows
- Policy-based authorization checks
- Code style enforced with Laravel Pint
- CI pipeline via GitHub Actions

## Useful commands

### Run tests

```bash
docker exec -it helpdesk-app php artisan test
```

### Code style (Pint)

```bash
docker exec -it helpdesk-app ./vendor/bin/pint --test
docker exec -it helpdesk-app ./vendor/bin/pint
```

## Demo users (local only)

Seeders create demo users in local environment (password: `password`):

- `qa-admin@example.com`
- `qa-agent@example.com`

## Postman

Local Postman files:

- `postman/helpdesk-api.local.collection.json`
- `postman/helpdesk-api.local.environment.json`

The local environment uses:

- `base_url`: `http://localhost`
- `admin`: `qa-admin@example.com` / `password`
- `agent`: `qa-agent@example.com` / `password`

The collection also creates fresh customer users during the run and covers auth, tickets, comments, notifications, and logout.
