#!/bin/bash

# ===========================================
# Deploy script - dev.stanektech.cz
# Laravel + PHP 8.3 + Node 22 + MySQL
# ===========================================
# Spustit na serveru: bash deploy.sh
# ===========================================

set -euo pipefail

DEPLOY_DIR="$(cd "$(dirname "$0")" && pwd)"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.3-fpm}"
APP_WENT_DOWN=0

finish() {
    if [ "$APP_WENT_DOWN" -eq 1 ]; then
        php artisan up >/dev/null 2>&1 || true
    fi
}

trap finish EXIT

require_command() {
    if ! command -v "$1" >/dev/null 2>&1; then
        echo "  CHYBA: Chybí příkaz '$1'"
        exit 1
    fi
}

echo "======================================"
echo " ZasukejSi - Deploy"
echo " $(date '+%Y-%m-%d %H:%M:%S')"
echo "======================================"

cd "$DEPLOY_DIR"

echo ""
echo "[1/11] Kontrola nástrojů..."
require_command php
require_command composer
require_command node
require_command npm
echo "  php/composer/node/npm OK"

echo ""
echo "[2/11] Kontrola PHP 8.3..."
PHP_VER=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "  PHP $PHP_VER"
if ! php -r "exit(version_compare(PHP_VERSION, '8.3.0', '>=') ? 0 : 1);"; then
    echo "  CHYBA: Vyžadováno PHP 8.3+, nalezeno PHP $PHP_VER"
    exit 1
fi
echo "  OK"

echo ""
echo "[3/11] Kontrola Node.js 22..."
NODE_VER=$(node -v 2>/dev/null || echo "nenalezeno")
NODE_MAJOR=$(node -p "process.versions.node.split('.')[0]" 2>/dev/null || echo "0")
echo "  Node $NODE_VER"
if [ "$NODE_MAJOR" -ne 22 ]; then
    echo "  CHYBA: Vyžadován Node.js 22.x, nalezeno $NODE_VER"
    exit 1
fi
echo "  OK"

echo ""
echo "[4/11] Nastavení produkčního .env..."
if [ ! -f ".env" ]; then
    if [ ! -f ".env.production" ]; then
        echo "  CHYBA: Chybí .env i .env.production"
        exit 1
    fi
    cp ".env.production" ".env"
    echo "  .env.production -> .env zkopírováno"
else
    echo "  .env již existuje, přeskakuji"
fi

echo ""
echo "[5/11] Maintenance mode..."
php artisan down --retry=60 >/dev/null 2>&1 || true
APP_WENT_DOWN=1
echo "  Aplikace je v maintenance mode"

echo ""
echo "[6/11] Composer install (production)..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
echo "  OK"

echo ""
echo "[7/11] Kontrola APP_KEY..."
CURRENT_KEY=$(grep '^APP_KEY=' ".env" | cut -d'=' -f2- || true)
if [ -z "$CURRENT_KEY" ]; then
    php artisan key:generate --force
    echo "  APP_KEY vygenerován"
else
    echo "  APP_KEY OK"
fi

echo ""
echo "[8/11] NPM install + build..."
if [ -f "package-lock.json" ]; then
    npm ci
else
    npm install
fi
npm run build
echo "  Frontend build OK"

echo ""
echo "[9/11] Migrace + storage..."
php artisan migrate --force
php artisan storage:link --force 2>/dev/null || true
echo "  Migrace a storage OK"

echo ""
echo "[10/11] Seed základních dat..."
php artisan db:seed --class=RoleSeeder --force 2>/dev/null || echo "  RoleSeeder přeskočen nebo neexistuje"

PAGE_COUNT=$(php artisan tinker --execute="echo \\App\\Models\\Page::count();" 2>/dev/null | tail -1 | tr -d '[:space:]' || echo "0")
if [ "$PAGE_COUNT" = "0" ] || [ -z "$PAGE_COUNT" ]; then
    php artisan db:seed --class=PageSeeder --force 2>/dev/null || echo "  PageSeeder přeskočen"
else
    echo "  Stránky již existují ($PAGE_COUNT), přeskakuji"
fi

CITY_COUNT=$(php artisan tinker --execute="echo \\App\\Models\\City::count();" 2>/dev/null | tail -1 | tr -d '[:space:]' || echo "0")
if [ "$CITY_COUNT" = "0" ] || [ -z "$CITY_COUNT" ]; then
    php artisan db:seed --class=CitySeeder --force 2>/dev/null || echo "  CitySeeder přeskočen"
else
    echo "  Města již existují ($CITY_COUNT), přeskakuji"
fi

php artisan db:seed --class=SubscriptionTypeSeeder --force 2>/dev/null || echo "  SubscriptionTypeSeeder přeskočen"

echo ""
echo "[11/11] Cache, oprávnění a restart služeb..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
php artisan filament:optimize 2>/dev/null || true
php artisan icons:cache 2>/dev/null || true
php artisan queue:restart 2>/dev/null || true

chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod -R 755 public 2>/dev/null || true

sudo systemctl restart "$PHP_FPM_SERVICE" 2>/dev/null || \
sudo service "$PHP_FPM_SERVICE" restart 2>/dev/null || \
echo "  PHP-FPM restart přeskočen (spusť ručně pokud třeba)"

php artisan up >/dev/null 2>&1 || true
APP_WENT_DOWN=0

echo ""
echo "======================================"
echo " Deploy dokončen!"
echo " URL: https://dev.stanektech.cz"
echo "======================================"
