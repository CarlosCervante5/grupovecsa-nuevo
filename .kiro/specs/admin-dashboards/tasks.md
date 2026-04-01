# Plan de Implementación: Dashboards Administrativos con Datos Reales por Rol

## Resumen

Implementar un endpoint backend unificado de métricas por rol, un servicio Angular compartido, migrar 8 paneles al patrón sidebar layout, y actualizar cada dashboard con stat cards + gráficas ECharts usando datos reales de la API. El panel gestor ya tiene sidebar — solo necesita actualización del dashboard.

## Tareas

- [x] 1. Backend: Crear AdminDashboardController y ruta de métricas
  - [x] 1.1 Crear `vecsa-backend/app/Http/Controllers/AdminDashboard/AdminDashboardController.php` con método `metrics(Request $request)`
    - Obtener rol con `$request->user()->getRoleNames()->first()`
    - Implementar switch por rol (administrator, marketing, staff, gestor, receptionist, valuator, appointment_manager, bodywork_paint_technician, spare_parts)
    - Cada caso retorna `stats` (conteos numéricos) y `charts` (arrays para gráficas) según el diseño
    - Usar consultas SQLite-compatible: `whereBetween('created_at', [$start, $end])` con Carbon para rangos de fecha
    - Retornar con `ApiResponseHelper::apiSuccess(200, $data)`
    - Rol no reconocido retorna `{ stats: {}, charts: {} }` con código 200
    - Envolver en try/catch, error retorna `ApiResponseHelper::apiError(...)` con código `DASHBOARD_METRICS_ERROR`
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 1.10, 1.11, 1.12, 1.13_
  - [x] 1.2 Registrar ruta `POST /api/admin-dashboard/metrics` en `vecsa-backend/routes/api.php`
    - Agregar `use App\Http\Controllers\AdminDashboard\AdminDashboardController;` al inicio
    - Crear grupo `Route::prefix('admin-dashboard')->middleware(['bandwidth_usage', 'auth:sanctum'])->group(...)` con la ruta POST `/metrics`
    - _Requisitos: 1.1, 1.11_

- [x] 2. Frontend: Crear AdminDashboardService
  - [x] 2.1 Crear `vecsa-frontend/src/app/admin/shared/services/admin-dashboard.service.ts`
    - `@Injectable({ providedIn: 'root' })`
    - Método `getMetrics(): Observable<any>` que hace POST a `${environment.baseUrl}/api/admin-dashboard/metrics`
    - Headers: `Authorization: Bearer ${localStorage.getItem('user_token')}`, `Content-Type: application/json`, `X-Requested-With: XMLHttpRequest`
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 3. Checkpoint — Verificar backend y servicio
  - Asegurar que el endpoint retorna métricas correctas para cada rol. Preguntar al usuario si hay dudas.

- [x] 4. Panel Administrator: Layout sidebar + Dashboard con métricas
  - [x] 4.1 Crear `AdminLayoutComponent` en `vecsa-frontend/src/app/admin/administrador/pages/layout/`
    - Copiar patrón de GestorLayoutComponent (HTML sidebar + CSS + TS)
    - navItems: Dashboard (`/admin/administrator`), Usuarios (`/admin/administrator/users`), Permisos (`/admin/administrator/permissions`), Boutique (`/admin/administrator/boutique`)
    - dynamicItems: Tienda si `access store_management`, Benchmark si `access benchmark`
    - User info desde localStorage, logout method
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.13, 4.14_
  - [x] 4.2 Actualizar `administrador-routing.module.ts` para usar `AdminLayoutComponent` como wrapper con children
    - _Requisitos: 4.1_
  - [x] 4.3 Declarar `AdminLayoutComponent` en `administrador.module.ts`
    - _Requisitos: 4.1_
  - [x] 4.4 Actualizar dashboard de administrator (`vecsa-frontend/src/app/admin/administrador/pages/dashboard/`)
    - Inyectar `AdminDashboardService`, definir array `stats` con 8 stat cards (vehículos, productos, pedidos, usuarios, clientes, sucursales, valuaciones, citas)
    - Llamar `getMetrics()` en `ngOnInit`, poblar stats y charts
    - Gráfica de barras: pedidos por mes (6 meses), gráfica de pie: pedidos por estatus
    - Tarjetas de navegación rápida a módulos del panel
    - Manejar estados loading/error
    - _Requisitos: 1.2, 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.10, 3.11, 6.1, 6.2, 6.3_

