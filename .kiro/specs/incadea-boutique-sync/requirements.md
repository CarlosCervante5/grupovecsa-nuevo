# Requerimientos: Sincronización Incadea → Boutique

## Requerimiento 1: Consumo de API de Incadea

### Descripción
El sistema debe consumir el endpoint externo de Incadea para obtener el inventario completo de refacciones.

### Criterios de Aceptación
- 1.1 El sistema realiza un GET a la URL configurada en `INCADEA_API_URL` del archivo `.env`
- 1.2 El sistema parsea correctamente la respuesta JSON con estructura `{ data: { spare_parts: [...] } }`
- 1.3 Si la API no responde en 30 segundos, se lanza una excepción y el log queda con status `failed`
- 1.4 Si la API retorna un status HTTP diferente a 200, se registra el error y se aborta el proceso
- 1.5 La URL de la API nunca se hardcodea en el código fuente

---

## Requerimiento 2: Mapeo de Categorías Incadea → Boutique

### Descripción
Cada refacción de Incadea tiene una categoría que debe mapearse a una categoría existente en la Boutique.

### Criterios de Aceptación
- 2.1 "Tires" y "Complete wheels/Rim" se mapean a "Llantas y Rines"
- 2.2 "Car accesories", "Workshop Equipment", "Original Part" y "Exchange part" se mapean a "Accesorios"
- 2.3 "Life Style Accesories" se mapea a "Life Style"
- 2.4 "Operating/Auxiliary material" se mapea a "Clean & Care"
- 2.5 Si una categoría de Incadea no tiene mapeo definido, la refacción se cuenta como `skipped` y no se crea producto
- 2.6 El mapeo de categorías se resuelve buscando la categoría Boutique por nombre en la base de datos

---

## Requerimiento 3: Transformación de Datos y Upsert de Productos

### Descripción
Las refacciones de Incadea se transforman al formato de `BoutiqueProduct` y se insertan o actualizan usando `no_part` como SKU único.

### Criterios de Aceptación
- 3.1 El campo `no_part` de Incadea se usa como `sku` del producto Boutique
- 3.2 El campo `description` de Incadea se usa como `name` del producto
- 3.3 El campo `unit_price` de Incadea se usa como `price` del producto
- 3.4 El campo `exists_parts` de Incadea se usa como `stock` del producto
- 3.5 El campo `description` del producto se construye como: `"Marca: {brand} | Ubicación: {location_code} | Caja: {box_code}"`
- 3.6 El campo `active` se establece en `true` si `exists_parts > 0`, `false` en caso contrario
- 3.7 Si el SKU no existe en `boutique_products`, se crea un nuevo producto
- 3.8 Si el SKU ya existe y hay cambios en precio, stock, categoría o nombre, se actualiza el producto
- 3.9 Si el SKU ya existe y no hay cambios, la parte se cuenta como `skipped`
- 3.10 No se crean productos duplicados por SKU en ningún caso

---

## Requerimiento 4: Filtrado por Marca y Categoría

### Descripción
El sistema permite filtrar qué refacciones se sincronizan basándose en marcas y categorías excluidas.

### Criterios de Aceptación
- 4.1 Se pueden configurar marcas excluidas (ej: "OTRAS") que no se sincronizan
- 4.2 Se pueden configurar categorías excluidas (ej: "Unknown category") que no se sincronizan
- 4.3 Los filtros se envían como parámetros al disparar la sincronización
- 4.4 Los filtros aplicados se registran en el log de sincronización
- 4.5 Si no se envían filtros, se sincronizan todas las refacciones (que tengan mapeo de categoría)

---

## Requerimiento 5: Registro de Logs de Sincronización

### Descripción
Cada ejecución de sincronización genera un registro detallado con estadísticas del proceso.

