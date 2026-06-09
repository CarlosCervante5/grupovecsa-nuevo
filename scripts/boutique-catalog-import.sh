#!/usr/bin/env bash
# Importa catálogo boutique en producción (reemplaza datos demo).
# Ejecutar en Railway Shell del backend PRODUCCIÓN.
# Uso: bash scripts/boutique-catalog-import.sh [ruta/al/dump.sql]
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

DUMP_FILE="${1:-storage/app/boutique-catalog-export/latest.sql}"
MAP_FILE="${MAP_FILE:-storage/app/boutique-catalog-export/boutique-dealership-map.json}"
CONFIRM="${CONFIRM:-}"

if [[ ! -f "$DUMP_FILE" ]]; then
  echo "❌ No existe el dump: $DUMP_FILE"
  exit 1
fi

PREFIX="${DB_TABLE_PREFIX:-}"
DB_HOST="${DB_HOST:-${MYSQLHOST:-}}"
DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
DB_USER="${DB_USERNAME:-${MYSQLUSER:-}}"
DB_PASS="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
DB_NAME="${DB_DATABASE:-${MYSQLDATABASE:-}}"

if [[ -z "$DB_HOST" || -z "$DB_USER" || -z "$DB_NAME" ]]; then
  echo "❌ Faltan variables DB_HOST / DB_USERNAME / DB_DATABASE."
  exit 1
fi

TABLES=(
  "${PREFIX}boutique_variant_attribute_values"
  "${PREFIX}boutique_product_variants"
  "${PREFIX}boutique_product_attribute_product"
  "${PREFIX}boutique_product_images"
  "${PREFIX}boutique_products"
  "${PREFIX}boutique_product_attribute_values"
  "${PREFIX}boutique_product_attributes"
  "${PREFIX}boutique_banners"
  "${PREFIX}boutique_categories"
)

echo "⚠️  Esto BORRARÁ el catálogo boutique actual en ${DB_NAME} y lo reemplazará."
echo "   Dump: $DUMP_FILE"
if [[ "$CONFIRM" != "yes" ]]; then
  echo "   Para continuar: CONFIRM=yes bash scripts/boutique-catalog-import.sh \"$DUMP_FILE\""
  exit 1
fi

echo "📋 Auditoría del dump (URLs WordPress en el SQL)..."
WP_COUNT="$(grep -Eo 'https?://[^\"'\'' ]*vecsaboutique\.com[^\"'\'' ]*' "$DUMP_FILE" | wc -l | tr -d ' ')"
if [[ "$WP_COUNT" != "0" ]]; then
  echo "❌ El dump contiene ${WP_COUNT} referencia(s) a vecsaboutique.com."
  echo "   Migra en sandbox antes de exportar, o revisa el archivo."
  exit 1
fi
echo "   OK: sin URLs WordPress detectadas en el dump."

MYSQL=(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER")
if [[ -n "$DB_PASS" ]]; then
  MYSQL+=(-p"$DB_PASS")
fi
MYSQL+=("$DB_NAME")

echo "🧹 Vaciando tablas boutique (orden seguro)..."
{
  echo "SET FOREIGN_KEY_CHECKS=0;"
  for table in "${TABLES[@]}"; do
    echo "DELETE FROM \`${table}\`;"
  done
  echo "SET FOREIGN_KEY_CHECKS=1;"
} | "${MYSQL[@]}"

echo "📥 Importando dump..."
"${MYSQL[@]}" < "$DUMP_FILE"

echo "🔧 Fixup dealership_id..."
FIXUP_ARGS=(boutique:catalog-import-fixup --remap-dealerships)
if [[ -f "$MAP_FILE" ]]; then
  FIXUP_ARGS+=(--map="$MAP_FILE")
else
  echo "⚠️  Sin mapa de sucursales; se pondrán NULL los dealership_id inválidos."
  FIXUP_ARGS=(boutique:catalog-import-fixup --null-dealerships)
fi
php artisan "${FIXUP_ARGS[@]}"

echo "📋 Auditoría post-import..."
php artisan boutique:catalog-audit --show-samples=3 --require-cdn-only

echo "✅ Import completado."
