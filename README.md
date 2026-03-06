# Laravel Helpdesk

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

## Local setup

```bash
git clone <REPO_URL>
cd laravel-helpdesk

cp .env.example .env
docker compose up -d --build

docker exec -it helpdesk-app php artisan key:generate
docker exec -it helpdesk-app php artisan migrate
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

Seeders create demo users in local environment (password: password).
