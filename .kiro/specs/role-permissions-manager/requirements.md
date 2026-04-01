# Documento de Requisitos — Gestión de Permisos y Roles

## Introducción

Este módulo agrega al panel de desarrollador la capacidad de gestionar permisos del sistema y asignar qué permisos tiene cada rol, utilizando el sistema existente de Spatie (tablas `roles`, `permissions`, `role_has_permissions`). Incluye un CRUD de permisos y una vista de matriz rol-permisos con toggles para activar/desactivar la relación.

## Glosario

- **Panel_Desarrollador**: Módulo de administración accesible únicamente por usuarios con rol `developer`, ubicado en `/admin/developer`
- **Permission_Manager**: Vista dentro del Panel_Desarrollador para listar, crear, editar y eliminar permisos del sistema
- **Role_Permission_Matrix**: Vista dentro del Panel_Desarrollador que muestra una tabla cruzada de roles (filas) y permisos (columnas) con toggles para asignar/desasignar
- **Spatie_Permission_System**: Paquete Laravel `spatie/laravel-permission` que gestiona roles y permisos mediante las tablas `roles`, `permissions`, `role_has_permissions`, `model_has_roles`, `model_has_permissions`
- **RoleController**: Controlador API existente que expone CRUD de roles como `apiResource` en la ruta `/api/roles`
- **PermissionController**: Controlador API existente que expone CRUD de permisos como `apiResource` en la ruta `/api/permissions`
- **DevCrudService**: Servicio Angular del Panel_Desarrollador que realiza peticiones HTTP al backend

## Requisitos

### Requisito 1: Listado de Permisos

**User Story:** Como desarrollador, quiero ver todos los permisos existentes en el sistema, para poder auditar y gestionar los permisos disponibles.

#### Criterios de Aceptación

1. WHEN el usuario navega a la sección "Permisos" del Panel_Desarrollador, THE Permission_Manager SHALL mostrar una tabla con todos los permisos obtenidos del endpoint `GET /api/permissions`
2. THE Permission_Manager SHALL mostrar las columnas `id`, `name` y `guard_name` para cada permiso
3. WHEN no existen permisos en el sistema, THE Permission_Manager SHALL mostrar un mensaje indicando "No hay permisos registrados"

### Requisito 2: Creación de Permisos

**User Story:** Como desarrollador, quiero crear nuevos permisos, para poder definir controles de acceso granulares en el sistema.

#### Criterios de Aceptación

1. WHEN el usuario hace clic en el botón "Crear Permiso", THE Permission_Manager SHALL mostrar un formulario con el campo `name` (requerido)
2. WHEN el usuario envía el formulario con un nombre válido, THE Permission_Manager SHALL enviar una petición `POST /api/permissions` con el nombre del permiso
3. WHEN el backend responde con código 201, THE Permission_Manager SHALL agregar el nuevo permiso a la tabla y cerrar el formulario
4. IF el nombre del permiso ya existe en el sistema, THEN THE Permission_Manager SHALL mostrar el mensaje de error retornado por el backend sin cerrar el formulario

### Requisito 3: Eliminación de Permisos

**User Story:** Como desarrollador, quiero eliminar permisos que ya no se necesitan, para mantener limpio el catálogo de permisos.

#### Criterios de Aceptación

1. WHEN el usuario hace clic en el botón "Eliminar" de un permiso, THE Permission_Manager SHALL mostrar un diálogo de confirmación antes de proceder
2. WHEN el usuario confirma la eliminación, THE Permission_Manager SHALL enviar una petición `DELETE /api/permissions/{id}` al backend
3. WHEN el backend responde con código 204, THE Permission_Manager SHALL remover el permiso de la tabla
4. IF la eliminación falla, THEN THE Permission_Manager SHALL mostrar el mensaje de error retornado por el backend

### Requisito 4: Listado de Roles con sus Permisos

**User Story:** Como desarrollador, quiero ver todos los roles y los permisos que tiene cada uno, para entender la configuración actual de acceso.

#### Criterios de Aceptación

