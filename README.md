# Laravel Helpdesk API

Ticket system for a helpdesk workflow (tickets, comments, roles) built with Laravel.  

Dockerized setup with PostgreSQL + Redis + Nginx. Includes Sanctum auth, policies, tests, and CI.

## Tech stack

- Laravel 12
- PHP **8.4** (Docker runtime), compatible with PHP **8.2+** (composer constraint)
- PostgreSQL 18
- Redis (cache/queues)
- Nginx
- Laravel Sanctum
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
```

For stable local Postman checks, make sure your `.env` uses:

```env
APP_ENV=local
QUEUE_CONNECTION=sync
```

Open: http://localhost

# Useful commands

## Run tests

```bash
docker exec -it helpdesk-app php artisan test
```

## Code style (Pint)

```bash
docker exec -it helpdesk-app ./vendor/bin/pint --test
docker exec -it helpdesk-app ./vendor/bin/pint
```

# Demo users (local only)

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