- [x] 5. Panel Marketing: Layout sidebar + Dashboard con métricas
  - [x] 5.1 Crear `MarketingLayoutComponent` en `vecsa-frontend/src/app/admin/marketing/pages/layout/`
    - navItems: Dashboard (`/admin/marketing`), Vehículos (`/admin/marketing/vehicles`), Home Slides (`/admin/marketing/home-slides`), Testimonios (`/admin/marketing/home-testimonials`)
    - dynamicItems, user info, logout — mismo patrón
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.6, 4.13, 4.14_
  - [x] 5.2 Actualizar `marketing-routing.module.ts` para usar `MarketingLayoutComponent` como wrapper con children
    - _Requisitos: 4.1_
  - [x] 5.3 Declarar `MarketingLayoutComponent` en `marketing.module.ts`
    - _Requisitos: 4.1_
  - [x] 5.4 Actualizar dashboard de marketing
    - 4 stat cards: campañas activas, promociones, eventos, vehículos publicados
    - Gráfica de pie: vehículos por marca
    - Tarjetas de navegación rápida
    - _Requisitos: 1.3, 2.1, 2.4, 2.6, 3.1, 3.3, 6.1, 6.2, 6.3_

- [x] 6. Panel Staff: Layout sidebar + Dashboard con métricas
  - [x] 6.1 Crear `StaffLayoutComponent` en `vecsa-frontend/src/app/admin/staff/pages/layout/`
    - navItems: Dashboard (`/admin/staff`), Registro de KM (`/admin/staff/riders`), Registro de Compras (`/admin/staff/sales`)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.7, 4.13, 4.14_
  - [x] 6.2 Actualizar `staff-routing.module.ts` para usar `StaffLayoutComponent` como wrapper
    - _Requisitos: 4.1_
  - [x] 6.3 Declarar `StaffLayoutComponent` en `staff.module.ts`
    - _Requisitos: 4.1_
  - [x] 6.4 Actualizar dashboard de staff
    - 3 stat cards: clientes, recompensas activas, total puntos
    - Sin gráficas (según diseño)
    - Tarjetas de navegación rápida
    - _Requisitos: 1.4, 2.1, 2.4, 2.7, 6.1, 6.2, 6.3_

- [x] 7. Panel Receptionist: Layout sidebar + Dashboard con métricas
  - [x] 7.1 Crear `ReceptionistLayoutComponent` en `vecsa-frontend/src/app/admin/receptionist/pages/layout/`
    - navItems: Dashboard (`/admin/receptionist`), Formulario de Recepción (`/admin/receptionist/reception-form`)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.8, 4.13, 4.14_
  - [x] 7.2 Actualizar `receptionist-routing.module.ts` para usar `ReceptionistLayoutComponent`
    - _Requisitos: 4.1_
  - [x] 7.3 Declarar `ReceptionistLayoutComponent` en módulo receptionist
    - _Requisitos: 4.1_
  - [x] 7.4 Actualizar dashboard de receptionist
    - 3 stat cards: citas hoy, citas semana, total citas
    - Gráfica de pie: citas por tipo
    - Tarjetas de navegación rápida
    - _Requisitos: 1.6, 2.1, 2.4, 2.9, 3.1, 3.5, 6.1, 6.2, 6.3_

- [x] 8. Checkpoint — Verificar paneles administrator, marketing, staff, receptionist
  - Asegurar que los 4 paneles muestran sidebar, stat cards y gráficas correctamente. Preguntar al usuario si hay dudas.

- [x] 9. Panel Valuator: Layout sidebar + Dashboard con métricas
  - [x] 9.1 Crear `ValuatorLayoutComponent` en `vecsa-frontend/src/app/admin/valuator/pages/layout/`
    - navItems: Dashboard (`/admin/valuator`), Citas de Valuación (`/admin/valuator/valuation-appointments`)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.9, 4.13, 4.14_
  - [x] 9.2 Actualizar `valuator-routing.module.ts` para usar `ValuatorLayoutComponent`
    - _Requisitos: 4.1_
  - [x] 9.3 Declarar `ValuatorLayoutComponent` en módulo valuator
    - _Requisitos: 4.1_
  - [x] 9.4 Actualizar dashboard de valuator
    - 4 stat cards: pendientes, en progreso, completadas, total
    - Gráfica de pie: valuaciones por estatus
    - Tarjetas de navegación rápida
    - _Requisitos: 1.7, 2.1, 2.4, 2.10, 3.1, 3.6, 6.1, 6.2, 6.3_

