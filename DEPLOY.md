# Production Deploy

This project is deployed manually on a VPS with Docker Compose.

## Server path

```bash
/var/www/laravel-helpdesk
```

## Main deploy method

The recommended deploy flow is the `deploy.sh` script from the project root.

### Run deploy

```bash
cd /var/www/laravel-helpdesk
./deploy.sh
```

## What the script does

The script performs the following steps:

1. pulls the latest code from GitHub
2. rebuilds and starts containers with production Compose files
3. installs production Composer dependencies inside the app container
4. runs database migrations
5. clears old Laravel caches
6. rebuilds config and route cache
7. restarts queue workers
8. recreates nginx to refresh the upstream connection
9. runs quick health checks

## Manual deploy fallback

If needed, the same deploy can be executed manually:

```bash
cd /var/www/laravel-helpdesk
git pull
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
curl -I https://helpdesk.rakitin.tech
curl -I https://helpdesk.rakitin.tech/docs/api.json
curl -I https://helpdesk.rakitin.tech/api-docs.html
```

Expected result:
- the main site responds successfully
- OpenAPI JSON responds successfully
- production documentation page opens successfully

## Notes

- `composer install` is required on the server after pulling changes that modify `composer.json` or `composer.lock`
- `queue:restart` is required so workers reload the new application code
- `nginx` is recreated at the end because otherwise it may keep a stale upstream connection to the PHP container after app container recreation
