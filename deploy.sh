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

echo "==> Fetching latest code..."
git fetch origin "$BRANCH"

NEW_COMMIT="$(git rev-parse "origin/$BRANCH")"
echo "==> Target commit: $NEW_COMMIT"

echo "==> Checking client digest before deploy..."
if ! git grep -q -F -e 'clients:approved-applications-digest' "$NEW_COMMIT" -- app/Console/Commands/SendClientApprovedDigest.php ||
   ! git grep -q -F -e '{--exclude-backlog' "$NEW_COMMIT" -- app/Console/Commands/SendClientApprovedDigest.php ||
   ! git cat-file -e "$NEW_COMMIT:resources/views/support/client_approved_digest.blade.php" ||
   ! test -r storage/app/client-approved-digest-baseline.json ||
   ! crontab -l | grep -F 'clients:approved-applications-digest --exclude-backlog' | grep -E '^0[[:space:]]+8[[:space:]]+\*[[:space:]]+\*[[:space:]]+\*[[:space:]]' >/dev/null; then
  echo "!! Client digest command, template, backlog cutoff or 08:00 cron entry is missing. Deployment stopped."
  exit 1
fi

echo "==> Enabling maintenance mode..."
$PHP_BIN artisan down --render="errors::503" --retry=60 || true

cleanup() {
  echo "==> Disabling maintenance mode..."
  $PHP_BIN artisan up || true
}
trap cleanup EXIT

echo "==> Resetting working tree to origin/$BRANCH..."
git reset --hard "origin/$BRANCH"

echo "==> Installing dependencies (if composer available)..."
if command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
  COMPOSER_ALLOW_SUPERUSER=1 $COMPOSER_BIN install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist
else
  echo "!! WARNING: composer not found on PATH — skipping dependency install."
  echo "!! If composer.lock changed in this deploy, dependencies will be out of date."
fi

echo "==> Clearing and rebuilding caches..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

digest_healthy=1
if ! $PHP_BIN artisan clients:approved-applications-digest --exclude-backlog --dry >/dev/null 2>&1; then
  echo "!! Client digest dry run failed."
  digest_healthy=0
fi

echo "==> Disabling maintenance mode before health check..."
$PHP_BIN artisan up || true

echo "==> Health check..."
HTTP_CODE="$(curl -k -s -o /dev/null -w "%{http_code}" https://www.simplyhiree.com/up || true)"
if [ "$digest_healthy" = 0 ] || { [ "$HTTP_CODE" != "200" ] && [ "$HTTP_CODE" != "302" ]; }; then
  echo "!! Deployment verification failed (health HTTP $HTTP_CODE, digest check $digest_healthy)"
  echo "!! Rolling back to previous commit: $CURRENT_COMMIT"

  $PHP_BIN artisan down --render="errors::503" --retry=60 || true

  git reset --hard "$CURRENT_COMMIT"

  if command -v "$COMPOSER_BIN" >/dev/null 2>&1; then
    COMPOSER_ALLOW_SUPERUSER=1 $COMPOSER_BIN install \
      --no-dev \
      --optimize-autoloader \
      --no-interaction \
      --prefer-dist
  fi

  $PHP_BIN artisan optimize:clear
  $PHP_BIN artisan config:cache
  $PHP_BIN artisan route:cache
  $PHP_BIN artisan view:cache
  $PHP_BIN artisan event:cache
  $PHP_BIN artisan up || true

  exit 1
fi

echo "==> Deploy successful. HTTP $HTTP_CODE"
