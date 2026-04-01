# Documento de Requisitos — Panel de Gestión de Tienda

## Introducción

Panel administrativo dedicado dentro de la plataforma VECSA que centraliza la gestión de operaciones de la boutique: pedidos, envíos, clientes, puntos/rewards, cupones y redenciones. Se accede mediante `/admin/store` con protección basada en el permiso `access store_management`. El backend expone endpoints bajo el prefijo `store-management/` protegidos con `auth:sanctum` y `permission:access store_management`, reutilizando los modelos existentes del sistema.

## Glosario

- **Panel_Tienda**: Módulo administrativo de gestión de tienda dentro de la plataforma VECSA, accesible en `/admin/store`
- **StoreService**: Servicio Angular centralizado que gestiona todas las llamadas HTTP al backend del panel de tienda
- **StoreManagementGuard**: Guard Angular que protege el acceso al módulo verificando el permiso `access store_management`
- **Dashboard**: Vista principal del Panel_Tienda con métricas agregadas y gráficas de la tienda
- **Pedido**: Registro de compra en la boutique representado por el modelo `BoutiqueOrder`
- **Envío**: Registro de envío asociado a un pedido representado por el modelo `BoutiqueShipment`
- **Cliente**: Usuario registrado en el sistema representado por el modelo `Customer`
- **Punto_Reward**: Movimiento individual de puntos representado por el modelo `RewardPoint`
- **Customer_Reward**: Relación entre un cliente y un programa de rewards representado por el modelo `CustomerReward`
- **Cupón**: Código de descuento representado por el modelo `CustomerCoupon`
- **Redención**: Solicitud de canje de puntos por un reward
- **Administrador_Tienda**: Usuario con permiso `access store_management` o rol `developer`
- **API_Tienda**: Conjunto de endpoints POST bajo el prefijo `store-management/` en el backend Laravel

## Requisitos

### Requisito 1: Control de Acceso al Panel de Tienda

**Historia de Usuario:** Como Administrador_Tienda, quiero que el acceso al panel esté protegido por permisos, para que solo usuarios autorizados puedan gestionar las operaciones de la tienda.

#### Criterios de Aceptación

1. WHEN un usuario navega a `/admin/store`, THE StoreManagementGuard SHALL verificar que el array de permisos en localStorage incluya `access store_management`
2. IF un usuario no posee el permiso `access store_management` y su rol no es `developer`, THEN THE StoreManagementGuard SHALL redirigir al usuario a la ruta `/`
3. WHILE un usuario posee el rol `developer`, THE StoreManagementGuard SHALL permitir el acceso al Panel_Tienda sin verificar permisos adicionales
4. THE StoreService SHALL incluir el header `Authorization: Bearer {token}` en todas las peticiones HTTP, leyendo el token de `localStorage` con la clave `user_token`
5. THE API_Tienda SHALL proteger todos los endpoints con los middleware `auth:sanctum` y `permission:access store_management`

### Requisito 2: Dashboard de Métricas

**Historia de Usuario:** Como Administrador_Tienda, quiero ver un resumen visual de las métricas de la tienda, para tener una visión general del estado del negocio.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede al Dashboard, THE Panel_Tienda SHALL mostrar cards con: total de pedidos, ingresos, pedidos pendientes, total de clientes y productos activos
2. WHEN el Dashboard calcula los ingresos, THE API_Tienda SHALL sumar únicamente los totales de pedidos con estado en `['pagado', 'en_preparacion', 'enviado', 'entregado']`
3. THE Dashboard SHALL renderizar gráficas con ECharts mostrando: pedidos por mes (últimos 6 meses) y distribución de pedidos por estado
4. WHEN el Dashboard consulta pedidos por mes, THE API_Tienda SHALL usar `whereBetween` en lugar de funciones SQL como `YEAR()` o `MONTH()` para compatibilidad con SQLite
5. THE Dashboard SHALL proveer navegación rápida a cada sección del Panel_Tienda: Pedidos, Envíos, Clientes, Puntos, Cupones y Redenciones

### Requisito 3: Gestión de Pedidos

