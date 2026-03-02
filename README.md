# Helpdesk (Ticket System) API — Laravel

Backend API helpdesk-системы (tickets/comments/roles) на Laravel + PostgreSQL + Redis, упаковано в Docker Compose.

## Tech Stack
- Laravel (latest stable)
- PHP 8.4 (php-fpm, custom image)
- PostgreSQL
- Redis (cache/queue)
- Nginx
- PHPUnit (feature tests)
- Sanctum (auth)

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