- [x] 10. Panel Appointment Manager: Layout sidebar + Dashboard con métricas
  - [x] 10.1 Crear `AppointmentManagerLayoutComponent` en `vecsa-frontend/src/app/admin/appointment-manager/pages/layout/`
    - navItems: Dashboard (`/admin/appointment_manager`), Asignar Citas (`/admin/appointment_manager/assign-appointments`)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.10, 4.13, 4.14_
  - [x] 10.2 Actualizar routing de appointment-manager para usar `AppointmentManagerLayoutComponent`
    - _Requisitos: 4.1_
  - [x] 10.3 Declarar `AppointmentManagerLayoutComponent` en módulo appointment-manager
    - _Requisitos: 4.1_
  - [x] 10.4 Actualizar dashboard de appointment_manager
    - 4 stat cards: citas hoy, citas semana, pendientes asignar, total
    - Gráfica de barras: citas por mes (6 meses)
    - Tarjetas de navegación rápida
    - _Requisitos: 1.8, 2.1, 2.4, 2.11, 3.1, 3.7, 6.1, 6.2, 6.3_

- [x] 11. Panel Bodywork Paint Technician: Layout sidebar + Dashboard con métricas
  - [x] 11.1 Crear `BodyworkLayoutComponent` en `vecsa-frontend/src/app/admin/bodywork-paint-technician/pages/layout/`
    - navItems: Dashboard (`/admin/bodywork_paint_technician`), Hojalatería y Pintura (`/admin/bodywork_paint_technician/repairs`)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.11, 4.13, 4.14_
  - [x] 11.2 Actualizar routing de bodywork-paint-technician para usar `BodyworkLayoutComponent`
    - _Requisitos: 4.1_
  - [x] 11.3 Declarar `BodyworkLayoutComponent` en módulo bodywork-paint-technician
    - _Requisitos: 4.1_
  - [x] 11.4 Actualizar dashboard de bodywork_paint_technician
    - 3 stat cards: pendientes, en progreso, completadas
    - Gráfica de pie: reparaciones por estatus
    - Tarjetas de navegación rápida
    - _Requisitos: 1.9, 2.1, 2.4, 2.12, 3.1, 3.8, 6.1, 6.2, 6.3_

- [x] 12. Panel Spare Parts: Layout sidebar + Dashboard con métricas
  - [x] 12.1 Crear `SparePartsLayoutComponent` en `vecsa-frontend/src/app/admin/spare-parts/pages/layout/`
    - navItems: Dashboard (`/admin/spare_parts`), Refacciones (`/admin/spare_parts/parts`)
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.12, 4.13, 4.14_
  - [x] 12.2 Actualizar routing de spare-parts para usar `SparePartsLayoutComponent`
    - _Requisitos: 4.1_
  - [x] 12.3 Declarar `SparePartsLayoutComponent` en módulo spare-parts
    - _Requisitos: 4.1_
  - [x] 12.4 Actualizar dashboard de spare_parts
    - 3 stat cards: pendientes, en revisión, completadas
    - Gráfica de pie: refacciones por estatus
    - Tarjetas de navegación rápida
    - _Requisitos: 1.10, 2.1, 2.4, 2.13, 3.1, 3.9, 6.1, 6.2, 6.3_

- [x] 13. Checkpoint — Verificar paneles valuator, appointment_manager, bodywork, spare_parts
  - Asegurar que los 4 paneles muestran sidebar, stat cards y gráficas correctamente. Preguntar al usuario si hay dudas.

- [x] 14. Panel Gestor: Actualizar dashboard con métricas reales
  - [x] 14.1 Actualizar dashboard de gestor (`vecsa-frontend/src/app/admin/gestor/pages/dashboard/`)
    - Inyectar `AdminDashboardService`, definir stat cards: promociones, eventos, recompensas
    - Llamar `getMetrics()` en `ngOnInit`, poblar stats
    - Gráfica de barras: eventos por mes (6 meses)
    - Tarjetas de navegación rápida a módulos del panel
    - Manejar estados loading/error
    - No necesita layout nuevo — gestor ya tiene `GestorLayoutComponent` con sidebar
    - _Requisitos: 1.5, 2.1, 2.4, 2.8, 3.1, 3.4, 6.1, 6.2, 6.3_

- [x] 15. Checkpoint final — Verificar todos los paneles
  - Asegurar que los 9 paneles (administrator, marketing, staff, receptionist, valuator, appointment_manager, bodywork_paint_technician, spare_parts, gestor) muestran dashboards con datos reales. Preguntar al usuario si hay dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- El panel gestor solo necesita actualización del dashboard (ya tiene sidebar)
- Todos los layout components siguen el patrón exacto de `GestorLayoutComponent`
- El CSS del sidebar se reutiliza de `gestor-layout.component.css`
