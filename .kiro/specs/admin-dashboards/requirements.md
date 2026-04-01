# Documento de Requisitos — Dashboards Administrativos con Datos Reales por Rol

## Introducción

Este documento define los requisitos para implementar dashboards con datos reales en cada panel administrativo del sistema VECSA. Actualmente, la mayoría de los paneles admin (marketing, staff, receptionist, valuator, appointment_manager, bodywork_paint_technician, spare_parts, administrator) muestran únicamente un componente Overview con tarjetas de navegación estáticas, sin métricas ni gráficas. Los paneles de Developer y Store ya cuentan con dashboards funcionales con stat cards y gráficas ECharts.

El objetivo es crear un endpoint backend unificado que retorne métricas según el rol del usuario, actualizar cada dashboard frontend para mostrar stat cards y gráficas con datos reales de la API, y migrar los paneles que aún no usan sidebar layout al patrón establecido por gestor/store/developer.

## Glosario

- **Sistema_Dashboard**: El sistema de dashboards administrativos que muestra métricas y gráficas por rol
- **Endpoint_Métricas**: Endpoint backend `POST /api/admin-dashboard/metrics` que retorna métricas según el rol del usuario autenticado
- **Stat_Card**: Componente visual que muestra un valor numérico con icono, etiqueta y color representando una métrica
- **Panel_Admin**: Cada uno de los 11 paneles administrativos del sistema (/admin/{rol})
- **Sidebar_Layout**: Patrón de layout con barra lateral de navegación, usado por developer, gestor y store
- **Overview_Component**: Componente legacy que muestra tarjetas de navegación sin métricas reales
- **ECharts**: Librería de gráficas utilizada en los dashboards existentes (developer, store)
- **Rol**: Identificador del tipo de usuario (developer, administrator, marketing, staff, gestor, receptionist, valuator, appointment_manager, bodywork_paint_technician, spare_parts)

## Requisitos

### Requisito 1: Endpoint Backend de Métricas por Rol

**Historia de Usuario:** Como administrador del sistema, quiero un endpoint que retorne métricas relevantes según mi rol, para que cada panel muestre datos reales pertinentes a mi función.

#### Criterios de Aceptación

1. WHEN el Endpoint_Métricas recibe una solicitud POST con token válido, THE Sistema_Dashboard SHALL retornar métricas específicas según el rol del usuario autenticado
2. WHEN un usuario con rol "administrator" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: vehículos, productos boutique, pedidos boutique, usuarios, clientes, sucursales, valuaciones, citas, y datos para gráficas de pedidos por mes y por estatus
3. WHEN un usuario con rol "marketing" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: campañas activas, promociones, eventos, vehículos publicados, y datos para gráfica de vehículos por marca
4. WHEN un usuario con rol "staff" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: clientes registrados, recompensas activas, y total de puntos en el sistema
5. WHEN un usuario con rol "gestor" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: promociones, eventos, recompensas activas, y datos para gráfica de eventos por mes
6. WHEN un usuario con rol "receptionist" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: citas del día, citas de la semana, total de citas, y datos para gráfica de citas por tipo
7. WHEN un usuario con rol "valuator" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: valuaciones pendientes, valuaciones en progreso, valuaciones completadas, total de valuaciones, y datos para gráfica de valuaciones por estatus
8. WHEN un usuario con rol "appointment_manager" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: citas de hoy, citas de la semana, citas pendientes de asignar, total de citas, y datos para gráfica de citas por mes
9. WHEN un usuario con rol "bodywork_paint_technician" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: valuaciones con reparaciones pendientes, reparaciones en progreso, reparaciones completadas, y datos para gráfica de reparaciones por estatus
10. WHEN un usuario con rol "spare_parts" solicita métricas, THE Endpoint_Métricas SHALL retornar conteos de: valuaciones con refacciones pendientes, refacciones en revisión, refacciones completadas, y datos para gráfica de refacciones por estatus
11. IF un usuario no autenticado solicita el Endpoint_Métricas, THEN THE Sistema_Dashboard SHALL retornar un error 401 con mensaje descriptivo
12. IF un usuario con rol no reconocido solicita métricas, THEN THE Endpoint_Métricas SHALL retornar un conjunto vacío de métricas con código 200
13. THE Endpoint_Métricas SHALL utilizar funciones compatibles con SQLite para consultas de fecha (strftime en lugar de YEAR/MONTH de MySQL)

