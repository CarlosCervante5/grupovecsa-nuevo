#!/bin/bash
set -e

echo "🚀 Running deploy script..."

# Run migrations (falla el script si la migración falla; no ocultar errores)
echo "📦 Running migrations..."
php artisan migrate --force --no-interaction

# Run seeders (safe to re-run - they use firstOrCreate patterns)
echo "🌱 Running seeders..."
php artisan db:seed --class=DeveloperUserSeeder --force 2>/dev/null || echo "⚠️ DeveloperUserSeeder skipped"
php artisan db:seed --class=HomeContentSeeder --force 2>/dev/null || echo "⚠️ HomeContentSeeder skipped"
php artisan db:seed --class=BoutiqueBannerSeeder --force 2>/dev/null || echo "⚠️ BoutiqueBannerSeeder skipped"
php artisan db:seed --class=BoutiqueCategoriesSeeder --force 2>/dev/null || echo "⚠️ BoutiqueCategoriesSeeder skipped"
php artisan db:seed --class=VehicleDataSeeder --force 2>/dev/null || echo "⚠️ VehicleDataSeeder skipped"
php artisan db:seed --class=VehicleInventorySeeder --force 2>/dev/null || echo "⚠️ VehicleInventorySeeder skipped"
php artisan db:seed --class=BoutiqueProductsSeeder --force 2>/dev/null || echo "⚠️ BoutiqueProductsSeeder skipped"

# Cache config and routes for production
echo "⚡ Caching config and routes..."
php artisan permission:cache-reset 2>/dev/null || echo "⚠️ Permission cache reset skipped"
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true

echo "✅ Deploy complete!"
