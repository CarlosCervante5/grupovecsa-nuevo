# Tareas de Implementación: Sincronización Incadea → Boutique

## 1. Migración y Modelo de Base de Datos
*Requerimientos: 5, 10*

- [x] 1.1 Crear migración `create_incadea_sync_logs_table` con campos: id, uuid, user_id, status (enum: running/completed/failed), total_fetched, total_created, total_updated, total_skipped, total_errors, filters_applied (json), error_details (json), started_at, finished_at, timestamps. Seguir patrón existente con `DB_TABLE_PREFIX` y `hasTable`.
- [x] 1.2 Crear modelo `IncadeaSyncLog` en `app/Models/Boutique/` con fillable, casts, relación `user()`, método `findByUuid()`, y boot con UUID auto-generado. Seguir el patrón de los modelos Boutique existentes.
- [x] 1.3 Agregar variable `INCADEA_API_URL` al archivo `.env.example` con valor `http://52.21.121.207/api/incadea/get_spare_parts`.

## 2. Servicio de Mapeo de Categorías
*Requerimientos: 2*

- [x] 2.1 Crear clase `CategoryMapper` en `app/Services/Incadea/CategoryMapper.php` con método `resolve(string $incadeaCategory): ?int` que mapee categorías de Incadea a IDs de categorías Boutique según la tabla de mapeo definida en el diseño. Incluir método `getMappingTable(): array`.

## 3. Servicio de Sincronización
*Requerimientos: 1, 3, 4, 5, 9*

- [x] 3.1 Crear clase `IncadeaSyncService` en `app/Services/Incadea/IncadeaSyncService.php` con método `fetchSpareParts(): array` que consume la API de Incadea usando `Http::timeout(30)->get()` con la URL desde `config('services.incadea.api_url')`.
- [x] 3.2 Implementar método `filterParts(array $parts, array $filters): array` que excluye partes por marca y categoría según los filtros proporcionados.
- [x] 3.3 Implementar método `syncPart(array $part): string` que transforma una refacción al formato BoutiqueProduct y realiza upsert por SKU (`no_part`). Retorna 'created', 'updated' o 'skipped'.
- [x] 3.4 Implementar método `executeSyncProcess(array $filters): array` que orquesta el flujo completo: crear log → fetch → filter → sync cada parte → actualizar log con estadísticas. Manejar errores individuales sin detener el proceso.

## 4. Configuración del Servicio
*Requerimientos: 7*

- [x] 4.1 Agregar configuración de Incadea en `config/services.php` con clave `incadea.api_url` leyendo de `env('INCADEA_API_URL')`.
- [x] 4.2 Implementar lectura/escritura de configuración de filtros en `system_settings` con clave `incadea_sync_config`. Crear helper o usar el modelo Settings existente.

## 5. Controlador y Rutas API
*Requerimientos: 6*

- [x] 5.1 Crear `IncadeaSyncController` en `app/Http/Controllers/Boutique/` con métodos: `sync()`, `logs()`, `getConfig()`, `updateConfig()`.
- [x] 5.2 Registrar rutas en `routes/api.php` bajo el prefijo `boutique/admin/incadea` con middleware `bandwidth_usage`, `auth:sanctum`. Verificar rol developer|administrator en el controlador.

## 6. Panel Administrativo Angular — Servicio
*Requerimientos: 8*

- [x] 6.1 Crear servicio `IncadeaSyncService` en Angular (`services/incadea-sync.service.ts`) con métodos: `triggerSync(filters)`, `getLogs()`, `getConfig()`, `updateConfig(config)`.
- [x] 6.2 Crear interfaces TypeScript para `SyncResult`, `SyncLog`, `SyncConfig` en `interfaces/incadea-sync.interfaces.ts`.

## 7. Panel Administrativo Angular — Componente
*Requerimientos: 8*

- [x] 7.1 Crear componente `IncadeaSyncComponent` con vista que incluya: botón de sincronización, indicador de carga, resumen de resultados, y tabla de historial de logs.
- [x] 7.2 Agregar sección de configuración de filtros (marcas y categorías excluidas) con formulario editable.
- [x] 7.3 Registrar la ruta `/admin/developer/incadea-sync` en el módulo de routing del panel developer con el guard correspondiente.

## 8. Deploy y Migración
*Requerimientos: 10*

- [x] 8.1 Agregar la variable `INCADEA_API_URL` al `.env` de producción en Railway.
- [x] 8.2 Verificar que la migración se ejecuta correctamente con `php artisan migrate` (el deploy.sh existente ya ejecuta migraciones automáticamente).
