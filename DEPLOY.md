# Production Deploy

This project is deployed manually on a VPS with Docker Compose.

## Server path

```bash
/var/www/laravel-helpdesk
```

## Main deploy method

The recommended deploy flow is the `deploy.sh` script.

The script can be started directly from the project directory:

```bash
cd /var/www/laravel-helpdesk
./deploy.sh
```

A global symlink is also configured on the server, so the deploy can be started from any directory with a shorter command:

```bash
hdp
```

## What the script does

The script performs the following steps:

1. pulls the latest code from GitHub with `git pull --ff-only`
2. rebuilds and starts containers with the production Compose files
3. installs production Composer dependencies inside the app container
4. runs database migrations
5. clears old Laravel caches
6. rebuilds config and route cache
7. restarts queue workers
8. recreates nginx to refresh the upstream connection
9. runs health checks for the main site and documentation endpoints

## Manual deploy fallback

If needed, the same deploy can be executed manually:

```bash
cd /var/www/laravel-helpdesk
git pull --ff-only
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
docker exec helpdesk-app composer install --no-dev --optimize-autoloader --no-interaction
docker exec helpdesk-app php artisan migrate --force
docker exec helpdesk-app php artisan optimize:clear
docker exec helpdesk-app php artisan config:cache
docker exec helpdesk-app php artisan route:cache
docker exec helpdesk-app php artisan queue:restart
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --force-recreate nginx
```

## Health checks

After deploy, verify the main endpoints:

```bash
APP_URL=https://your-domain.example

curl -I "$APP_URL"
curl -I "$APP_URL/docs/api.json"
curl -I "$APP_URL/api-docs.html"
```

Expected result:

- the main site responds successfully
- OpenAPI JSON responds successfully
- the production documentation page opens successfully

## Deploy smoke checklist

Set `APP_URL` to the deployed site. Use the first three checks for every deploy.
Use either login or register to get a token, then verify at least one protected
endpoint with that token.

```bash
APP_URL=https://your-domain.example
```

- Root endpoint:

```bash
curl -fsS "$APP_URL/"
```

- Docs JSON:

```bash
curl -fsS "$APP_URL/docs/api.json"
```

- Docs UI:

```bash
curl -fsS "$APP_URL/api-docs.html"
```

- Auth login with a known account:

```bash
curl -fsS "$APP_URL/api/auth/login" \
  -H 'Content-Type: application/json' \
  -d '{"email":"qa-agent@example.com","password":"password"}'
```

- Or register a throwaway customer account:

```bash
curl -fsS "$APP_URL/api/auth/register" \
  -H 'Content-Type: application/json' \
  -d '{"name":"Smoke Test","email":"smoke-CHANGE-ME@example.com","password":"password123","password_confirmation":"password123"}'
```

- Protected endpoint with the returned bearer token:

```bash
curl -fsS "$APP_URL/api/auth/me" \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

Production auth endpoints have a stricter rate limit than authenticated API
endpoints, so avoid looping these smoke commands rapidly.

## Notes

- `git pull --ff-only` prevents accidental merge commits on the server
- `composer install` is required on the server after pulling changes that modify `composer.json` or `composer.lock`
- `queue:restart` is required so workers reload the new application code
- `nginx` is recreated at the end because otherwise it may keep a stale upstream connection to the PHP container after app container recreation
- the script includes automatic health-check retries
- production database credentials should live in the server `.env`; the Compose
  file only provides local development defaults
