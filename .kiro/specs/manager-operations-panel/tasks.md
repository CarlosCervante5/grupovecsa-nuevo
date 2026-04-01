# Plan de Implementación: Panel de Operaciones del Gerente

## Resumen

Implementación del módulo administrativo para el rol gerente en VECSA. Se inicia con el backend (seeder de rol/permisos y método `gerenteMetrics()` en el controlador existente), luego el módulo frontend Angular completo (guard, servicio, layout con sidebar, dashboard con stat cards y gráficas ECharts), y finalmente la integración con la ruta lazy-loaded en `admin-routing.module.ts`.

## Tareas

- [x] 1. Backend — Rol, permisos y usuario de prueba
  - [x] 1.1 Actualizar `DeveloperUserSeeder` para crear el rol `gerente` con permisos de acceso
    - Agregar `gerente` al array `$roleNames`
    - Crear permisos: `access gerente`, `access gestor`, `access receptionist`, `access valuator`, `access appointment_manager`, `access staff`, `access bodywork_paint_technician`, `access spare_parts`, `access store_management`, `access benchmark`, `access marketing`
    - Asignar todos los permisos `access` al rol `gerente`, excluyendo `list users`, `create users`, `update users`, `delete users`
    - Asignar `access gerente` también a los roles `developer` y `administrator`
    - Agregar usuario de prueba: email `gerente@vecsa.com`, contraseña `TestUser%2024%%`, nickname `gerente_test`, nombre `Gerente`, apellido `Test`, rol `gerente`
    - Actualizar `ACCESOS.md` con el nuevo usuario y permisos
    - _Requisitos: 1.1, 1.2, 1.3, 1.4_

- [x] 2. Backend — Endpoint de métricas del gerente
  - [x] 2.1 Agregar caso `gerente` en el `match` de `AdminDashboardController::metrics()` y crear método privado `gerenteMetrics()`
    - Agregar `'gerente' => $this->gerenteMetrics()` en el `match` existente
    - Importar `BoutiqueOrderItem` si no está importado
    - Stats generales: `Vehicle::count()`, `BoutiqueProduct::where('active', true)->count()`, `BoutiqueOrder::count()`, `User::count()`, `Customer::count()`, `Dealership::count()`, `VehicleValuation::count()`, `CustomerAppointment::count()`
    - Stats boutique: `total_sales` sumando totales de pedidos con status en `['pagado', 'en_preparacion', 'enviado', 'entregado']`, `pending_orders` con status `pendiente`, `products` activos
    - Stats citas/valuaciones: `appointments_today` con `whereDate('scheduled_date', Carbon::today())`, `appointments_week` con `whereBetween('scheduled_date', [$weekStart, $weekEnd])`, `valuations_pending` con status `pending`, `valuations_in_progress` con status `in_progress`
    - Stats benchmark: contar competidores y escaneos del directorio de benchmark en Storage
    - Charts: `orders_by_month` (últimos 6 meses con `whereBetween`), `orders_by_status` (agrupado por status), `top_products` (top 5 por cantidad con `BoutiqueOrderItem`), `valuations_by_dealership` (agrupado por `dealership_id` con nombre de sucursal), `appointments_by_dealership` (agrupado por `dealership_id` con nombre), `appointments_by_month` (últimos 6 meses con `whereBetween`)
    - Usar `whereBetween` en todas las consultas por fecha para compatibilidad SQLite
    - Usar `ApiResponseHelper` para la respuesta
    - _Requisitos: 3.2, 4.3, 4.4, 5.2, 5.4, 6.3, 6.4, 7.2, 7.3, 8.4, 11.1, 11.2, 11.3, 11.4, 11.5_

- [x] 3. Checkpoint — Verificar backend
  - Asegurar que el seeder corre sin errores y crea el rol/usuario correctamente. Verificar que el endpoint retorna métricas para el rol gerente. Preguntar al usuario si hay dudas.

