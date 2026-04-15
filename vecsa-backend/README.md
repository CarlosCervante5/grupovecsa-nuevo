# vecsa-backend
Vecsa backend 2.0

## Monorepo / Railway sandbox

El historial canónico de desarrollo puede vivir en el monorepo Grupo VECSA. Los pushes de rutina a Railway usan la rama **`sandboxRailwayGrupoVecsaBackend`** (`git subtree`). Detalle: `ACCESOS.md` y `tools/push-sandbox-subtrees.sh` en el monorepo.

### Migraciones en deploy (Railway)

En la raíz del backend hay **`railway.toml`**: `releaseCommand` ejecuta `php artisan migrate --force --no-interaction` en cada deploy, antes de sustituir el tráfico al nuevo despliegue. Si en Railway usas otro proceso web (Octane, `php-fpm`, etc.), ajusta solo `startCommand` en ese archivo o en el dashboard y deja el `releaseCommand`.
