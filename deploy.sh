#!/usr/bin/env bash

set -Eeuo pipefail

PROJECT_DIR="$(cd "$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")" && pwd)"
cd "$PROJECT_DIR"

COMPOSE_FILES=(-f docker-compose.yml -f docker-compose.prod.yml)

log() {
  echo
  echo "==> $1"
}

wait_for_url() {
  local url="$1"
  local attempts="${2:-20}"
  local delay="${3:-3}"

  for ((i=1; i<=attempts; i++)); do
    if curl -fsS --max-time 10 -o /dev/null "$url"; then
      echo "OK: $url"
      return 0
    fi

    echo "Attempt $i/$attempts failed for $url, retrying in ${delay}s..."
    sleep "$delay"
  done

  echo "FAILED: $url"
  return 1
}

log "Pull latest changes"
git pull --ff-only

log "Build and start containers"
docker compose "${COMPOSE_FILES[@]}" up -d --build

log "Install production PHP dependencies"
docker compose "${COMPOSE_FILES[@]}" exec -T app composer install --no-dev --optimize-autoloader --no-interaction

log "Run database migrations"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan migrate --force

log "Fix Laravel writable directory permissions"
docker compose "${COMPOSE_FILES[@]}" exec -T app chown -R www-data:www-data storage bootstrap/cache

log "Clear old caches"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan optimize:clear

log "Build fresh Laravel caches"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan config:cache
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan route:cache

log "Restart queue workers"
docker compose "${COMPOSE_FILES[@]}" exec -T app php artisan queue:restart

log "Recreate nginx to refresh upstream connection"
docker compose "${COMPOSE_FILES[@]}" up -d --force-recreate nginx

log "Health checks"
wait_for_url "https://helpdesk.rakitin.tech"
wait_for_url "https://helpdesk.rakitin.tech/docs/api"
wait_for_url "https://helpdesk.rakitin.tech/docs/api.json"

log "Deploy finished successfully"
