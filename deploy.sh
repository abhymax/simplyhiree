#!/bin/bash
set -euo pipefail

APP_DIR="/home/simplyhiree/public_html"
BRANCH="main"
PHP_BIN="php"
COMPOSER_BIN="composer"

cd "$APP_DIR"

echo "==> Starting deploy: $(date)"
echo "==> App dir: $APP_DIR"

CURRENT_COMMIT="$(git rev-parse HEAD)"
echo "==> Current commit: $CURRENT_COMMIT"

echo "==> Enabling maintenance mode..."
$PHP_BIN artisan down --render="errors::503" --retry=60 || true

cleanup() {
  echo "==> Disabling maintenance mode..."
  $PHP_BIN artisan up || true
}
trap cleanup EXIT

echo "==> Fetching latest code..."
git fetch origin "$BRANCH"

NEW_COMMIT="$(git rev-parse "origin/$BRANCH")"
echo "==> Target commit: $NEW_COMMIT"

echo "==> Resetting working tree to origin/$BRANCH..."
git reset --hard "origin/$BRANCH"

echo "==> Installing dependencies..."
COMPOSER_ALLOW_SUPERUSER=1 $COMPOSER_BIN install \
  --no-dev \
  --optimize-autoloader \
  --no-interaction \
  --prefer-dist

echo "==> Clearing and rebuilding caches..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "==> Health check..."
HTTP_CODE="$(curl -k -s -o /dev/null -w "%{http_code}" https://www.simplyhiree.com/ || true)"
if [ "$HTTP_CODE" != "200" ] && [ "$HTTP_CODE" != "302" ]; then
  echo "!! Health check failed with HTTP $HTTP_CODE"
  echo "!! Rolling back to previous commit: $CURRENT_COMMIT"

  git reset --hard "$CURRENT_COMMIT"

  COMPOSER_ALLOW_SUPERUSER=1 $COMPOSER_BIN install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

  $PHP_BIN artisan optimize:clear
  $PHP_BIN artisan config:cache
  $PHP_BIN artisan route:cache
  $PHP_BIN artisan view:cache

  exit 1
fi

echo "==> Deploy successful. HTTP $HTTP_CODE"