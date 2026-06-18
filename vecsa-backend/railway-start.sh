#!/bin/bash
set -e

# Migraciones: deploy.sh vía preDeployCommand en railway.toml (no aquí; evita carreras al escalar).
php artisan config:clear 2>/dev/null || true

# Cola database: debe correr en el MISMO contenedor que recibe las subidas (temp_images/).
# Reinicia tras --max-time para no quedar sin worker después de ~1 h.
run_queue_worker_loop() {
  while true; do
    php artisan queue:work database --sleep=3 --tries=5 --timeout=300 --max-jobs=500 --max-time=3500 || true
    sleep 2
  done
}

# Servicio dedicado solo cola (opcional; desactivado en sandbox si el web procesa jobs).
if [[ "${RAILWAY_SERVICE_NAME:-}" == *worker* ]]; then
  run_queue_worker_loop
fi

# Web (sandbox-vecsa-backend): API + worker en background en el mismo contenedor.
run_queue_worker_loop &
exec php artisan serve --host=0.0.0.0 --port="${PORT:?}"
