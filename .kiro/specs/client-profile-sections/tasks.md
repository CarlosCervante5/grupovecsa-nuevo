# Plan de Implementación: Secciones del Perfil de Cliente

## Resumen

Agregar navegación por pestañas a la página de perfil del cliente (`/auth/mi-cuenta`) con cuatro secciones: Perfil (existente), Pedidos, Citas y Cotizaciones. Se crean interfaces, servicios y componentes Angular que consumen endpoints existentes del backend Laravel. No se requieren cambios en el backend.

## Tareas

- [x] 1. Crear interfaces TypeScript para pedidos, citas y cotizaciones
  - [x] 1.1 Crear `orders.interface.ts` en `account/interfaces/` con las interfaces `Order`, `OrderDetail`, `OrderItem`, `Payment`, `Shipment`, `OrdersResponse` y `OrderDetailResponse`
    - Definir los campos según el diseño: uuid, order_number, status, subtotal, shipping_cost, total, delivery_method, created_at, order_items_count para `Order`
    - Incluir order_items[], payment y shipment en `OrderDetail`
    - _Requisitos: 3.3, 3.5_
  - [x] 1.2 Crear `appointments.interface.ts` en `account/interfaces/` con las interfaces `Appointment` y `AppointmentsResponse`
    - Campos: appointment_uuid, customer_name, customer_lastname, phone_1, vehicle_brandname, vehicle_modelname, vehicle_year, vehicle_mileage, appointment_type, appointment_scheduled_date, dealership_name, valuator_name, valuator_last_name
    - _Requisitos: 4.3_
  - [x] 1.3 Crear `quotations.interface.ts` en `account/interfaces/` con las interfaces `Quotation` y `QuotationsResponse`
    - Campos: uuid, status, created_at, appointment (con customer y vehicle), vehicle (con brand, line, model, year)
    - _Requisitos: 5.3_

- [x] 2. Crear servicios Angular para consumir los endpoints existentes
  - [x] 2.1 Crear `orders.service.ts` en `account/services/` con métodos `search()` y `detail(uuid)`
    - `search()` hace POST a `/api/boutique/orders/search` con header `Authorization: Bearer {user_token}`
    - `detail(uuid)` hace POST a `/api/boutique/orders/detail` con `{ uuid }` y header Bearer
    - _Requisitos: 3.1, 3.5, 6.1_
  - [x] 2.2 Crear `appointments.service.ts` en `account/services/` con método `search()`
    - POST a `/api/appointment/search` con `{ type: '', keyword: '', paginate: 100 }` y header Bearer
    - _Requisitos: 4.1, 6.2_
  - [x] 2.3 Crear `quotations.service.ts` en `account/services/` con método `search()`
    - GET a `/api/valuations/search` con header Bearer
    - _Requisitos: 5.1, 6.3_

- [x] 3. Implementar la navegación por pestañas en ProfileComponent
  - Agregar variable `activeTab: string = 'perfil'` al componente
  - Agregar método `selectTab(tab: string)` que cambia `activeTab`
  - Modificar `profile.component.html`: insertar barra de tabs debajo del hero y antes de `profile-container`, envolver contenido existente en `*ngIf="activeTab === 'perfil'"`
  - Agregar `*ngIf` para cada sección: pedidos, citas, cotizaciones con sus componentes hijos
  - Agregar estilos CSS para la barra de tabs: pestaña activa con color #1c69d4 e indicador inferior, responsive con scroll horizontal en < 480px
  - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 2.1, 2.2, 2.3_

- [x] 4. Checkpoint — Verificar navegación por pestañas
  - Asegurar que la compilación no tiene errores, preguntar al usuario si hay dudas.

- [x] 5. Implementar OrdersTabComponent (pestaña Pedidos)
  - [x] 5.1 Crear `OrdersTabComponent` en `account/components/orders-tab/` con template, estilos y lógica
    - Inyectar `OrdersService`, cargar pedidos en `ngOnInit`, almacenar en variable local para caché (Requisito 7.5)
    - Mostrar spinner con "Cargando pedidos..." mientras carga (Requisito 3.2)
    - Renderizar lista de tarjetas blancas (border-radius 16px) con: número de pedido, fecha, badge de estado con color según mapeo del diseño, cantidad de artículos, total (Requisitos 3.3, 3.4)
    - Ordenar pedidos por `created_at` descendente (Requisito 3.8)
    - Mostrar estado vacío con ícono `shopping_bag` y texto "Aún no tienes pedidos en la Boutique" si lista vacía (Requisito 3.6)
    - Mostrar mensaje de error con botón "Reintentar" si falla la API (Requisito 3.7)
    - Implementar vista de detalle: al hacer click en un pedido, llamar `detail(uuid)` y mostrar artículos, envío, pago con botón "Volver" (Requisito 3.5)
    - No usar `.flat()` — usar `[].concat(...)` si se necesita aplanar arrays
    - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3_
  - [ ]* 5.2 Escribir test de propiedad para campos requeridos en tarjeta de pedido
    - **Propiedad 2: Campos requeridos en tarjeta de pedido**
    - **Valida: Requisito 3.3**
  - [ ]* 5.3 Escribir test de propiedad para mapeo de estado a color de badge
    - **Propiedad 5: Mapeo de estado/tipo a indicador visual (pedidos)**
    - **Valida: Requisito 3.4**
  - [ ]* 5.4 Escribir tests unitarios para OrdersTabComponent
    - Verificar renderizado de lista, estado vacío, estado de error, vista de detalle
    - _Requisitos: 3.2, 3.3, 3.5, 3.6, 3.7_

