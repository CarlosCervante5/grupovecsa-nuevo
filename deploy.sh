#!/bin/bash
set -e

echo "🚀 Running deploy script..."

# Run migrations
echo "📦 Running migrations..."
php artisan migrate --force 2>/dev/null || echo "⚠️ Some migrations skipped (tables may already exist)"

# Run seeders (safe to re-run - they use firstOrCreate patterns)
echo "🌱 Running seeders..."
php artisan db:seed --class=DeveloperUserSeeder --force 2>/dev/null || echo "⚠️ DeveloperUserSeeder skipped"
php artisan db:seed --class=HomeContentSeeder --force 2>/dev/null || echo "⚠️ HomeContentSeeder skipped"
php artisan db:seed --class=BoutiqueBannerSeeder --force 2>/dev/null || echo "⚠️ BoutiqueBannerSeeder skipped"
php artisan db:seed --class=BoutiqueCategoriesSeeder --force 2>/dev/null || echo "⚠️ BoutiqueCategoriesSeeder skipped"

# Cache config and routes for production
echo "⚡ Caching config and routes..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true

echo "✅ Deploy complete!"
