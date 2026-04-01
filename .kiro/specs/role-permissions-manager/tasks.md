# Plan de Implementación: Gestión de Permisos y Matriz Rol-Permisos

## Resumen

Implementar el CRUD de permisos (reutilizando el sistema genérico existente) y la vista de matriz rol-permisos con toggles en el Panel de Desarrollador. Incluye agregar `deleteById` al servicio, propiedades de estado para la matriz, métodos de carga y toggle, template HTML, estilos CSS, y navegación en el sidebar.

## Tasks

- [x] 1. Agregar método `deleteById` al DevCrudService y flag `useApiResourceDelete` al CrudSection
  - [x] 1.1 Agregar `useApiResourceDelete?: boolean` a la interfaz `CrudSection` en `dev-crud.service.ts`
    - Agregar la propiedad opcional al interface
    - _Requirements: 3.2_
  - [x] 1.2 Agregar método `deleteById(resource: string, id: number | string)` al `DevCrudService`
    - Implementar `this.http.delete(\`\${this.baseUrl}/api/\${resource}/\${id}\`, { headers: this.headers })`
    - _Requirements: 3.2_
  - [x] 1.3 Actualizar `executeDelete()` en `DeveloperDashboardComponent` para usar `deleteById` cuando `useApiResourceDelete` es `true`
    - Detectar el flag en la sección activa y llamar `deleteById` con el endpoint y el id del registro
    - _Requirements: 3.2_

- [x] 2. Agregar sección CRUD de Permisos al arreglo `sections`
  - [x] 2.1 Agregar la entrada de permisos al array `sections` en `dashboard.component.ts`
    - key: `'permissions'`, label: `'Permisos'`, icon: `'vpn_key'`
    - endpoint: `'permissions'`, method: `'GET'`, dataKey: `'permissions'`
    - columns: `id`, `name`, `guard_name`
    - storeEndpoint: `'permissions'`, deleteEndpoint: `'permissions'`, useApiResourceDelete: `true`, idKey: `'id'`
    - formFields: campo `name` tipo text requerido
    - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 7.1_
  - [ ]* 2.2 Write property test: Permission list completeness
    - **Property 1: Permission list completeness**
    - **Validates: Requirements 1.1, 1.2**

- [x] 3. Checkpoint - Verificar CRUD de permisos
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Implementar estado y métodos de la Matriz Rol-Permisos
  - [x] 4.1 Agregar propiedades de estado de la matriz al `DeveloperDashboardComponent`
    - `matrixRoles`, `matrixPermissions`, `matrixLoading`, `matrixError`, `matrixSaving`
    - `adminModules` readonly array con los 10 módulos
    - _Requirements: 4.1, 4.2, 6.2, 8.1_
  - [x] 4.2 Implementar método `loadMatrix()`
    - Hacer GET /api/permissions para obtener `matrixPermissions`
    - Hacer GET /api/roles para obtener lista de roles
    - Para cada rol, hacer GET /api/roles/{id} para obtener permisos
    - Construir `matrixRoles` con array de nombres de permisos
    - Manejar loading y error states
    - _Requirements: 4.1, 6.1, 6.2, 6.3_
  - [x] 4.3 Implementar método `isPermissionActive(role, permissionName)`
    - Retornar `role.permissions.includes(permissionName)`
    - _Requirements: 4.3, 4.4_
  - [x] 4.4 Implementar método `togglePermission(role, permission)`
    - Calcular nuevo array de permisos (agregar o quitar)
    - Enviar PUT /api/roles/{role.id} con `{ permissions: [...] }`
    - Actualizar estado local en éxito, revertir en error
    - Manejar `matrixSaving` por celda
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  - [x] 4.5 Implementar método `toggleModuleAccess(role, moduleName)`
    - Verificar si permiso `"access {module}"` existe en `matrixPermissions`
    - Si no existe, crear con POST /api/permissions y agregarlo a `matrixPermissions`
    - Luego ejecutar toggle normal
    - _Requirements: 8.2, 8.3, 8.4_
  - [ ]* 4.6 Write property test: Matrix checkbox state reflects assignment
    - **Property 2: Matrix checkbox state reflects assignment**
    - **Validates: Requirements 4.3, 4.4, 8.2**
  - [ ]* 4.7 Write property test: Toggle produces correct updated permissions array
    - **Property 4: Toggle produces correct updated permissions array**
    - **Validates: Requirements 5.1, 5.2, 8.4**
  - [ ]* 4.8 Write property test: Module toggle creates missing permission before assignment
    - **Property 5: Module toggle creates missing permission before assignment**
    - **Validates: Requirements 8.3**

- [x] 5. Checkpoint - Verificar lógica de la matriz
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Agregar template HTML de la matriz y actualizar navegación
  - [x] 6.1 Agregar sección "Herramientas" y botón "Matriz Rol-Permisos" al sidebar en `dashboard.component.html`
    - Agregar `<p class="dev-nav-label">Herramientas</p>` después del bloque de CRUDs
    - Agregar botón con icon `grid_on`, label "Matriz Rol-Permisos", que active `'role_matrix'`
    - _Requirements: 7.2, 7.3_
  - [x] 6.2 Agregar bloque `@if (activeSection === 'role_matrix')` al template principal
    - Título "Matriz Rol-Permisos"
    - Loading state, error state con botón reintentar
    - Tabla de permisos regulares: roles como filas, permisos como columnas, checkboxes
    - Sección "Módulos de Admin": roles como filas, módulos como columnas, checkboxes
    - Headers de permisos con texto vertical
    - _Requirements: 4.2, 4.3, 4.4, 6.2, 6.3, 8.1, 8.2_
  - [x] 6.3 Actualizar `selectSection()` para manejar `'role_matrix'`
    - Cuando key es `'role_matrix'`, llamar `loadMatrix()` en lugar de `loadData()`
    - _Requirements: 7.3_
  - [ ]* 6.4 Write property test: Matrix dimensions match data
    - **Property 3: Matrix dimensions match data**
    - **Validates: Requirements 4.2**

- [x] 7. Agregar estilos CSS de la matriz
  - [x] 7.1 Agregar estilos para la matriz en `dashboard.component.css`
    - `.matrix-table-wrap`, `.matrix-table`, `.matrix-role-header`, `.matrix-perm-header`
    - `.matrix-role-name`, `.matrix-cell`, `.matrix-section-title`
    - Sticky columns para nombre de rol, headers verticales para permisos
    - Estilos de checkbox con accent-color y estados disabled
    - _Requirements: 4.2, 8.1_

- [x] 8. Seed de permisos iniciales para testing
  - [x] 8.1 Crear o actualizar un seeder en `vecsa-backend/database/seeders/` para insertar permisos de prueba
    - Crear permisos base: `list users`, `create users`, `edit users`, `delete users`, `access marketing`, `access administrator`, `access developer`
    - Asignar permisos al rol `developer` usando `syncPermissions()`
    - _Requirements: 1.1, 4.1, 8.1_

- [x] 9. Checkpoint final - Verificar integración completa
  - Ensure all tests pass, ask the user if questions arise.

## Notas

- Tasks marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada task referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- El diseño usa TypeScript/Angular, todos los ejemplos de código usan ese stack
- No se requieren cambios en el backend (controllers ya existen), excepto el seeder de datos de prueba
