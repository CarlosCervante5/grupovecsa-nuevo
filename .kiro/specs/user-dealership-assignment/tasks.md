# Plan de Implementación: Asignación de Usuarios a Sucursales

## Resumen

Implementar la relación muchos-a-muchos entre usuarios y sucursales en el sistema VECSA. Se crea la tabla pivote, endpoints API de asignación, se modifica el listado de usuarios para incluir sucursales, se agrega un tipo de campo `multi-select` al sistema de formularios del panel de desarrollador, y se implementa un filtro por sucursal.

## Tareas

- [x] 1. Crear migración y relaciones del modelo
  - [x] 1.1 Crear migración para la tabla pivote `app_vecsa_dealership_user`
    - Crear archivo de migración en `vecsa-backend/database/migrations/`
    - Definir columnas: `id`, `user_id` (FK → users), `dealership_id` (FK → dealerships), `timestamps`
    - Agregar índice único compuesto `(user_id, dealership_id)`
    - Usar `env('DB_TABLE_PREFIX', '')` para el nombre de tabla, consistente con el resto del proyecto
    - _Requirements: 1.1_

  - [x] 1.2 Agregar relación `dealerships()` al modelo User
    - Agregar método `dealerships()` que retorne `belongsToMany(Dealership::class, ...)` en `vecsa-backend/app/Models/User.php`
    - Usar `env('DB_TABLE_PREFIX', '')` para el nombre de la tabla pivote
    - _Requirements: 1.4_

  - [x] 1.3 Agregar relación `users()` al modelo Dealership
    - Agregar método `users()` que retorne `belongsToMany(User::class, ...)` en `vecsa-backend/app/Models/Dealership.php`
    - Usar `env('DB_TABLE_PREFIX', '')` para el nombre de la tabla pivote
    - _Requirements: 1.5_

- [x] 2. Implementar endpoints API de asignación
  - [x] 2.1 Crear `UserDealershipController` con método `assignDealerships`
    - Crear `vecsa-backend/app/Http/Controllers/Users/UserDealershipController.php`
    - Validar `user_uuid` (required, exists en users) y `dealership_ids` (present, array, cada elemento exists en dealerships)
    - Buscar usuario por UUID, ejecutar `$user->dealerships()->sync($dealershipIds)`
    - Retornar lista actualizada de dealerships usando `ApiResponseHelper`
    - Manejar errores 404 (usuario no encontrado) y 422 (validación)
    - _Requirements: 2.1, 2.3, 2.4_

  - [x] 2.2 Agregar método `getUserDealerships` al `UserDealershipController`
    - Validar `user_uuid` (required, exists)
    - Retornar `$user->dealerships` usando `ApiResponseHelper`
    - Manejar error 404 si usuario no existe
    - _Requirements: 2.2, 2.3_

  - [x] 2.3 Agregar método `users()` al `DealershipController`
    - Validar `dealership_id` (required, integer, exists en dealerships)
    - Retornar `Dealership::find($id)->users()->with('userProfile')->paginate(15)`
    - Manejar error 404 si sucursal no existe
    - _Requirements: 6.1, 6.2_

  - [x] 2.4 Registrar rutas nuevas en `api.php`
    - Agregar `POST /users/assign_dealerships` → `UserDealershipController::assignDealerships` con middleware `auth:sanctum` y `role:administrator|developer`
    - Agregar `POST /users/dealerships` → `UserDealershipController::getUserDealerships` con middleware `auth:sanctum` y `role:administrator|developer`
    - Agregar `POST /dealerships/users` → `DealershipController::users` con middleware `auth:sanctum`
    - _Requirements: 2.5, 2.6, 6.3_

  - [ ]* 2.5 Escribir test de propiedad: Sync round-trip
    - **Property 1: Sync round-trip**
    - Para cualquier usuario y cualquier subconjunto de dealership IDs existentes, sincronizar y luego consultar debe retornar exactamente el mismo conjunto de IDs
    - **Validates: Requirements 1.2, 1.3, 2.1, 2.2**

  - [ ]* 2.6 Escribir test de propiedad: Sync es idempotente
    - **Property 2: Sync idempotent**
    - Para cualquier usuario y conjunto de dealership IDs, sincronizar dos veces produce el mismo resultado que sincronizar una vez
    - **Validates: Requirements 2.1**

  - [ ]* 2.7 Escribir test de propiedad: Autorización
    - **Property 3: Authorization enforcement**
    - Para cualquier usuario autenticado sin rol `administrator` o `developer`, llamar a `assign_dealerships` debe retornar 403 y no modificar asignaciones
    - **Validates: Requirements 2.6**

