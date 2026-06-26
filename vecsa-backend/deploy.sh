#!/bin/bash
set -e

echo "🚀 Running deploy script..."

# Leer DB_TABLE_PREFIX y demás vars de Railway antes de migrate (evita config cache del build sin prefijo)
echo "⚡ Limpiando caché de config antes de migrate..."
php artisan config:clear 2>/dev/null || true

# Run migrations (falla el script si la migración falla; no ocultar errores)
echo "📦 Running migrations..."
php artisan migrate --force --no-interaction

# Seeders idempotentes: firstOrCreate por SKU/nombre; boutique demo se omite si hay catálogo real importado
echo "🌱 Running seeders..."
php artisan db:seed --class=DeveloperUserSeeder --force 2>/dev/null || echo "⚠️ DeveloperUserSeeder skipped"
php artisan db:seed --class=HomeContentSeeder --force 2>/dev/null || echo "⚠️ HomeContentSeeder skipped"
php artisan db:seed --class=BoutiqueBannerSeeder --force 2>/dev/null || echo "⚠️ BoutiqueBannerSeeder skipped"
php artisan db:seed --class=BoutiqueCategoriesSeeder --force 2>/dev/null || echo "⚠️ BoutiqueCategoriesSeeder skipped"
php artisan db:seed --class=SyncBoutiqueCategoryHierarchySeeder --force 2>/dev/null || echo "⚠️ SyncBoutiqueCategoryHierarchySeeder skipped"
php artisan db:seed --class=VehicleDataSeeder --force 2>/dev/null || echo "⚠️ VehicleDataSeeder skipped"
php artisan db:seed --class=VehicleInventorySeeder --force 2>/dev/null || echo "⚠️ VehicleInventorySeeder skipped"
php artisan db:seed --class=BoutiqueProductsSeeder --force 2>/dev/null || echo "⚠️ BoutiqueProductsSeeder skipped"
php artisan db:seed --class=LegalesSeeder --force 2>/dev/null || echo "⚠️ LegalesSeeder skipped"

# En Railway las variables (AWS_*, etc.) se inyectan en runtime; config:cache las
# congela en el release y deja bucket/credenciales vacíos si se añadieron después.
echo "⚡ Limpiando caché de config y cacheando rutas..."
php artisan permission:cache-reset 2>/dev/null || echo "⚠️ Permission cache reset skipped"
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan route:cache 2>/dev/null || true

echo "✅ Deploy complete!"