1. WHEN el usuario navega a la sección "Matriz Rol-Permisos" del Panel_Desarrollador, THE Role_Permission_Matrix SHALL obtener todos los roles con sus permisos desde `GET /api/roles` y todos los permisos desde `GET /api/permissions`
2. THE Role_Permission_Matrix SHALL mostrar una tabla donde las filas representan roles y las columnas representan permisos
3. THE Role_Permission_Matrix SHALL mostrar un toggle activado para cada combinación rol-permiso donde el rol posee ese permiso
4. THE Role_Permission_Matrix SHALL mostrar un toggle desactivado para cada combinación rol-permiso donde el rol no posee ese permiso

### Requisito 5: Asignación y Desasignación de Permisos a Roles

**User Story:** Como desarrollador, quiero activar o desactivar permisos para cada rol mediante toggles, para configurar el acceso de cada rol de forma visual.

#### Criterios de Aceptación

1. WHEN el usuario activa un toggle en la Role_Permission_Matrix, THE Role_Permission_Matrix SHALL enviar una petición `PUT /api/roles/{id}` con el arreglo actualizado de permisos del rol
2. WHEN el usuario desactiva un toggle en la Role_Permission_Matrix, THE Role_Permission_Matrix SHALL enviar una petición `PUT /api/roles/{id}` con el arreglo actualizado de permisos del rol (sin el permiso removido)
3. WHEN el backend responde exitosamente, THE Role_Permission_Matrix SHALL actualizar visualmente el estado del toggle
4. IF la actualización falla, THEN THE Role_Permission_Matrix SHALL revertir el toggle a su estado anterior y mostrar un mensaje de error

### Requisito 6: Carga de Datos de Roles con Permisos

**User Story:** Como desarrollador, quiero que la matriz cargue los permisos actuales de cada rol, para que los toggles reflejen el estado real del sistema.

#### Criterios de Aceptación

1. THE Role_Permission_Matrix SHALL obtener cada rol con sus permisos utilizando `GET /api/roles/{id}` (que retorna el rol con la relación `permissions` cargada) para construir el estado inicial de los toggles
2. WHILE la Role_Permission_Matrix está cargando datos, THE Role_Permission_Matrix SHALL mostrar un indicador de carga
3. IF la carga de datos falla, THEN THE Role_Permission_Matrix SHALL mostrar un mensaje de error con opción de reintentar

### Requisito 7: Navegación en el Panel de Desarrollador

**User Story:** Como desarrollador, quiero acceder a las vistas de permisos y matriz desde el menú lateral del panel, para navegar fácilmente entre secciones.

#### Criterios de Aceptación

1. THE Panel_Desarrollador SHALL incluir una sección "Permisos" en el menú lateral con ícono `vpn_key` que active la vista Permission_Manager
2. THE Panel_Desarrollador SHALL incluir una sección "Matriz Rol-Permisos" en el menú lateral con ícono `grid_on` que active la vista Role_Permission_Matrix
3. WHEN el usuario selecciona una sección del menú, THE Panel_Desarrollador SHALL cargar la vista correspondiente y resaltar la sección activa en el menú

### Requisito 8: Definición de Vistas/Secciones Accesibles por Rol

**User Story:** Como desarrollador, quiero definir qué módulos del panel administrativo puede acceder cada rol, para controlar la navegación según el rol del usuario.

#### Criterios de Aceptación

1. THE Role_Permission_Matrix SHALL incluir una sección de "Módulos de Admin" que liste los módulos disponibles: `marketing`, `gestor`, `staff`, `receptionist`, `valuator`, `appointment_manager`, `administrator`, `bodywork_paint_technician`, `spare_parts`, `developer`
2. THE Role_Permission_Matrix SHALL mostrar toggles para cada combinación rol-módulo, representados como permisos con el prefijo `access ` (ejemplo: `access marketing`, `access administrator`)
3. WHEN el usuario activa un toggle de módulo para un rol, THE Role_Permission_Matrix SHALL crear el permiso `access {módulo}` si no existe y asignarlo al rol
4. WHEN el usuario desactiva un toggle de módulo para un rol, THE Role_Permission_Matrix SHALL remover el permiso `access {módulo}` del rol
