# Production Deploy

Server `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://helpdesk.rakitin.tech
QUEUE_CONNECTION=redis
```

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

## Deploy steps

The script runs these steps automatically. For a manual deployment, run the commands below.

1. Pull the latest code.

    ```bash
    cd /var/www/laravel-helpdesk
    git pull --ff-only
    ```

2. Build the PHP containers with fresh base images, then start containers with the production Compose files.

    ```bash
    docker compose -f docker-compose.yml -f docker-compose.prod.yml build --pull app worker
    docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
    ```

    `--pull` refreshes the base images for both `app` and `worker`, including PHP patch releases within the 8.4 branch. If the build fails, stop here and resolve the error before starting containers.

3. Install production PHP dependencies.

    ```bash
    docker exec helpdesk-app composer install --no-dev --optimize-autoloader --no-interaction
    ```

4. Run database migrations.

    ```bash
    docker exec helpdesk-app php artisan migrate --force
    ```

5. Set ownership of Laravel's writable directories to `www-data`.

    ```bash
    docker exec helpdesk-app chown -R www-data:www-data storage bootstrap/cache
    ```

6. Clear old Laravel caches.

    ```bash
    docker exec helpdesk-app php artisan optimize:clear
    ```

7. Rebuild the configuration and route caches.

    ```bash
    docker exec helpdesk-app php artisan config:cache
    docker exec helpdesk-app php artisan route:cache
    ```

8. Restart queue workers.

    ```bash
    docker exec helpdesk-app php artisan queue:restart
    ```

9. Recreate Nginx to refresh its connection to the app.

    ```bash
    docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --force-recreate nginx
    ```

10. Check the application and API documentation.

    ```bash
    APP_URL=https://helpdesk.rakitin.tech

    curl -fsS -o /dev/null "$APP_URL/"
    curl -fsS -o /dev/null "$APP_URL/docs/api"
    curl -fsS -o /dev/null "$APP_URL/docs/api.json"
    ```

    Successful requests produce no output; errors are printed.

## Verify the PHP version

After deployment, manually check the PHP version in both containers:

```bash
docker exec helpdesk-app php -v
docker exec helpdesk-worker php -v
```

The critical PHP security fixes released on 21 October 2026 require PHP 8.4.27 or later within the 8.4 branch. See the [official PHP security advisory](https://www.php.net/archive/2026.php#2026-10-21-5).
