#!/usr/bin/env bash
# Exporta catálogo boutique (sin pedidos/carritos) desde la BD actual (sandbox).
# Ejecutar en Railway Shell del backend SANDBOX: bash scripts/boutique-catalog-export.sh
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# shellcheck source=lib/load-db-env.sh
source "${ROOT_DIR}/scripts/lib/load-db-env.sh"
boutique_load_db_env || true

OUTPUT_DIR="${OUTPUT_DIR:-storage/app/boutique-catalog-export}"
# 1 = solo CloudFront/Cloudinary; 0 = permite mix con WordPress (vecsaboutique.com)
REQUIRE_CDN_ONLY="${REQUIRE_CDN_ONLY:-1}"
TIMESTAMP="$(date -u +%Y%m%d_%H%M%S)"
DUMP_FILE="${OUTPUT_DIR}/boutique_catalog_${TIMESTAMP}.sql"
MAP_FILE="${OUTPUT_DIR}/boutique-dealership-map.json"
LATEST_LINK="${OUTPUT_DIR}/latest.sql"

PREFIX="${DB_TABLE_PREFIX:-}"
DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
DB_USER="${DB_USERNAME:-${MYSQLUSER:-}}"
DB_PASS="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
DB_NAME="${DB_DATABASE:-${MYSQLDATABASE:-}}"

if [[ -z "$DB_HOST" || -z "$DB_USER" || -z "$DB_NAME" ]]; then
  echo "❌ No se pudieron leer credenciales MySQL (shell ni Laravel)."
  echo "   Prueba: php artisan tinker --execute=\"echo config('database.connections.mysql.host');\""
  exit 1
fi

TABLES=(
  "${PREFIX}boutique_categories"
  "${PREFIX}boutique_banners"
  "${PREFIX}boutique_product_attributes"
  "${PREFIX}boutique_product_attribute_values"
  "${PREFIX}boutique_products"
  "${PREFIX}boutique_product_attribute_product"
  "${PREFIX}boutique_product_variants"
  "${PREFIX}boutique_variant_attribute_values"
  "${PREFIX}boutique_product_images"
)

mkdir -p "$OUTPUT_DIR"

echo "📋 Auditoría previa al export..."
AUDIT_ARGS=(boutique:catalog-audit --show-samples=3)
if [[ "$REQUIRE_CDN_ONLY" == "1" ]]; then
  AUDIT_ARGS+=(--require-cdn-only)
fi
php artisan "${AUDIT_ARGS[@]}"

echo "🗺️  Exportando mapa de sucursales (sandbox)..."
php artisan boutique:catalog-export-dealership-map --output="$MAP_FILE"

echo "📦 Generando dump SQL..."
{
  echo "-- Boutique catalog export ${TIMESTAMP}"
  echo "-- Source DB: ${DB_NAME}"
  echo "SET NAMES utf8mb4;"
  echo "SET FOREIGN_KEY_CHECKS=0;"
  mysqldump \
    --no-create-info \
    --complete-insert \
    --single-transaction \
    --skip-triggers \
    --set-gtid-purged=OFF \
    -h "$DB_HOST" \
    -P "$DB_PORT" \
    -u "$DB_USER" \
    ${DB_PASS:+-p"$DB_PASS"} \
    "$DB_NAME" \
    "${TABLES[@]}"
  echo "SET FOREIGN_KEY_CHECKS=1;"
} > "$DUMP_FILE"

ln -sf "$(basename "$DUMP_FILE")" "$LATEST_LINK"

BYTES="$(wc -c < "$DUMP_FILE" | tr -d ' ')"
echo "✅ Export listo:"
echo "   SQL:  $DUMP_FILE (${BYTES} bytes)"
echo "   Mapa: $MAP_FILE"
echo ""
echo "Siguiente paso: copia ambos archivos a producción y ejecuta:"
echo "   bash scripts/boutique-catalog-import.sh storage/app/boutique-catalog-export/$(basename "$DUMP_FILE")"