- [x] 4. Frontend — Guard y servicio
  - [x] 4.1 Crear `GerenteGuard` en `vecsa-frontend/src/app/admin/gerente/guards/gerente.guard.ts`
    - Seguir patrón exacto de `GestorGuard`
    - Invocar `validateRole('gerente')` en `canActivate` y `canLoad`
    - Redirigir a `/auth/iniciar-sesion` si falla
    - _Requisitos: 2.1, 2.2, 2.3_

  - [x] 4.2 Crear `GerenteDashboardService` en `vecsa-frontend/src/app/admin/gerente/services/gerente-dashboard.service.ts`
    - Seguir patrón exacto de `AdminDashboardService`
    - Leer token de `localStorage` con clave `user_token`
    - Método `getMetrics()` que hace POST a `/api/admin-dashboard/metrics`
    - Header `Authorization: Bearer {token}`
    - _Requisitos: 2.4_

- [x] 5. Frontend — Layout con sidebar
  - [x] 5.1 Crear `GerenteLayoutComponent` en `vecsa-frontend/src/app/admin/gerente/pages/layout/`
    - Archivos: `gerente-layout.component.ts`, `gerente-layout.component.html`, `gerente-layout.component.css`
    - Seguir patrón de `AdminLayoutComponent` con sidebar, `<router-outlet>`, secciones de paneles y herramientas
    - `navItems`: solo Dashboard con ruta `/admin/gerente`
    - `panelItems` filtrados por permisos: Gestor (`access gestor`), Recepción (`access receptionist`), Valuador (`access valuator`), Citas (`access appointment_manager`), Staff (`access staff`), Hojalatería (`access bodywork_paint_technician`), Refacciones (`access spare_parts`)
    - `dynamicItems` filtrados por permisos: Tienda (`access store_management`), Benchmark ADS (`access benchmark`), Marketing (`access marketing`)
    - Leer user, permissions, role, profile de localStorage con try/catch
    - Botón "Volver a mi panel" con ruta `/admin/{role}`
    - Botón cerrar sesión que limpia localStorage y redirige a `/auth/login`
    - CSS: copiar estilos de `gestor-layout.component.css` (mismo patrón de sidebar)
    - _Requisitos: 9.1, 9.2, 9.3, 9.4, 9.5, 10.3, 10.4_

- [x] 6. Frontend — Dashboard con métricas y gráficas
  - [x] 6.1 Crear `GerenteDashboardComponent` en `vecsa-frontend/src/app/admin/gerente/pages/dashboard/`
    - Archivos: `gerente-dashboard.component.ts`, `gerente-dashboard.component.html`, `gerente-dashboard.component.css`
    - Stat cards generales: Vehículos, Productos activos, Pedidos, Usuarios, Clientes, Sucursales, Valuaciones, Citas
    - Stat cards boutique: Total ventas (formateado como moneda), Pedidos pendientes, Productos activos
    - Stat cards citas/valuaciones: Citas hoy, Citas semana, Valuaciones pendientes, Valuaciones en progreso
    - Stat cards benchmark: Competidores, Escaneos
    - Estado de carga (skeleton/spinner) mientras se cargan métricas
    - Mensaje de error con botón "Reintentar" si la API falla
    - Accesos rápidos (quickLinks) a los paneles principales
    - Enlace directo al módulo Benchmark ADS
    - Usar `import * as echarts from 'echarts'` para gráficas
    - NO usar `.flat()` — usar `[].concat(...)` si se necesita aplanar arrays
    - _Requisitos: 3.1, 3.3, 3.4, 5.1, 7.1, 8.1, 8.2, 8.3_

  - [x] 6.2 Implementar gráficas ECharts en el dashboard
    - Gráfica de barras: pedidos por mes (últimos 6 meses)
    - Gráfica de dona: distribución de pedidos por estado
    - Gráfica de barras horizontales: top 5 productos más vendidos
    - Gráfica de barras: valuaciones por sucursal
    - Gráfica de barras: citas por sucursal
    - Gráfica de línea: citas por mes (últimos 6 meses)
    - Cada gráfica con `@ViewChild` y `ElementRef`, dispose en `ngOnDestroy`, resize en window resize
    - Mostrar "Sin datos" si el array de datos está vacío
    - _Requisitos: 4.1, 4.2, 5.3, 6.1, 6.2, 7.4, 10.6_