- [x] 3. Modificar listado de usuarios para incluir sucursales
  - [x] 3.1 Modificar `UserController::index()` para incluir dealerships
    - Agregar eager loading `with('dealerships:id,name')` a la query de usuarios
    - Agregar `$user->dealership_names = $user->dealerships->pluck('name')->implode(', ')` en el transform
    - _Requirements: 3.1_

  - [ ]* 3.2 Escribir test de propiedad: Listado incluye datos de sucursales
    - **Property 4: User list includes dealership data**
    - Para cualquier usuario retornado por `GET /api/users`, si tiene dealerships asignados, la respuesta debe contener `dealership_names` y `dealerships` correctos
    - **Validates: Requirements 3.1**

- [x] 4. Checkpoint — Verificar backend
  - Ejecutar migraciones y verificar que todos los endpoints funcionan correctamente
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Implementar multi-select en el frontend
  - [x] 5.1 Extender interfaz `FormField` con tipo `multi-select`
    - Modificar `vecsa-frontend/src/app/admin/developer/services/dev-crud.service.ts`
    - Agregar `'multi-select'` al union type de `type`
    - Agregar propiedades opcionales: `optionsEndpoint`, `optionsMethod`, `optionsDataKey`, `optionsValueKey`, `optionsLabelKey`
    - _Requirements: 4.1_

  - [x] 5.2 Agregar renderizado del multi-select en el template del modal
    - Modificar `vecsa-frontend/src/app/admin/developer/pages/dashboard/dashboard.component.html`
    - Agregar bloque `@if (field.type === 'multi-select')` con checkboxes para cada opción
    - Usar `isMultiSelected()` para checked y `toggleMultiSelect()` para change
    - _Requirements: 4.1, 4.2_

  - [x] 5.3 Agregar lógica de multi-select en el componente dashboard
    - Modificar `vecsa-frontend/src/app/admin/developer/pages/dashboard/dashboard.component.ts`
    - Agregar propiedad `dynamicOptions: Record<string, { value: string; label: string }[]>`
    - Implementar métodos `isMultiSelected()`, `toggleMultiSelect()`, `loadDynamicOptions()`
    - Cargar opciones dinámicas al abrir modal si el campo tiene `optionsEndpoint`
    - _Requirements: 4.1, 4.2_

- [x] 6. Integrar sucursales en la sección de usuarios
  - [x] 6.1 Agregar columna y campo de sucursales a la sección `users`
    - Agregar `{ key: 'dealership_names', label: 'Sucursales' }` al array `columns` de la sección users
    - Agregar campo `multi-select` con key `dealership_ids` al array `formFields` de la sección users, con `optionsEndpoint: 'dealerships/search'`
    - _Requirements: 3.2, 3.3, 4.1_

  - [x] 6.2 Implementar flujo de guardado con asignación de sucursales
    - Modificar `saveModal()` para que, al guardar un usuario exitosamente, si `dealership_ids` está presente, haga una segunda llamada a `POST /api/users/assign_dealerships`
    - En `openEdit()`, cargar las sucursales actuales del usuario y preseleccionarlas en `modalData.dealership_ids`
    - _Requirements: 4.2, 4.3, 4.4_

  - [x] 6.3 Implementar filtro por sucursal en la lista de usuarios
    - Agregar propiedades `dealershipFilter` y `dealershipOptions` al componente
    - Agregar selector de filtro en el template (similar al filtro de roles existente) con opciones: "Todas", "Sin sucursal", y cada sucursal disponible
    - Cargar opciones de sucursales dinámicamente al entrar a la sección de usuarios
    - Modificar `filteredRows` getter para aplicar filtro por sucursal
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [ ]* 6.4 Escribir test de propiedad: Filtro por sucursal
    - **Property 5: Dealership filter correctness**
    - Para cualquier lista de usuarios y cualquier valor de filtro seleccionado: si se selecciona una sucursal específica, todos los usuarios filtrados deben tener esa sucursal; si se selecciona "sin sucursal", todos deben tener cero asignaciones
    - **Validates: Requirements 5.2, 5.4**

- [x] 7. Endpoint de usuarios por sucursal
  - [ ]* 7.1 Escribir test de propiedad: Consistencia de consulta por sucursal
    - **Property 6: Dealership users query consistency**
    - Para cualquier sucursal, los usuarios retornados por `POST /api/dealerships/users` deben ser exactamente los que tienen esa sucursal en sus asignaciones
    - **Validates: Requirements 6.1**

- [x] 8. Seeder de datos de prueba
  - [x] 8.1 Crear seeder para asignaciones de prueba
    - Crear `vecsa-backend/database/seeders/DealershipUserSeeder.php`
    - Asignar usuarios existentes a sucursales existentes con datos representativos
    - _Requirements: 1.2, 1.3_

- [x] 9. Checkpoint final — Verificar integración completa
  - Verificar que el flujo completo funciona: listado con sucursales, creación/edición con multi-select, filtro por sucursal
  - Ensure all tests pass, ask the user if questions arise.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Los tests de propiedad validan propiedades universales de corrección
- El backend usa PHP/Laravel y el frontend usa TypeScript/Angular