- [x] 6. Implementar AppointmentsTabComponent (pestaña Citas)
  - [x] 6.1 Crear `AppointmentsTabComponent` en `account/components/appointments-tab/` con template, estilos y lógica
    - Inyectar `AppointmentsService`, cargar citas en `ngOnInit`, almacenar en variable local para caché (Requisito 7.5)
    - Mostrar spinner con "Cargando citas..." mientras carga (Requisito 4.2)
    - Renderizar tarjetas blancas (border-radius 16px) con: fecha programada, tipo de cita con ícono Material según mapeo del diseño, nombre de sucursal, marca y modelo del vehículo (Requisitos 4.3, 4.4)
    - Ordenar citas por `appointment_scheduled_date` descendente (Requisito 4.7)
    - Mostrar estado vacío con ícono `event_busy` y texto "No tienes citas agendadas" si lista vacía (Requisito 4.5)
    - Mostrar mensaje de error con botón "Reintentar" si falla la API (Requisito 4.6)
    - No usar `.flat()` — usar `[].concat(...)` si se necesita aplanar arrays
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3_
  - [ ]* 6.2 Escribir test de propiedad para campos requeridos en tarjeta de cita
    - **Propiedad 3: Campos requeridos en tarjeta de cita**
    - **Valida: Requisito 4.3**
  - [ ]* 6.3 Escribir test de propiedad para mapeo de tipo a ícono Material
    - **Propiedad 5: Mapeo de estado/tipo a indicador visual (citas)**
    - **Valida: Requisito 4.4**
  - [ ]* 6.4 Escribir tests unitarios para AppointmentsTabComponent
    - Verificar renderizado de lista, estado vacío, estado de error
    - _Requisitos: 4.2, 4.3, 4.5, 4.6_

- [x] 7. Implementar QuotationsTabComponent (pestaña Cotizaciones)
  - [x] 7.1 Crear `QuotationsTabComponent` en `account/components/quotations-tab/` con template, estilos y lógica
    - Inyectar `QuotationsService`, cargar cotizaciones en `ngOnInit`, almacenar en variable local para caché (Requisito 7.5)
    - Mostrar spinner con "Cargando cotizaciones..." mientras carga (Requisito 5.2)
    - Renderizar tarjetas blancas (border-radius 16px) con: marca y modelo del vehículo, año, kilometraje, badge de estado con color, fecha de creación (Requisitos 5.3, 5.4)
    - Ordenar cotizaciones por `created_at` descendente (Requisito 5.7)
    - Mostrar estado vacío con ícono `directions_car` y texto "No tienes cotizaciones registradas" si lista vacía (Requisito 5.5)
    - Mostrar mensaje de error con botón "Reintentar" si falla la API (Requisito 5.6)
    - No usar `.flat()` — usar `[].concat(...)` si se necesita aplanar arrays
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 7.1, 7.2, 7.3, 7.4, 7.5, 8.1, 8.2, 8.3_
  - [ ]* 7.2 Escribir test de propiedad para campos requeridos en tarjeta de cotización
    - **Propiedad 4: Campos requeridos en tarjeta de cotización**
    - **Valida: Requisito 5.3**
  - [ ]* 7.3 Escribir tests unitarios para QuotationsTabComponent
    - Verificar renderizado de lista, estado vacío, estado de error
    - _Requisitos: 5.2, 5.3, 5.5, 5.6_

- [x] 8. Registrar componentes en AccountModule y cablear todo
  - Declarar `OrdersTabComponent`, `AppointmentsTabComponent` y `QuotationsTabComponent` en el array `declarations` de `AccountModule`
  - Importar `HttpClientModule` en `AccountModule` si no está disponible (verificar que `provideHttpClient(withInterceptorsFromDi())` en app.module.ts ya lo provee)
  - Verificar que los selectores de los componentes hijos están correctamente referenciados en `profile.component.html`
  - _Requisitos: 1.1, 1.3, 2.1_

- [x] 9. Checkpoint final — Verificar compilación y funcionalidad completa
  - Asegurar que no hay errores de compilación, que las 4 pestañas navegan correctamente, y preguntar al usuario si hay dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los endpoints del backend ya existen — no se requieren cambios en Laravel
- El token se obtiene de `localStorage.getItem('user_token')` con header `Authorization: Bearer`
- No usar `.flat()` (ES2019) — usar `[].concat(...)` para compatibilidad ES2017
- No usar Material form fields — los tabs se implementan con HTML/CSS puro
- Toda la UI debe estar en español
