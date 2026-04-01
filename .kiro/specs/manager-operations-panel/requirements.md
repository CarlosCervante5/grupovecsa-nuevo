# Documento de Requisitos — Panel de Operaciones del Gerente

## Introducción

Panel administrativo dedicado para el rol "gerente" dentro de la plataforma VECSA, accesible en `/admin/gerente`. Este panel centraliza métricas operativas y analíticas de todas las áreas del negocio: vehículos, productos boutique, pedidos, usuarios, clientes, sucursales, valuaciones, citas, benchmark de competencia y rendimiento por sucursal. El gerente tiene acceso de lectura a todos los paneles de otros roles (como el administrador) pero sin capacidad de gestión de usuarios ni permisos. El backend expone un endpoint dedicado para métricas gerenciales bajo `admin-dashboard/metrics` reutilizando el `AdminDashboardController` existente.

## Glosario

- **Panel_Gerente**: Módulo administrativo de operaciones gerenciales dentro de la plataforma VECSA, accesible en `/admin/gerente`
- **Gerente**: Usuario con rol `gerente` en el sistema, orientado a supervisión operativa sin gestión de usuarios
- **GerenteGuard**: Guard Angular que protege el acceso al módulo verificando el rol `gerente` o el permiso `access gerente`
- **GerenteDashboardService**: Servicio Angular que gestiona las llamadas HTTP al endpoint de métricas gerenciales
- **Dashboard_Gerente**: Vista principal del Panel_Gerente con métricas agregadas, gráficas y accesos rápidos
- **AdminDashboardController**: Controlador Laravel existente que retorna métricas específicas por rol
- **Stat_Card**: Componente visual que muestra una métrica individual con icono, valor numérico y etiqueta
- **API_Métricas**: Endpoint POST `/api/admin-dashboard/metrics` protegido con `auth:sanctum`
- **DeveloperUserSeeder**: Seeder que crea roles, permisos y usuarios de prueba en el sistema

## Requisitos

### Requisito 1: Rol y Permisos del Gerente

**Historia de Usuario:** Como administrador del sistema, quiero crear el rol "gerente" con permisos de acceso a todos los paneles operativos, para que los gerentes puedan supervisar todas las áreas sin gestionar usuarios.

#### Criterios de Aceptación

1. THE DeveloperUserSeeder SHALL crear el rol `gerente` en la lista de roles del sistema
2. THE DeveloperUserSeeder SHALL crear los permisos `access gerente`, `access gestor`, `access receptionist`, `access valuator`, `access appointment_manager`, `access staff`, `access bodywork_paint_technician`, `access spare_parts`, `access store_management`, `access benchmark` y `access marketing` y asignarlos al rol `gerente`
3. THE DeveloperUserSeeder SHALL crear un usuario de prueba con email `gerente@vecsa.com`, contraseña `TestUser%2024%%`, nickname `gerente_test`, nombre `Gerente` y apellido `Test` con rol `gerente`
4. THE DeveloperUserSeeder SHALL asignar al rol `gerente` todos los permisos de tipo `access` existentes, excluyendo los permisos `list users`, `create users`, `update users` y `delete users`

### Requisito 2: Control de Acceso al Panel del Gerente

**Historia de Usuario:** Como Gerente, quiero que el acceso al panel esté protegido por validación de rol, para que solo usuarios autorizados accedan a las métricas operativas.

#### Criterios de Aceptación

1. WHEN un usuario navega a `/admin/gerente`, THE GerenteGuard SHALL invocar `validateRole` con `expected_role` igual a `gerente`
2. IF el usuario no posee el rol `gerente` y no posee el permiso `access gerente`, THEN THE GerenteGuard SHALL redirigir al usuario a `/auth/iniciar-sesion`
3. WHILE un usuario posee el rol `developer` o `administrator`, THE GerenteGuard SHALL permitir el acceso al Panel_Gerente mediante el permiso `access gerente`
4. THE GerenteDashboardService SHALL incluir el header `Authorization: Bearer {token}` en todas las peticiones HTTP, leyendo el token de `localStorage` con la clave `user_token`

### Requisito 3: Métricas Generales del Dashboard

**Historia de Usuario:** Como Gerente, quiero ver un resumen de métricas generales del negocio, para tener visibilidad completa del estado operativo.

#### Criterios de Aceptación

