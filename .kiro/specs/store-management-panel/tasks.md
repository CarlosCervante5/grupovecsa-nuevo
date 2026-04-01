# Plan de Implementación: Panel de Gestión de Tienda

## Resumen

Implementación del módulo administrativo de gestión de tienda para VECSA. Se inicia con los controllers backend (reutilizando modelos existentes), luego las rutas API, después el módulo frontend Angular con guard, servicio y componentes, y finalmente integración de todo el flujo.

## Tareas

- [x] 1. Controllers Backend — Gestión de Pedidos y Métricas
  - [x] 1.1 Crear `StoreManagementController` con métodos: dashboard, searchOrders, orderDetail, updateOrderStatus, generateLabel, searchShipments
    - `dashboard()`: calcular total_orders, revenue (solo estados pagados), pending_orders, total_customers, total_products, orders_by_month (últimos 6 meses con whereBetween), orders_by_status
    - `searchOrders()`: paginación 15/página, filtros por status, date_from, date_to, search (LIKE en order_number y shipping_name), ordenado por created_at DESC
    - `orderDetail()`: retornar pedido con relaciones user, orderItems, payment, shipment por UUID
    - `updateOrderStatus()`: validar transiciones de estado (pendiente→pagado/cancelado, pagado→en_preparacion/cancelado, etc.), restaurar inventario en cancelación, sincronizar estado de shipment
    - `generateLabel()`: invocar EnviacomService existente, actualizar shipment con tracking
    - `searchShipments()`: paginación con filtros por status y carrier
    - Usar ApiResponseHelper para todas las respuestas
    - _Requisitos: 2.1, 2.2, 2.4, 3.1, 3.2, 3.5, 3.6, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 11.2, 11.4_

  - [x] 1.2 Crear `StoreCustomerController` con métodos: search, detail, customerOrders
    - `search()`: lista paginada de clientes con búsqueda por nombre/email
    - `detail()`: retornar datos del cliente con total_points (suma earned_points no redimidos), orders, rewards, coupons. Retornar 404 con CUSTOMER_NOT_FOUND si no existe
    - `customerOrders()`: historial de pedidos del cliente
    - _Requisitos: 6.1, 6.2, 6.3, 6.4, 11.2, 11.4_

  - [x] 1.3 Crear `StorePointsController` con métodos: search, adjust, customerBalance, searchRedemptions, updateRedemptionStatus
    - `search()`: lista de clientes con balance de puntos
    - `adjust()`: validar points > 0, reason >= 5 chars, type in [add, subtract]. Si subtract y balance < points → 400 INSUFFICIENT_BALANCE. Crear registro con name=ajuste_manual, detail=reason. Retornar nuevo balance
    - `customerBalance()`: historial de movimientos de puntos
    - `searchRedemptions()`: lista paginada con filtro por estado
    - `updateRedemptionStatus()`: aprobar/rechazar redención
    - _Requisitos: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 9.1, 9.2, 9.3, 11.2_

  - [x] 1.4 Crear `StoreCouponController` con métodos: search, store, update, delete
    - `store()`: validar código único (case-insensitive), alfanumérico con guiones, 4-20 chars. Si percentage → amount <= 100. Si max y min → max >= min. Almacenar código en UPPER, usage_count=0. Retornar 422 DUPLICATE_COUPON_CODE si existe
    - `update()`: actualizar campos del cupón
    - `delete()`: soft delete del cupón
    - _Requisitos: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7, 8.8, 11.2_

- [x] 2. Rutas API Backend
  - [x] 2.1 Registrar rutas en `routes/api.php` bajo prefijo `store-management/` con middleware `bandwidth_usage`, `auth:sanctum`, `permission:access store_management`
    - Rutas POST para: metrics, orders/search, orders/detail, orders/update_status, orders/generate_label, shipments/search, customers/search, customers/detail, customers/orders, points/search, points/adjust, points/customer_balance, coupons/search, coupons/store, coupons/update, coupons/delete, redemptions/search, redemptions/update_status
    - _Requisitos: 1.5, 11.1, 11.5_