**Historia de Usuario:** Como Administrador_Tienda, quiero buscar, filtrar y gestionar los pedidos de la boutique, para procesar las ventas de forma eficiente.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede a la sección de pedidos, THE Panel_Tienda SHALL mostrar una tabla paginada con columnas: número de pedido, cliente, estado, total y fecha
2. THE Panel_Tienda SHALL permitir filtrar pedidos por estado, rango de fechas y término de búsqueda (por número de pedido o nombre de envío)
3. WHEN el Administrador_Tienda selecciona un pedido, THE Panel_Tienda SHALL mostrar el detalle completo: información del pedido, lista de productos, información de pago e información de envío
4. THE Panel_Tienda SHALL mostrar badges de color por estado de pedido: pendiente en amarillo, pagado en verde, en_preparacion en azul, enviado en azul, entregado en verde oscuro y cancelado en rojo
5. THE API_Tienda SHALL retornar los pedidos ordenados por `created_at` descendente con paginación de 15 registros por página por defecto
6. WHEN el Administrador_Tienda busca pedidos, THE API_Tienda SHALL filtrar por `order_number` o `shipping_name` usando coincidencia parcial (LIKE)

### Requisito 4: Transiciones de Estado de Pedidos

**Historia de Usuario:** Como Administrador_Tienda, quiero cambiar el estado de los pedidos siguiendo un flujo válido, para mantener el control del proceso de venta.

#### Criterios de Aceptación

1. THE API_Tienda SHALL validar las transiciones de estado de pedidos según las reglas: pendiente puede cambiar a pagado o cancelado, pagado a en_preparacion o cancelado, en_preparacion a enviado o cancelado, enviado a entregado, entregado y cancelado no permiten cambios
2. IF el Administrador_Tienda intenta una transición de estado no válida, THEN THE API_Tienda SHALL retornar HTTP 400 con código `INVALID_STATUS_TRANSITION`
3. WHEN el Administrador_Tienda cancela un pedido, THE API_Tienda SHALL restaurar el inventario de los productos del pedido cancelado
4. WHEN el estado de un pedido cambia a `en_preparacion`, `enviado` o `entregado`, THE API_Tienda SHALL actualizar el estado del envío asociado al mismo valor

### Requisito 5: Gestión de Envíos

**Historia de Usuario:** Como Administrador_Tienda, quiero gestionar los envíos de forma centralizada, para dar seguimiento a las entregas de los pedidos.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede a la sección de envíos, THE Panel_Tienda SHALL mostrar una tabla con: número de pedido, cliente, carrier, número de tracking, estado y fecha estimada de entrega
2. THE Panel_Tienda SHALL permitir filtrar envíos por estado y carrier
3. WHEN el Administrador_Tienda genera una guía de envío, THE API_Tienda SHALL invocar el servicio EnviacomService existente para crear el envío y almacenar el número de tracking y URL de la guía
4. THE Panel_Tienda SHALL proveer un enlace al tracking externo del envío cuando el número de tracking esté disponible

### Requisito 6: Gestión de Clientes

**Historia de Usuario:** Como Administrador_Tienda, quiero consultar la información de los clientes y su historial, para brindar mejor atención y seguimiento.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede a la sección de clientes, THE Panel_Tienda SHALL mostrar una lista paginada de clientes con búsqueda por nombre o email
2. WHEN el Administrador_Tienda selecciona un cliente, THE Panel_Tienda SHALL mostrar: datos personales, historial de pedidos, balance de puntos y cupones asignados
3. WHEN la API_Tienda consulta el detalle de un cliente, THE API_Tienda SHALL retornar `total_points` calculado como la suma de `earned_points` no redimidos del cliente
4. IF el UUID del cliente no existe en la base de datos, THEN THE API_Tienda SHALL retornar HTTP 404 con código `CUSTOMER_NOT_FOUND`

### Requisito 7: Gestión de Puntos y Rewards

**Historia de Usuario:** Como Administrador_Tienda, quiero consultar y ajustar manualmente los puntos de los clientes, para corregir errores o aplicar bonificaciones especiales.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede a la sección de puntos, THE Panel_Tienda SHALL mostrar una lista de clientes con su balance de puntos actual
2. THE Panel_Tienda SHALL mostrar el historial de movimientos de puntos por cliente
3. THE Panel_Tienda SHALL permitir al Administrador_Tienda realizar ajustes manuales de puntos especificando: tipo (agregar o restar), cantidad de puntos y motivo obligatorio
4. WHEN se realiza un ajuste manual de puntos, THE API_Tienda SHALL crear un registro en `reward_points` con `name` igual a `ajuste_manual` y `detail` igual al motivo proporcionado
5. IF el tipo de ajuste es `subtract` y el balance actual del cliente es menor a los puntos a restar, THEN THE API_Tienda SHALL retornar HTTP 400 con código `INSUFFICIENT_BALANCE` y el balance actual
6. THE API_Tienda SHALL validar que la cantidad de puntos sea mayor a 0 y que el motivo tenga al menos 5 caracteres
7. WHEN un ajuste de puntos es exitoso, THE API_Tienda SHALL retornar el nuevo balance total del customer_reward