1. WHEN el Gerente accede al Dashboard_Gerente, THE Panel_Gerente SHALL mostrar Stat_Cards con: total de vehículos, productos activos, pedidos, usuarios, clientes, sucursales, valuaciones y citas
2. WHEN el Dashboard_Gerente solicita métricas, THE API_Métricas SHALL retornar los conteos consultando los modelos `Vehicle`, `BoutiqueProduct` (activos), `BoutiqueOrder`, `User`, `Customer`, `Dealership`, `VehicleValuation` y `CustomerAppointment`
3. WHILE las métricas se cargan, THE Panel_Gerente SHALL mostrar un estado de carga (skeleton) en cada Stat_Card
4. IF la API_Métricas retorna un error, THEN THE Panel_Gerente SHALL mostrar un mensaje de error y permitir reintentar la carga

### Requisito 4: Gráficas de Pedidos

**Historia de Usuario:** Como Gerente, quiero visualizar la tendencia de pedidos y su distribución por estado, para evaluar el rendimiento de ventas.

#### Criterios de Aceptación

1. THE Dashboard_Gerente SHALL renderizar una gráfica de barras con ECharts mostrando pedidos por mes de los últimos 6 meses
2. THE Dashboard_Gerente SHALL renderizar una gráfica de dona con ECharts mostrando la distribución de pedidos por estado
3. WHEN la API_Métricas calcula pedidos por mes, THE AdminDashboardController SHALL usar `whereBetween` sobre `created_at` para compatibilidad con SQLite
4. WHEN la API_Métricas calcula pedidos por estado, THE AdminDashboardController SHALL agrupar por el campo `status` y retornar el conteo por cada estado

### Requisito 5: Métricas de Boutique

**Historia de Usuario:** Como Gerente, quiero ver métricas de ventas y productos más vendidos de la boutique, para evaluar el rendimiento comercial.

#### Criterios de Aceptación

1. THE Dashboard_Gerente SHALL mostrar Stat_Cards con: total de ventas (suma de totales de pedidos pagados), pedidos pendientes y productos activos en boutique
2. WHEN la API_Métricas calcula el total de ventas, THE AdminDashboardController SHALL sumar los totales de pedidos con estado en `['pagado', 'en_preparacion', 'enviado', 'entregado']`
3. THE Dashboard_Gerente SHALL renderizar una gráfica de barras horizontales con ECharts mostrando los 5 productos más vendidos por cantidad
4. WHEN la API_Métricas calcula los productos más vendidos, THE AdminDashboardController SHALL consultar los items de pedidos agrupados por producto y ordenados por cantidad descendente, limitando a 5 resultados

### Requisito 6: Rendimiento por Sucursal

**Historia de Usuario:** Como Gerente, quiero comparar el rendimiento entre sucursales, para identificar oportunidades de mejora.

#### Criterios de Aceptación

1. THE Dashboard_Gerente SHALL renderizar una gráfica de barras con ECharts mostrando el conteo de valuaciones por sucursal
2. THE Dashboard_Gerente SHALL renderizar una gráfica de barras con ECharts mostrando el conteo de citas por sucursal
3. WHEN la API_Métricas calcula métricas por sucursal, THE AdminDashboardController SHALL agrupar valuaciones y citas por `dealership_id` y unir con el modelo `Dealership` para obtener el nombre de cada sucursal
4. IF una sucursal no tiene valuaciones ni citas registradas, THEN THE AdminDashboardController SHALL omitir esa sucursal de los resultados

### Requisito 7: Citas y Valuaciones Recientes

**Historia de Usuario:** Como Gerente, quiero ver el volumen de citas y valuaciones diarias y semanales, para monitorear la actividad operativa actual.

#### Criterios de Aceptación

1. THE Dashboard_Gerente SHALL mostrar Stat_Cards con: citas de hoy, citas de la semana, valuaciones pendientes y valuaciones en progreso
2. WHEN la API_Métricas calcula citas de hoy, THE AdminDashboardController SHALL filtrar `CustomerAppointment` por `scheduled_date` igual a la fecha actual
3. WHEN la API_Métricas calcula citas de la semana, THE AdminDashboardController SHALL filtrar `CustomerAppointment` con `scheduled_date` entre el inicio y fin de la semana actual
4. THE Dashboard_Gerente SHALL renderizar una gráfica de línea con ECharts mostrando citas por mes de los últimos 6 meses