### Criterios de Aceptación
- 5.1 Se crea una tabla `incadea_sync_logs` con campos: uuid, user_id, status, total_fetched, total_created, total_updated, total_skipped, total_errors, filters_applied, error_details, started_at, finished_at
- 5.2 Al iniciar la sincronización, se crea un registro con status `running`
- 5.3 Al finalizar exitosamente, el status cambia a `completed` con las estadísticas finales
- 5.4 Si falla el proceso, el status cambia a `failed` con el detalle del error
- 5.5 La suma `total_created + total_updated + total_skipped + total_errors` es igual al total de partes filtradas procesadas
- 5.6 Cada ejecución produce exactamente un registro de log

---

## Requerimiento 6: Endpoints API del Backend

### Descripción
Se exponen endpoints REST protegidos para disparar la sincronización y consultar el historial.

### Criterios de Aceptación
- 6.1 `POST /api/boutique/admin/incadea/sync` dispara el proceso de sincronización
- 6.2 `POST /api/boutique/admin/incadea/logs` retorna el historial de sincronizaciones paginado
- 6.3 `POST /api/boutique/admin/incadea/config` retorna la configuración actual de filtros
- 6.4 `POST /api/boutique/admin/incadea/update_config` actualiza la configuración de filtros
- 6.5 Todos los endpoints requieren autenticación (`auth:sanctum`)
- 6.6 Todos los endpoints requieren rol `developer` o `administrator`
- 6.7 Las respuestas usan el formato estándar de `ApiResponseHelper`

---

## Requerimiento 7: Configuración Persistente de Filtros

### Descripción
La configuración de marcas y categorías excluidas se almacena de forma persistente para reutilizarse entre sincronizaciones.

### Criterios de Aceptación
- 7.1 La configuración se almacena en la tabla `system_settings` con clave `incadea_sync_config`
- 7.2 La configuración incluye: `excluded_brands`, `excluded_categories`, `sync_inactive_when_zero_stock`, `default_category_slug`
- 7.3 La configuración se puede leer y actualizar desde los endpoints de la API
- 7.4 Si no existe configuración, se usan valores por defecto: excluir "OTRAS" y "Unknown category"

---

## Requerimiento 8: Panel Administrativo en Angular

### Descripción
Se agrega una sección en el panel de administración para gestionar la sincronización con Incadea.

### Criterios de Aceptación
- 8.1 Se agrega una vista accesible desde el panel developer en la ruta `/admin/developer/incadea-sync`
- 8.2 La vista muestra un botón para disparar la sincronización manualmente
- 8.3 Durante la sincronización, se muestra un indicador de carga (spinner/progress)
- 8.4 Al completar, se muestra un resumen con: total obtenidos, creados, actualizados, omitidos, errores
- 8.5 Se muestra una tabla con el historial de sincronizaciones anteriores (fecha, status, estadísticas)
- 8.6 Se permite configurar las marcas y categorías excluidas desde la interfaz
- 8.7 Solo usuarios con rol `developer` o `administrator` pueden acceder a esta vista

---

## Requerimiento 9: Manejo de Errores Resiliente

### Descripción
El proceso de sincronización maneja errores individuales sin detener la ejecución completa.

### Criterios de Aceptación
- 9.1 Si falla la creación/actualización de una refacción individual, el error se registra y el proceso continúa
- 9.2 Los errores individuales se almacenan en `error_details` del log con el `no_part` y el mensaje de error
- 9.3 Si falla la conexión a la API de Incadea, el proceso se aborta y el log queda como `failed`
- 9.4 El frontend muestra los errores de forma clara al usuario

---

## Requerimiento 10: Migración de Base de Datos

### Descripción
Se crea la migración necesaria para la tabla de logs y se actualiza el deploy script.

### Criterios de Aceptación
- 10.1 Se crea una migración para la tabla `incadea_sync_logs`
- 10.2 La migración sigue el patrón existente con `DB_TABLE_PREFIX` y verificación `hasTable`
- 10.3 Se agrega el seeder de configuración inicial al `deploy.sh` si es necesario