### Requisito 2: Dashboard con Stat Cards para Cada Panel

**Historia de Usuario:** Como usuario administrativo, quiero ver tarjetas con métricas numéricas reales en mi dashboard, para tener una vista rápida del estado de los datos relevantes a mi función.

#### Criterios de Aceptación

1. WHEN el dashboard de un Panel_Admin se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards con valores numéricos obtenidos del Endpoint_Métricas
2. WHILE las métricas se están cargando desde la API, THE Sistema_Dashboard SHALL mostrar un indicador de carga en cada Stat_Card
3. IF la llamada al Endpoint_Métricas falla, THEN THE Sistema_Dashboard SHALL mostrar "Error" en el valor de cada Stat_Card
4. THE Sistema_Dashboard SHALL mostrar cada Stat_Card con un icono Material Icons, una etiqueta descriptiva, un valor numérico y un color distintivo
5. WHEN el dashboard del panel "administrator" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: vehículos, productos, pedidos, usuarios, clientes, sucursales, valuaciones y citas
6. WHEN el dashboard del panel "marketing" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: campañas activas, promociones, eventos y vehículos publicados
7. WHEN el dashboard del panel "staff" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: clientes, recompensas activas y total de puntos
8. WHEN el dashboard del panel "gestor" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: promociones, eventos y recompensas
9. WHEN el dashboard del panel "receptionist" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: citas de hoy, citas de la semana y total de citas
10. WHEN el dashboard del panel "valuator" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: valuaciones pendientes, en progreso, completadas y total
11. WHEN el dashboard del panel "appointment_manager" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: citas de hoy, citas de la semana, pendientes de asignar y total
12. WHEN el dashboard del panel "bodywork_paint_technician" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: reparaciones pendientes, en progreso y completadas
13. WHEN el dashboard del panel "spare_parts" se carga, THE Sistema_Dashboard SHALL mostrar Stat_Cards para: refacciones pendientes, en revisión y completadas

### Requisito 3: Gráficas ECharts por Rol

**Historia de Usuario:** Como usuario administrativo, quiero ver gráficas con datos reales en mi dashboard, para visualizar tendencias y distribuciones relevantes a mi función.

#### Criterios de Aceptación

1. WHEN el dashboard de un Panel_Admin se carga y las métricas contienen datos para gráficas, THE Sistema_Dashboard SHALL renderizar gráficas ECharts con los datos recibidos
2. WHEN el dashboard del panel "administrator" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de barras de pedidos por mes y una gráfica de pie de pedidos por estatus
3. WHEN el dashboard del panel "marketing" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de pie de vehículos por marca
4. WHEN el dashboard del panel "gestor" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de barras de eventos por mes
5. WHEN el dashboard del panel "receptionist" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de pie de citas por tipo (valuación, servicio)
6. WHEN el dashboard del panel "valuator" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de pie de valuaciones por estatus
7. WHEN el dashboard del panel "appointment_manager" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de barras de citas por mes
8. WHEN el dashboard del panel "bodywork_paint_technician" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de pie de reparaciones por estatus
9. WHEN el dashboard del panel "spare_parts" se carga, THE Sistema_Dashboard SHALL mostrar una gráfica de pie de refacciones por estatus
10. IF los datos para una gráfica están vacíos, THEN THE Sistema_Dashboard SHALL mostrar un mensaje "Sin datos" centrado en el área de la gráfica
11. WHEN la ventana del navegador cambia de tamaño, THE Sistema_Dashboard SHALL redimensionar las gráficas ECharts proporcionalmente

