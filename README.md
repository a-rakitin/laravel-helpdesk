# Laravel Helpdesk API

[English](README.md) | [Русский](README.ru.md)

[![CI](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml)

Helpdesk API built with Laravel 12 for support tickets, comments, notifications, and user role management. The project environment runs in Docker containers using Docker Compose.

- **Application:** <https://helpdesk.rakitin.tech>
- **API documentation:** <https://helpdesk.rakitin.tech/docs/api>

## API Features

- Registration, login, and logout with Laravel Sanctum.
- Access control for `customer`, `agent`, and `admin` roles.
- Ticket creation and viewing, search, filtering, sorting, and pagination.
- Ticket assignment and status changes.
- Adding and viewing ticket comments.
- Email notifications for new comments and notification listing.
- User listing and role management by admins.
- API rate limiting.

## Tech Stack

- Laravel 12
- PHP 8.4
- PostgreSQL 18
- Redis
- Nginx
- Docker and Docker Compose

## Local Setup

Requirements:

- Git
- Docker with Docker Compose

Recommended Docker setup:

- Windows: Docker Desktop with the WSL 2 backend.
- macOS: Docker Desktop.
- Linux: Docker Engine and Docker Compose.

Clone the repository and start the application:

```shell
git clone https://github.com/a-rakitin/laravel-helpdesk.git
cd laravel-helpdesk

cp .env.example .env
docker compose up -d --build

docker exec -it helpdesk-app composer install --no-interaction
docker exec -it helpdesk-app php artisan key:generate
docker exec -it helpdesk-app php artisan migrate --seed
docker exec -it helpdesk-app php artisan optimize:clear
```

The defaults in `.env.example` (`APP_ENV=local`, `QUEUE_CONNECTION=sync`) enable demo seeders and process notifications during the HTTP request.

After startup:

- **Application:** <http://localhost>
- **Email inbox (Mailpit):** <http://localhost:8025>

Demo accounts share the password `password`:

- `admin`: `qa-admin@example.com`
- `agent`: `qa-agent@example.com`

For background notifications, set `QUEUE_CONNECTION=redis` in `.env` and run:

```shell
docker exec -it helpdesk-app php artisan optimize:clear
docker compose restart worker
```

## API Usage

Scramble generates OpenAPI JSON; Scalar displays the interactive documentation. Local addresses:

- **Interactive documentation:** <http://localhost/docs/api>
- **OpenAPI JSON:** <http://localhost/docs/api.json>

1. Open the documentation and call `POST /api/auth/register` or `POST /api/auth/login`.
2. Copy the token from the response.
3. Include the token in the header of protected requests:

```http
Authorization: Bearer YOUR_TOKEN
```

To check the API with Postman, import the [collection](postman/helpdesk-api.local.collection.json) and [environment](postman/helpdesk-api.local.environment.json), then select that environment. It targets `http://localhost` and uses the demo accounts listed above.

## Tests and Checks

PHPUnit tests cover API workflows, authorization, validation, response formats, and OpenAPI documentation.

Run PHPUnit tests:

```shell
docker exec helpdesk-app composer run test
```

Check PHP code style with Laravel Pint:

```shell
docker exec helpdesk-app ./vendor/bin/pint --test
```

Check PHP dependencies for known vulnerabilities:

```shell
docker exec helpdesk-app composer audit
```

GitHub Actions runs tests, PHP code style checks, and a PHP dependency audit on pull requests and pushes to `main`.

## Deployment

Production deployment is described in [DEPLOY.md](DEPLOY.md).