- [ ] 3. Checkpoint — Verificar backend
  - Verificar que todos los endpoints responden correctamente con Postman o similar. Asegurar compatibilidad SQLite en métricas. Preguntar al usuario si hay dudas.

- [x] 4. Módulo Frontend Angular — Estructura base
  - [x] 4.1 Crear `StoreManagementGuard` en `vecsa-frontend/src/app/admin/store/guards/store-management.guard.ts`
    - Verificar permiso `access store_management` en localStorage (array permissions)
    - Permitir acceso automático si rol es `developer`
    - Redirigir a `/` si no tiene permiso
    - Implementar canActivate y canLoad
    - Seguir patrón exacto de BenchmarkGuard
    - _Requisitos: 1.1, 1.2, 1.3_

  - [x] 4.2 Crear `StoreService` en `vecsa-frontend/src/app/admin/store/services/store.service.ts`
    - Leer token de localStorage con clave `user_token`
    - Métodos para todos los endpoints: getDashboardMetrics, searchOrders, getOrderDetail, updateOrderStatus, generateShippingLabel, searchShipments, searchCustomers, getCustomerDetail, getCustomerOrders, getCustomerPoints, searchPoints, adjustPoints, searchCoupons, createCoupon, updateCoupon, deleteCoupon, searchRedemptions, updateRedemptionStatus
    - _Requisitos: 1.4, 11.3_

  - [x] 4.3 Crear `StoreModule` y `StoreRoutingModule` con rutas hijas
    - Rutas: dashboard, orders, orders/:uuid, shipping, customers, customers/:uuid, points, coupons, redemptions
    - Registrar en `admin-routing.module.ts` como lazy-loaded en path `store` con StoreManagementGuard
    - _Requisitos: 10.1, 10.2_

  - [x] 4.4 Crear interfaces TypeScript para el módulo
    - OrderSearchParams, ShipmentSearchParams, CustomerSearchParams, PointsSearchParams, PointAdjustment, CouponSearchParams, CouponCreate, RedemptionSearchParams, DashboardStat
    - _Requisitos: 10.4_

- [x] 5. Componentes Frontend — Dashboard y Pedidos
  - [x] 5.1 Crear `DashboardComponent` con cards de métricas y gráficas ECharts
    - Cards: total pedidos, ingresos, pedidos pendientes, clientes, productos activos
    - Gráficas: pedidos por mes (bar chart), distribución por estado (pie chart)
    - Navegación rápida a cada sección
    - Estilo: fondo blanco, color primario #1c69d4, cards con border-radius 16px, Material Icons
    - _Requisitos: 2.1, 2.3, 2.5, 10.3_

  - [x] 5.2 Crear `OrdersComponent` con tabla paginada y filtros
    - Columnas: # Pedido, Cliente, Estado, Total, Fecha
    - Filtros: estado (select), rango de fechas (date pickers), búsqueda (input)
    - Badges de color por estado
    - Click en fila navega a detalle
    - _Requisitos: 3.1, 3.2, 3.4_

  - [x] 5.3 Crear `OrderDetailComponent` con vista completa del pedido
    - Info del pedido: número, estado, fechas, totales
    - Lista de productos con cantidades y precios
    - Info de pago: método, referencia, estado
    - Info de envío: carrier, tracking, estado
    - Acciones: cambiar estado (dropdown), generar guía de envío (botón)
    - _Requisitos: 3.3, 4.1, 5.3_

