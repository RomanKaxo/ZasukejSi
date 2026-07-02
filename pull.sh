#!/bin/bash

set -e

echo "📥 Pulling latest changes..."
git pull

echo ""
echo "🔨 Building frontend assets..."
npm run build


# Clear all caches
echo ""
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "✅ Done!"