### Requisito 8: Datos de Benchmark de Competencia

**Historia de Usuario:** Como Gerente, quiero ver un resumen de los datos de benchmark de la competencia, para tomar decisiones informadas sobre posicionamiento.

#### Criterios de Aceptación

1. THE Dashboard_Gerente SHALL mostrar una Stat_Card con el total de competidores registrados en el sistema de benchmark
2. THE Dashboard_Gerente SHALL mostrar una Stat_Card con el total de escaneos de benchmark realizados
3. THE Dashboard_Gerente SHALL proveer un enlace directo al módulo de Benchmark ADS en `/admin/benchmark`
4. WHEN la API_Métricas obtiene datos de benchmark, THE AdminDashboardController SHALL contar los archivos de competidores y escaneos almacenados en el directorio de benchmark


### Requisito 9: Navegación del Sidebar con Acceso a Paneles

**Historia de Usuario:** Como Gerente, quiero acceder a todos los paneles de otros roles desde el sidebar, para navegar rápidamente entre las diferentes áreas operativas.

#### Criterios de Aceptación

1. THE Panel_Gerente SHALL mostrar un sidebar con secciones: navegación principal, paneles de roles y herramientas
2. THE Panel_Gerente SHALL incluir en la sección de paneles enlaces a: Gestor, Recepción, Valuador, Citas, Staff, Hojalatería y Refacciones, filtrados por los permisos del usuario en localStorage
3. THE Panel_Gerente SHALL incluir en la sección de herramientas enlaces a: Tienda, Benchmark ADS y Marketing, filtrados por los permisos del usuario en localStorage
4. THE Panel_Gerente SHALL mostrar el nombre y rol del usuario autenticado en la parte superior del sidebar
5. THE Panel_Gerente SHALL incluir un botón de cerrar sesión que limpie localStorage y redirija a `/auth/login`

### Requisito 10: Estructura del Módulo Frontend

**Historia de Usuario:** Como desarrollador, quiero que el módulo del panel gerente siga las convenciones de arquitectura existentes, para mantener la consistencia del proyecto.

#### Criterios de Aceptación

1. THE Panel_Gerente SHALL implementarse como un módulo Angular lazy-loaded registrado en la ruta `/admin/gerente` dentro de `admin-routing.module.ts`
2. THE Panel_Gerente SHALL seguir la estructura de directorio: `vecsa-frontend/src/app/admin/gerente/` con subcarpetas `guards/`, `pages/layout/`, `pages/dashboard/` y `services/`
3. THE Panel_Gerente SHALL utilizar el componente de layout con sidebar y `<router-outlet>` siguiendo el patrón de `AdminLayoutComponent`
4. THE Panel_Gerente SHALL utilizar el estilo visual de VECSA: fondo blanco, color primario `#1c69d4`, tarjetas con `border-radius: 16px` y Material Icons
5. THE Panel_Gerente SHALL ocultar la navegación global mediante la propiedad `hideChrome` en la configuración de rutas
6. THE Panel_Gerente SHALL utilizar ECharts para todas las gráficas del dashboard

### Requisito 11: Endpoint de Métricas del Gerente

**Historia de Usuario:** Como desarrollador, quiero que el endpoint de métricas soporte el rol gerente, para retornar datos operativos completos.

#### Criterios de Aceptación

1. WHEN el AdminDashboardController recibe una petición de un usuario con rol `gerente`, THE AdminDashboardController SHALL retornar métricas completas incluyendo: stats generales, gráficas de pedidos, métricas de boutique, rendimiento por sucursal, citas y valuaciones recientes, y datos de benchmark
2. THE AdminDashboardController SHALL agregar el caso `gerente` en el `match` de la función `metrics` invocando un método privado `gerenteMetrics()`
3. THE AdminDashboardController SHALL utilizar el helper `ApiResponseHelper` para formatear la respuesta
4. WHEN la API_Métricas consulta datos por mes, THE AdminDashboardController SHALL usar `whereBetween` en lugar de funciones SQL como `YEAR()` o `MONTH()` para compatibilidad con SQLite
5. THE AdminDashboardController SHALL reutilizar los modelos existentes: `Vehicle`, `BoutiqueProduct`, `BoutiqueOrder`, `User`, `Customer`, `Dealership`, `VehicleValuation`, `CustomerAppointment` y `BoutiqueOrderItem`
