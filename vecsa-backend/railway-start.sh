#!/bin/bash
set -e

# Mismo criterio que deploy.sh: variables de Railway disponibles al arrancar el contenedor
php artisan config:clear 2>/dev/null || true
php artisan migrate --force --no-interaction

exec php artisan serve --host=0.0.0.0 --port="${PORT:?}"
