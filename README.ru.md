# Laravel Helpdesk API

[English](README.md) | [Русский](README.ru.md)

[![CI](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml)

Helpdesk API для работы с тикетами, построенный на Laravel 12. Проект включает аутентификацию через Sanctum, ролевую модель доступа, PostgreSQL, Redis, Docker, автоматизированные тесты, CI и OpenAPI-документацию, сгенерированную через Scramble.

## Ссылки

- Главная страница: https://helpdesk.rakitin.tech
- Интерактивная документация: https://helpdesk.rakitin.tech/docs/api
- OpenAPI JSON: https://helpdesk.rakitin.tech/docs/api.json
- Репозиторий GitHub: https://github.com/a-rakitin/laravel-helpdesk
- Файлы Postman: [`postman/`](postman/)
- Локальная интерактивная документация: http://localhost/docs/api
- Локальный OpenAPI JSON: http://localhost/docs/api.json

## Что показывает проект

- Аутентификация через Laravel Sanctum
- Роли `admin`, `agent` и `customer`
- Авторизация через Laravel policies для тикетов, комментариев и уведомлений
- Рабочий процесс тикетов: создание, список, просмотр, назначение, обновление статуса, комментарии
- Эндпоинты уведомлений для helpdesk-событий
- Хранение данных в PostgreSQL с migrations, factories и seeders
- Конфигурация cache, queue и sessions, готовая к Redis
- Docker-окружение с PHP 8.4, PostgreSQL 18, Redis и Nginx
- Базовое ограничение частоты запросов для auth и защищенных endpoints
- Интерактивная документация и OpenAPI JSON через Scramble
- PHPUnit feature-тесты, Laravel Pint и GitHub Actions CI

## Быстрый демо-сценарий

### Живая документация

1. Откройте https://helpdesk.rakitin.tech/docs/api.
2. Посмотрите публичные auth-эндпоинты:
   - `POST /api/auth/register`
   - `POST /api/auth/login`
3. Зарегистрируйтесь или войдите, чтобы получить Sanctum-токен.
4. Используйте `Authorization: Bearer <token>` для защищенных endpoints тикетов, комментариев, уведомлений и logout.
5. Проверьте сгенерированный OpenAPI-контракт: https://helpdesk.rakitin.tech/docs/api.json.

### Локальная проверка через Postman

Для полного API-сценария запустите проект локально. Включенная Postman-коллекция создает новых пользователей `customer`, выполняет вход под seeded-пользователями `agent` и `admin`, создает тикеты, проверяет ошибки валидации, границы авторизации, ответы not-found, комментарии, уведомления и подтверждает, что logout делает текущий токен недействительным.

Файлы Postman:

- [`postman/helpdesk-api.local.collection.json`](postman/helpdesk-api.local.collection.json)
- [`postman/helpdesk-api.local.environment.json`](postman/helpdesk-api.local.environment.json)

Локальные демо-пользователи, созданные seeders, используют пароль `password`:

- `qa-admin@example.com`
- `qa-agent@example.com`

В окружении Postman используются:

- `base_url`: `http://localhost`
- `admin`: `qa-admin@example.com` / `password`
- `agent`: `qa-agent@example.com` / `password`

Автоматическая локальная smoke-проверка:

```bash
npm install
npm run api:smoke:local
```

Команда запускает существующую Postman-коллекцию с локальным окружением против `http://localhost`.

## Стек

- Laravel 12
- PHP 8.4 в Docker и CI, Composer-совместимость задана как PHP 8.2+
- PostgreSQL 18
- Redis 7
- Nginx
- Laravel Sanctum
- Scramble для OpenAPI-документации
- PHPUnit feature-тесты
- Laravel Pint
- GitHub Actions

## Локальный запуск

Требования:

- Docker + Docker Compose
- Postman для проверок через коллекцию

```bash
git clone https://github.com/a-rakitin/laravel-helpdesk.git
cd laravel-helpdesk

cp .env.example .env
docker compose up -d --build

docker exec -it helpdesk-app composer install --no-interaction
docker exec -it helpdesk-app php artisan key:generate
docker exec -it helpdesk-app php artisan migrate --seed
docker exec -it helpdesk-app php artisan optimize:clear
docker compose restart worker
```

Для стабильных локальных API и Postman-проверок оставьте эти значения в `.env`:

```env
APP_ENV=local
QUEUE_CONNECTION=sync
```

Docker-сервис PostgreSQL читает `DB_DATABASE`, `DB_USERNAME` и `DB_PASSWORD`
из `.env`. Пароль из примера подходит только для локальной разработки; для
production используйте сильное уникальное значение до создания или пересоздания
контейнера базы данных.

Локальный API открывается по адресу http://localhost.

## API-документация

Scramble генерирует API-документацию из Laravel-приложения.

Эндпоинты документации:

- Локальная интерактивная документация: `http://localhost/docs/api`
- Локальный OpenAPI JSON: `http://localhost/docs/api.json`
- Интерактивная документация в production: `https://helpdesk.rakitin.tech/docs/api`
- Production OpenAPI JSON: `https://helpdesk.rakitin.tech/docs/api.json`

Модель аутентификации:

- Публичные endpoints: `POST /api/auth/register`, `POST /api/auth/login`
- Защищенные endpoints: tickets, comments, notifications и logout
- Защищенные requests требуют действительный Sanctum Bearer token
- Для auth endpoints лимиты строже, чем для authenticated API endpoints

Пример заголовка:

```http
Authorization: Bearer YOUR_TOKEN
```

## Команды проверки

Метаданные Composer:

```bash
composer validate --strict
```

Аудит зависимостей:

```bash
composer audit
npm audit --omit=dev
```

Конфигурация Docker Compose:

```bash
docker compose config
```

Стиль PHP-кода:

```bash
docker exec -it helpdesk-app ./vendor/bin/pint --test
```

Backend-тесты:

```bash
docker exec -it helpdesk-app php artisan test
```

Локальная API smoke-проверка через существующую Postman-коллекцию:

```bash
npm install
npm run api:smoke:local
```

Frontend assets нужно пересобирать только при изменениях в `resources/` или конфигурации Vite:

```bash
npm run build
```

## Деплой

Production-деплой описан в [`DEPLOY.md`](DEPLOY.md).

Рекомендуемый production-деплой:

```bash
./deploy.sh
```
