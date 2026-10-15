# Laravel Helpdesk API

[English](README.md) | [Русский](README.ru.md)

[![CI](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/a-rakitin/laravel-helpdesk/actions/workflows/ci.yml)

Helpdesk API на Laravel 12 для работы с заявками, комментариями, уведомлениями и ролями пользователей. Окружение проекта запускается в контейнерах Docker через Docker Compose.

- **Приложение:** <https://helpdesk.rakitin.tech>
- **Документация API:** <https://helpdesk.rakitin.tech/docs/api>

## Возможности API

- Регистрация, вход и выход через Laravel Sanctum.
- Доступ по ролям `customer`, `agent` и `admin`.
- Создание и просмотр заявок, поиск, фильтрация, сортировка и пагинация.
- Назначение исполнителя и изменение статуса заявки.
- Добавление и просмотр комментариев к заявкам.
- Уведомления о новых комментариях по email и просмотр списка уведомлений.
- Просмотр пользователей и управление ролями администратором.
- Ограничение частоты запросов к API.

## Стек

- Laravel 12
- PHP 8.4
- PostgreSQL 18
- Redis
- Nginx
- Docker и Docker Compose

## Локальный запуск

Требования:

- Git
- Docker с Docker Compose

Рекомендуемые варианты установки Docker:

- Windows: Docker Desktop с настроенным WSL 2.
- macOS: Docker Desktop.
- Linux: Docker Engine и Docker Compose.

Клонировать репозиторий и запустить приложение:

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

Настройки `.env.example` (`APP_ENV=local`, `QUEUE_CONNECTION=sync`) включают демо-сидеры и обработку уведомлений во время HTTP-запроса.

После запуска:

- **Приложение:** <http://localhost>
- **Почта (Mailpit):** <http://localhost:8025>

Демо-пользователи с общим паролем `password`:

- `admin`: `qa-admin@example.com`
- `agent`: `qa-agent@example.com`

Для фоновой обработки уведомлений установить `QUEUE_CONNECTION=redis` в `.env` и выполнить:

```shell
docker exec -it helpdesk-app php artisan optimize:clear
docker compose restart worker
```

## Работа с API

Scramble генерирует OpenAPI JSON, Scalar отображает интерактивную документацию. Локальные адреса:

- **Интерактивная документация:** <http://localhost/docs/api>
- **OpenAPI JSON:** <http://localhost/docs/api.json>

1. Открыть документацию и выполнить `POST /api/auth/register` или `POST /api/auth/login`.
2. Скопировать токен из ответа.
3. Передавать токен в заголовке защищённых запросов:

```http
Authorization: Bearer YOUR_TOKEN
```

Для проверки API через Postman импортировать [коллекцию](postman/helpdesk-api.local.collection.json) и [окружение](postman/helpdesk-api.local.environment.json), затем выбрать это окружение. Оно настроено на `http://localhost` и использует демо-пользователей, указанных выше.

## Тесты и проверки

Тесты PHPUnit проверяют сценарии API, права доступа, валидацию, формат ответов и OpenAPI-документацию.

Запустить тесты PHPUnit:

```shell
docker exec helpdesk-app composer run test
```

Проверить стиль PHP-кода через Laravel Pint:

```shell
docker exec helpdesk-app ./vendor/bin/pint --test
```

Проверить PHP-зависимости на известные уязвимости:

```shell
docker exec helpdesk-app composer audit
```

GitHub Actions запускает тесты, проверку стиля PHP-кода и аудит PHP-зависимостей при pull request и push в `main`.

## Деплой

Развёртывание в production описано в [DEPLOY.md](DEPLOY.md).