### Requisito 4: Migración a Sidebar Layout

**Historia de Usuario:** Como usuario administrativo, quiero que todos los paneles tengan una barra lateral de navegación consistente, para tener una experiencia uniforme en todos los paneles del sistema.

#### Criterios de Aceptación

1. THE Sistema_Dashboard SHALL implementar un layout con sidebar para los paneles: administrator, marketing, staff, receptionist, valuator, appointment_manager, bodywork_paint_technician y spare_parts
2. THE Sidebar_Layout de cada panel SHALL incluir: logo/nombre del panel, avatar con inicial del usuario, nombre del usuario, rol, enlaces de navegación a los módulos del panel, enlace "Ir al inicio" y botón "Cerrar sesión"
3. WHEN un usuario navega entre secciones del panel, THE Sidebar_Layout SHALL resaltar el enlace activo con estilo visual diferenciado
4. THE Sidebar_Layout SHALL seguir el mismo patrón visual establecido por el panel gestor (GestorLayoutComponent) y el panel store (StoreLayoutComponent)
5. WHEN el panel "administrator" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard, Usuarios, Permisos y Boutique
6. WHEN el panel "marketing" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard, Vehículos, Home Slides y Testimonios
7. WHEN el panel "staff" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard, Registro de KM y Registro de Compras
8. WHEN el panel "receptionist" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard y Formulario de Recepción
9. WHEN el panel "valuator" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard y Citas de Valuación
10. WHEN el panel "appointment_manager" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard y Asignar Citas
11. WHEN el panel "bodywork_paint_technician" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard y Hojalatería y Pintura
12. WHEN el panel "spare_parts" se carga, THE Sidebar_Layout SHALL mostrar enlaces a: Dashboard y Refacciones
13. WHERE un usuario tiene el permiso "access store_management", THE Sidebar_Layout SHALL agregar un enlace a "Tienda" en la sección de herramientas
14. WHERE un usuario tiene el permiso "access benchmark", THE Sidebar_Layout SHALL agregar un enlace a "Benchmark ADS" en la sección de herramientas

### Requisito 5: Servicio Frontend de Métricas

**Historia de Usuario:** Como desarrollador frontend, quiero un servicio Angular centralizado para obtener métricas del dashboard, para reutilizar la lógica de comunicación con la API en todos los paneles.

#### Criterios de Aceptación

1. THE Sistema_Dashboard SHALL proveer un servicio Angular (`AdminDashboardService`) que encapsule la llamada al Endpoint_Métricas
2. THE AdminDashboardService SHALL enviar el token de autenticación almacenado en localStorage como header Authorization Bearer
3. THE AdminDashboardService SHALL exponer un método `getMetrics()` que retorne un Observable con las métricas del rol del usuario autenticado
4. WHEN el AdminDashboardService realiza una solicitud, THE AdminDashboardService SHALL incluir los headers Content-Type application/json y X-Requested-With XMLHttpRequest
5. THE AdminDashboardService SHALL ser inyectable a nivel raíz (providedIn: 'root') para ser compartido entre todos los módulos admin

### Requisito 6: Tarjetas de Navegación Rápida

**Historia de Usuario:** Como usuario administrativo, quiero tener acceso rápido a los módulos de mi panel desde el dashboard, para navegar eficientemente a las secciones que necesito.

#### Criterios de Aceptación

1. WHEN el dashboard de un Panel_Admin se carga, THE Sistema_Dashboard SHALL mostrar tarjetas de navegación rápida a los módulos disponibles del panel debajo de las Stat_Cards y gráficas
2. THE Sistema_Dashboard SHALL mostrar cada tarjeta de navegación con un icono Material Icons, el nombre del módulo y un indicador visual de enlace
3. WHEN un usuario hace clic en una tarjeta de navegación, THE Sistema_Dashboard SHALL navegar a la ruta correspondiente del módulo
