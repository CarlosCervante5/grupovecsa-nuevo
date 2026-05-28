#!/bin/bash
set -e

# Migraciones: deploy.sh vía preDeployCommand en railway.toml (no aquí; evita carreras al escalar).
php artisan config:clear 2>/dev/null || true

# Cola database: uploads de imágenes, correos, etc. (sin esto los jobs quedan bloqueando nuevas cargas)
php artisan queue:work database --sleep=3 --tries=5 --timeout=300 --max-jobs=500 --max-time=3600 &
WORKER_PID=$!
trap 'kill "$WORKER_PID" 2>/dev/null || true' EXIT

exec php artisan serve --host=0.0.0.0 --port="${PORT:?}"