- [x] 6. Componentes Frontend — Envíos y Clientes
  - [x] 6.1 Crear `ShippingComponent` con tabla de envíos
    - Columnas: # Pedido, Cliente, Carrier, Tracking, Estado, Fecha estimada
    - Filtros por estado y carrier
    - Link a tracking externo
    - _Requisitos: 5.1, 5.2, 5.4_

  - [x] 6.2 Crear `CustomersComponent` con lista paginada
    - Búsqueda por nombre/email
    - Click en fila navega a detalle
    - _Requisitos: 6.1_

  - [x] 6.3 Crear `CustomerDetailComponent` con perfil completo
    - Datos personales, historial de pedidos, balance de puntos, cupones asignados
    - _Requisitos: 6.2_

- [x] 7. Componentes Frontend — Puntos, Cupones y Redenciones
  - [x] 7.1 Crear `PointsComponent` con gestión de puntos
    - Lista de clientes con balance
    - Historial de movimientos por cliente
    - Modal/formulario para ajuste manual: tipo (add/subtract), cantidad, motivo
    - Validación frontend: puntos > 0, motivo >= 5 caracteres
    - _Requisitos: 7.1, 7.2, 7.3_

  - [x] 7.2 Crear `CouponsComponent` con CRUD de cupones
    - Tabla de cupones: código, monto, tipo, usos, estado
    - Modal de creación/edición con validaciones
    - Botón eliminar con confirmación
    - _Requisitos: 8.1, 8.2, 8.7_

  - [x] 7.3 Crear `RedemptionsComponent` con gestión de redenciones
    - Lista de solicitudes con filtro por estado
    - Acciones: aprobar/rechazar
    - _Requisitos: 9.1, 9.2, 9.3_

- [ ] 8. Checkpoint — Verificar frontend completo
  - Verificar navegación completa del módulo, que todos los componentes renderizan sin errores, que las llamadas API funcionan correctamente. Preguntar al usuario si hay dudas.

- [ ]* 9. Tests de propiedades
  - [ ]* 9.1 Escribir test de propiedad para el guard de acceso
    - **Propiedad 1: El guard permite acceso si y solo si el usuario tiene permiso o es developer**
    - **Valida: Requisitos 1.1, 1.2, 1.3**

  - [ ]* 9.2 Escribir test de propiedad para cálculo de revenue
    - **Propiedad 2: Revenue solo incluye pedidos con estados pagados**
    - **Valida: Requisito 2.2**

  - [ ]* 9.3 Escribir test de propiedad para máquina de estados de pedidos
    - **Propiedad 3: Máquina de estados de pedidos válida**
    - **Valida: Requisitos 4.1, 4.2, 4.4**

  - [ ]* 9.4 Escribir test de propiedad para cancelación restaura inventario
    - **Propiedad 4: Cancelación de pedido restaura inventario**
    - **Valida: Requisito 4.3**

  - [ ]* 9.5 Escribir test de propiedad para ajuste de puntos
    - **Propiedad 5: Balance de puntos nunca es negativo después de un ajuste**
    - **Valida: Requisitos 7.5, 7.7**

  - [ ]* 9.6 Escribir test de propiedad para registro de ajuste manual
    - **Propiedad 6: Ajuste manual de puntos crea registro correcto**
    - **Valida: Requisitos 7.4, 7.6**

  - [ ]* 9.7 Escribir test de propiedad para validación de cupones
    - **Propiedad 7: Validación de cupones y almacenamiento en mayúsculas**
    - **Valida: Requisitos 8.3, 8.4, 8.5, 8.6**

  - [ ]* 9.8 Escribir test de propiedad para paginación
    - **Propiedad 8: Paginación retorna resultados consistentes**
    - **Valida: Requisitos 3.5, 5.2, 9.2**

## Notas

- Las tareas marcadas con `*` son opcionales (tests de propiedades)
- Cada tarea referencia requisitos específicos para trazabilidad
- Los controllers backend reutilizan modelos existentes sin crear migraciones nuevas
- El guard sigue el patrón exacto de BenchmarkGuard
- El módulo se registra como lazy-loaded en admin-routing.module.ts
- Compatibilidad SQLite: usar whereBetween en lugar de funciones SQL específicas de MySQL