- [x] 7. Frontend — Módulo, routing e integración
  - [x] 7.1 Crear `GerenteModule` y `GerenteRoutingModule`
    - `gerente.module.ts`: declarar `GerenteDashboardComponent` y `GerenteLayoutComponent`, importar `CommonModule`, `GerenteRoutingModule`
    - `gerente-routing.module.ts`: ruta raíz con `GerenteLayoutComponent` y child `''` con `GerenteDashboardComponent`
    - Seguir patrón exacto de `GestorModule` y `GestorRoutingModule`
    - _Requisitos: 10.1, 10.2, 10.5_

  - [x] 7.2 Registrar ruta lazy-loaded en `admin-routing.module.ts`
    - Agregar ruta `gerente` con `loadChildren` apuntando a `GerenteModule`
    - Agregar `canActivate` y `canLoad` con `GerenteGuard`
    - Importar `GerenteGuard` en el archivo de rutas
    - Colocar antes del wildcard `**`
    - _Requisitos: 10.1_

- [x] 8. Checkpoint final — Verificar integración completa
  - Asegurar que la navegación a `/admin/gerente` funciona, el guard protege el acceso, el dashboard carga métricas y renderiza gráficas. Verificar que el sidebar muestra paneles filtrados por permisos. Preguntar al usuario si hay dudas.

- [ ]* 9. Tests de propiedades
  - [ ]* 9.1 Escribir test de propiedad para exclusión de permisos de usuario
    - **Propiedad 1: El rol gerente excluye permisos de gestión de usuarios**
    - **Valida: Requisitos 1.2, 1.4**

  - [ ]* 9.2 Escribir test de propiedad para el guard de acceso
    - **Propiedad 2: El guard deniega acceso a usuarios no autorizados**
    - **Valida: Requisito 2.2**

  - [ ]* 9.3 Escribir test de propiedad para header de autorización
    - **Propiedad 3: El servicio incluye el header de autorización correcto**
    - **Valida: Requisito 2.4**

  - [ ]* 9.4 Escribir test de propiedad para cálculo de ventas
    - **Propiedad 4: El total de ventas solo incluye pedidos con estados pagados**
    - **Valida: Requisito 5.2**

  - [ ]* 9.5 Escribir test de propiedad para top productos
    - **Propiedad 5: Los productos más vendidos están correctamente ordenados y limitados**
    - **Valida: Requisito 5.4**

  - [ ]* 9.6 Escribir test de propiedad para agrupación por sucursal
    - **Propiedad 6: La agrupación por sucursal incluye nombres y conteos correctos**
    - **Valida: Requisitos 6.3, 6.4**

  - [ ]* 9.7 Escribir test de propiedad para filtrado de citas por fecha
    - **Propiedad 7: El filtrado de citas por fecha es correcto**
    - **Valida: Requisitos 7.2, 7.3**

  - [ ]* 9.8 Escribir test de propiedad para filtrado del sidebar
    - **Propiedad 8: Los items del sidebar se filtran por permisos del usuario**
    - **Valida: Requisitos 9.2, 9.3**

  - [ ]* 9.9 Escribir test de propiedad para agregación mensual
    - **Propiedad 9: La agregación mensual es consistente y compatible con SQLite**
    - **Valida: Requisitos 4.3, 4.4, 11.4**

  - [ ]* 9.10 Escribir test de propiedad para conteo de benchmark
    - **Propiedad 10: El conteo de benchmark coincide con los archivos en storage**
    - **Valida: Requisito 8.4**

## Notas

- Las tareas marcadas con `*` son opcionales (tests de propiedades) y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Se reutilizan modelos existentes sin crear migraciones nuevas
- El guard sigue el patrón exacto de `GestorGuard`
- El layout sigue el patrón de `AdminLayoutComponent` con panelItems y dynamicItems
- Compatibilidad SQLite: usar `whereBetween` en lugar de funciones SQL como `YEAR()` o `MONTH()`
- NO usar `.flat()` — usar `[].concat(...)` para compatibilidad ES2017
- `provideHttpClient(withInterceptorsFromDi())` ya está configurado en `app.module.ts`
- Token se lee de `localStorage` con clave `user_token`
- ECharts se importa como `import * as echarts from 'echarts'`
