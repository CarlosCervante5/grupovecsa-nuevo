# Documento de Requisitos: Asignación de Usuarios a Sucursales

## Introducción

Este documento define los requisitos para implementar la separación de usuarios por sucursal en el sistema VECSA. El objetivo es crear una relación muchos-a-muchos entre usuarios y sucursales (dealerships), permitiendo que un usuario pueda estar asignado a más de una sucursal. Esta funcionalidad se integra con el sistema de roles existente (Spatie) y se gestiona desde el panel de desarrollador.

## Glosario

- **Sistema**: La aplicación VECSA (backend Laravel + frontend Angular)
- **Panel_Desarrollador**: El módulo de administración del frontend Angular ubicado en `/admin/developer`
- **Usuario**: Un registro en la tabla `users` con roles asignados mediante Spatie
- **Sucursal**: Un registro en la tabla `app_vecsa_dealerships` que representa una agencia/sucursal física
- **Asignación**: La relación entre un Usuario y una Sucursal en la tabla pivote `app_vecsa_dealership_user`
- **API_Asignación**: Los endpoints del backend que gestionan la relación Usuario-Sucursal
- **Contexto_Sucursal**: El filtro activo que limita los datos visibles según la sucursal seleccionada

## Requisitos

### Requisito 1: Relación muchos-a-muchos entre Usuarios y Sucursales

**User Story:** Como desarrollador, quiero que exista una relación muchos-a-muchos entre usuarios y sucursales, para que un usuario pueda pertenecer a más de una sucursal simultáneamente.

#### Criterios de Aceptación

1. THE Sistema SHALL almacenar las asignaciones de usuarios a sucursales en una tabla pivote `app_vecsa_dealership_user` con columnas `user_id`, `dealership_id` y timestamps.
2. THE Sistema SHALL permitir que un Usuario esté asignado a cero o más Sucursales.
3. THE Sistema SHALL permitir que una Sucursal tenga cero o más Usuarios asignados.
4. THE Sistema SHALL definir la relación `dealerships()` en el modelo User que retorne las Sucursales asociadas mediante `belongsToMany`.
5. THE Sistema SHALL definir la relación `users()` en el modelo Dealership que retorne los Usuarios asociados mediante `belongsToMany`.
6. IF un Usuario es eliminado (soft delete), THEN THE Sistema SHALL conservar los registros de Asignación en la tabla pivote sin eliminarlos.
7. IF una Sucursal es eliminada (soft delete), THEN THE Sistema SHALL conservar los registros de Asignación en la tabla pivote sin eliminarlos.

### Requisito 2: API para gestionar asignaciones de usuarios a sucursales

**User Story:** Como desarrollador, quiero endpoints API para asignar y desasignar usuarios a sucursales, para poder gestionar las relaciones desde el panel de desarrollador.

#### Criterios de Aceptación

1. WHEN se envía una petición POST a `/api/users/assign_dealerships` con `user_uuid` y un arreglo `dealership_ids`, THE API_Asignación SHALL sincronizar las Sucursales asignadas al Usuario reemplazando las asignaciones previas.
2. WHEN se envía una petición POST a `/api/users/dealerships` con `user_uuid`, THE API_Asignación SHALL retornar la lista de Sucursales asignadas al Usuario.
3. IF el `user_uuid` proporcionado no corresponde a un Usuario existente, THEN THE API_Asignación SHALL retornar un error con código HTTP 404 y mensaje descriptivo.
4. IF el arreglo `dealership_ids` contiene un ID de Sucursal inexistente, THEN THE API_Asignación SHALL retornar un error con código HTTP 422 y mensaje descriptivo.
5. THE API_Asignación SHALL requerir autenticación mediante Sanctum para todos los endpoints de asignación.
6. THE API_Asignación SHALL requerir el rol `administrator` o `developer` para ejecutar operaciones de asignación.

### Requisito 3: Visualización de sucursales asignadas en el listado de usuarios

**User Story:** Como administrador del panel de desarrollador, quiero ver las sucursales asignadas a cada usuario en el listado de usuarios, para tener visibilidad de la distribución del personal.

#### Criterios de Aceptación

1. WHEN el Panel_Desarrollador carga la sección de Usuarios, THE Sistema SHALL incluir las Sucursales asignadas a cada Usuario en la respuesta de la API.
2. THE Panel_Desarrollador SHALL mostrar una columna "Sucursales" en la tabla de usuarios que liste los nombres de las Sucursales asignadas.
3. WHEN un Usuario no tiene Sucursales asignadas, THE Panel_Desarrollador SHALL mostrar un indicador de "Sin sucursal" en la columna correspondiente.

### Requisito 4: Interfaz de asignación de sucursales a usuarios

**User Story:** Como administrador del panel de desarrollador, quiero poder asignar y desasignar sucursales a un usuario desde el formulario de edición, para gestionar la distribución del personal por sucursal.

#### Criterios de Aceptación

1. WHEN el Panel_Desarrollador abre el formulario de creación o edición de un Usuario, THE Panel_Desarrollador SHALL mostrar un campo de selección múltiple con la lista de todas las Sucursales disponibles.
2. WHEN el Panel_Desarrollador abre el formulario de edición de un Usuario, THE Panel_Desarrollador SHALL preseleccionar las Sucursales actualmente asignadas al Usuario.
3. WHEN el administrador guarda el formulario de un Usuario con Sucursales seleccionadas, THE Panel_Desarrollador SHALL enviar la lista de Sucursales seleccionadas a la API_Asignación para sincronizar las asignaciones.
4. WHEN el administrador deselecciona todas las Sucursales de un Usuario y guarda, THE Panel_Desarrollador SHALL enviar un arreglo vacío a la API_Asignación para remover todas las asignaciones.

### Requisito 5: Filtrado de usuarios por sucursal

**User Story:** Como administrador del panel de desarrollador, quiero filtrar la lista de usuarios por sucursal, para encontrar rápidamente al personal asignado a una sucursal específica.

#### Criterios de Aceptación

1. THE Panel_Desarrollador SHALL mostrar un selector de filtro por Sucursal en la sección de Usuarios.
2. WHEN el administrador selecciona una Sucursal en el filtro, THE Panel_Desarrollador SHALL mostrar únicamente los Usuarios asignados a la Sucursal seleccionada.
3. WHEN el administrador selecciona "Todas" en el filtro de Sucursal, THE Panel_Desarrollador SHALL mostrar todos los Usuarios sin filtrar por Sucursal.
4. WHEN el administrador selecciona "Sin sucursal" en el filtro, THE Panel_Desarrollador SHALL mostrar únicamente los Usuarios que no tienen ninguna Sucursal asignada.

### Requisito 6: Endpoint para listar usuarios por sucursal

**User Story:** Como desarrollador, quiero un endpoint que retorne los usuarios filtrados por sucursal, para que otros módulos del sistema puedan consultar el personal de una sucursal específica.

#### Criterios de Aceptación

1. WHEN se envía una petición POST a `/api/dealerships/users` con `dealership_id`, THE API_Asignación SHALL retornar la lista paginada de Usuarios asignados a la Sucursal indicada, incluyendo su rol y perfil.
2. IF el `dealership_id` proporcionado no corresponde a una Sucursal existente, THEN THE API_Asignación SHALL retornar un error con código HTTP 404 y mensaje descriptivo.
3. THE API_Asignación SHALL requerir autenticación mediante Sanctum para el endpoint de usuarios por sucursal.