### Requisito 8: Gestión de Cupones

**Historia de Usuario:** Como Administrador_Tienda, quiero crear, editar y eliminar cupones de descuento, para gestionar las promociones de la tienda.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede a la sección de cupones, THE Panel_Tienda SHALL mostrar una lista de cupones con: código, monto, tipo de descuento, usos y estado
2. THE Panel_Tienda SHALL permitir crear un cupón con: código, monto, tipo de descuento (porcentaje o fijo), descripción, límite de uso, monto mínimo, monto máximo y uso individual
3. THE API_Tienda SHALL validar que el código del cupón sea único (case-insensitive), alfanumérico con guiones, y tenga entre 4 y 20 caracteres
4. IF el tipo de descuento es `percentage`, THEN THE API_Tienda SHALL validar que el monto sea menor o igual a 100
5. IF se proporciona `maximum_amount` y `minimum_amount`, THEN THE API_Tienda SHALL validar que `maximum_amount` sea mayor o igual a `minimum_amount`
6. THE API_Tienda SHALL almacenar el código del cupón en mayúsculas y establecer `usage_count` en 0 al crear
7. THE Panel_Tienda SHALL permitir editar y eliminar cupones existentes
8. IF el código del cupón ya existe en la base de datos, THEN THE API_Tienda SHALL retornar HTTP 422 con código `DUPLICATE_COUPON_CODE`

### Requisito 9: Gestión de Redenciones

**Historia de Usuario:** Como Administrador_Tienda, quiero gestionar las solicitudes de redención de puntos, para aprobar o rechazar los canjes de los clientes.

#### Criterios de Aceptación

1. WHEN el Administrador_Tienda accede a la sección de redenciones, THE Panel_Tienda SHALL mostrar una lista de solicitudes con: cliente, puntos, reward, estado y fecha
2. THE Panel_Tienda SHALL permitir filtrar redenciones por estado: pendiente, aprobada y rechazada
3. THE Panel_Tienda SHALL permitir al Administrador_Tienda aprobar o rechazar una solicitud de redención

### Requisito 10: Estructura del Módulo Frontend

**Historia de Usuario:** Como desarrollador, quiero que el módulo del panel de tienda siga las convenciones de arquitectura existentes, para mantener la consistencia del proyecto.

#### Criterios de Aceptación

1. THE Panel_Tienda SHALL implementarse como un módulo Angular lazy-loaded registrado en la ruta `/admin/store` dentro de `admin-routing.module.ts`
2. THE Panel_Tienda SHALL seguir la estructura de rutas hijas: dashboard, orders, orders/:uuid, shipping, customers, customers/:uuid, points, coupons y redemptions
3. THE Panel_Tienda SHALL utilizar el estilo visual de VECSA: fondo blanco, color primario `#1c69d4`, tarjetas con `border-radius: 16px` y Material Icons
4. THE Panel_Tienda SHALL utilizar UUIDs en lugar de IDs numéricos en todas las interacciones con la API

### Requisito 11: Endpoints y Convenciones Backend

**Historia de Usuario:** Como desarrollador, quiero que los endpoints del panel sigan las convenciones existentes del proyecto, para mantener la consistencia de la API.

#### Criterios de Aceptación

1. THE API_Tienda SHALL definir todos los endpoints como rutas POST agrupadas bajo el prefijo `store-management/`
2. THE API_Tienda SHALL utilizar el helper `ApiResponseHelper` para formatear todas las respuestas
3. THE API_Tienda SHALL reutilizar los modelos existentes: `BoutiqueOrder`, `BoutiqueShipment`, `Customer`, `CustomerReward`, `RewardPoint` y `CustomerCoupon`
4. IF un recurso solicitado por UUID no existe, THEN THE API_Tienda SHALL retornar HTTP 404 con un código de error descriptivo
5. IF el token de autenticación es inválido o ha expirado, THEN THE API_Tienda SHALL retornar HTTP 401 mediante el middleware `auth:sanctum`
