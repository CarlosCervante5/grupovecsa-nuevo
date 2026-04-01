# Documento de Requisitos — Secciones del Perfil de Cliente

## Introducción

Mejora de la página de perfil del cliente en `/auth/mi-cuenta` para incluir navegación por pestañas con cuatro secciones: Perfil (información existente de rewards y datos personales), Pedidos (historial de compras en la Boutique), Citas (citas agendadas del cliente) y Cotizaciones (valuaciones de vehículos). Cada sección consume endpoints existentes del backend Laravel protegidos con Sanctum y presenta los datos en un diseño consistente con el sistema de diseño actual (tarjetas blancas, border-radius 16px, color primario #1c69d4, Material Icons). La interfaz se muestra en español.

## Glosario

- **Página_Cuenta**: Página del perfil del cliente ubicada en la ruta `/auth/mi-cuenta`, implementada como `ProfileComponent` dentro del módulo `AccountModule`
- **Navegación_Tabs**: Barra de pestañas horizontal que permite al Cliente alternar entre las secciones Perfil, Pedidos, Citas y Cotizaciones
- **Sección_Perfil**: Pestaña que muestra la información existente del cliente: datos personales, puntos Rewards, gráfica de puntos, tabla de posiciones y eventos
- **Sección_Pedidos**: Pestaña que muestra el historial de pedidos de la Boutique del Cliente
- **Sección_Citas**: Pestaña que muestra las citas agendadas del Cliente
- **Sección_Cotizaciones**: Pestaña que muestra las valuaciones/cotizaciones de vehículos del Cliente
- **Cliente**: Usuario autenticado con rol `client` que accede a la Página_Cuenta
- **Pedido**: Registro de compra en la Boutique con número de pedido, estado, fecha y total (modelo `BoutiqueOrder`)
- **Cita**: Registro de cita agendada con fecha, tipo, sucursal y estado (modelo `CustomerAppointment`)
- **Cotización**: Valuación de un vehículo del cliente con información del vehículo, estado y fecha (modelo `VehicleValuation`)
- **API_Pedidos**: Endpoint `POST /api/boutique/orders/search` que retorna los pedidos del Cliente autenticado
- **API_Pedido_Detalle**: Endpoint `POST /api/boutique/orders/detail` que retorna el detalle de un pedido por UUID
- **API_Citas**: Endpoint `POST /api/appointment/search` que retorna las citas filtradas por tipo y keyword
- **API_Cotizaciones**: Endpoint `GET /api/valuations/search` que retorna las valuaciones del usuario autenticado
- **Estado_Carga**: Indicador visual (spinner) que se muestra mientras se obtienen datos de la API
- **Estado_Vacío**: Mensaje informativo que se muestra cuando una sección no tiene registros

## Requisitos

### Requisito 1: Navegación por Pestañas en la Página de Cuenta

**Historia de Usuario:** Como Cliente, quiero navegar entre las secciones de mi cuenta mediante pestañas, para acceder fácilmente a mi información de perfil, pedidos, citas y cotizaciones.

#### Criterios de Aceptación

1. THE Página_Cuenta SHALL mostrar una Navegación_Tabs con cuatro pestañas: "Perfil", "Pedidos", "Citas" y "Cotizaciones"
2. WHEN el Cliente accede a `/auth/mi-cuenta`, THE Navegación_Tabs SHALL seleccionar la pestaña "Perfil" por defecto
3. WHEN el Cliente selecciona una pestaña, THE Página_Cuenta SHALL mostrar el contenido de la sección correspondiente y ocultar las demás secciones
4. THE Navegación_Tabs SHALL resaltar visualmente la pestaña activa con el color primario #1c69d4 y un indicador inferior
5. THE Navegación_Tabs SHALL posicionarse debajo del hero existente y encima del contenido de cada sección
6. THE Navegación_Tabs SHALL ser responsive: en pantallas menores a 480px las pestañas SHALL ocupar el ancho completo con scroll horizontal si es necesario

### Requisito 2: Sección de Perfil (Pestaña Existente)

**Historia de Usuario:** Como Cliente, quiero que mi información de perfil, puntos y rewards se mantenga accesible en la primera pestaña, para seguir consultando mis datos personales y posición en el programa de lealtad.

#### Criterios de Aceptación

1. WHEN el Cliente selecciona la pestaña "Perfil", THE Sección_Perfil SHALL mostrar el contenido existente: tarjeta de usuario, puntos del mes, puntos acumulados, posición, gráfica de puntos, tabla de posiciones y eventos próximos
2. THE Sección_Perfil SHALL mantener la funcionalidad existente de editar perfil, canjear puntos y ver cupones
3. THE Sección_Perfil SHALL conservar el diseño visual actual sin modificaciones

### Requisito 3: Sección de Pedidos de la Boutique

**Historia de Usuario:** Como Cliente, quiero ver el historial de mis pedidos de la Boutique, para dar seguimiento al estado de mis compras.

#### Criterios de Aceptación

1. WHEN el Cliente selecciona la pestaña "Pedidos", THE Sección_Pedidos SHALL consultar la API_Pedidos para obtener la lista de pedidos del Cliente
2. WHILE la Sección_Pedidos obtiene datos de la API_Pedidos, THE Sección_Pedidos SHALL mostrar un Estado_Carga con un spinner y el texto "Cargando pedidos..."
3. THE Sección_Pedidos SHALL mostrar cada Pedido en una tarjeta blanca con border-radius 16px que contenga: número de pedido, fecha de creación, estado, cantidad de artículos y total
4. THE Sección_Pedidos SHALL mostrar el estado de cada Pedido mediante un badge de color según el estado: "pendiente" (gris), "pagado" (azul), "en_preparacion" (amarillo), "enviado" (naranja), "entregado" (verde), "cancelado" (rojo)
5. WHEN el Cliente selecciona un Pedido de la lista, THE Sección_Pedidos SHALL consultar la API_Pedido_Detalle y mostrar el detalle del pedido: artículos con nombre y cantidad, información de envío, estado de pago y estado de envío
6. IF la API_Pedidos retorna una lista vacía, THEN THE Sección_Pedidos SHALL mostrar un Estado_Vacío con un ícono ilustrativo y el texto "Aún no tienes pedidos en la Boutique"
7. IF la API_Pedidos retorna un error, THEN THE Sección_Pedidos SHALL mostrar un mensaje de error con el texto "Error al cargar los pedidos. Intenta de nuevo." y un botón para reintentar
8. THE Sección_Pedidos SHALL ordenar los pedidos por fecha de creación descendente

### Requisito 4: Sección de Citas

**Historia de Usuario:** Como Cliente, quiero ver la lista de mis citas agendadas, para consultar las fechas, tipos y sucursales de mis próximas visitas.

#### Criterios de Aceptación

1. WHEN el Cliente selecciona la pestaña "Citas", THE Sección_Citas SHALL consultar la API_Citas para obtener las citas del Cliente
2. WHILE la Sección_Citas obtiene datos de la API_Citas, THE Sección_Citas SHALL mostrar un Estado_Carga con un spinner y el texto "Cargando citas..."
3. THE Sección_Citas SHALL mostrar cada Cita en una tarjeta blanca con border-radius 16px que contenga: fecha programada, tipo de cita, nombre de la sucursal y datos del vehículo (marca y modelo)
4. THE Sección_Citas SHALL mostrar el tipo de cita con un ícono de Material Icons correspondiente (ej. "event" para cita general, "directions_car" para valuación)
5. IF la API_Citas retorna una lista vacía, THEN THE Sección_Citas SHALL mostrar un Estado_Vacío con un ícono ilustrativo y el texto "No tienes citas agendadas"
6. IF la API_Citas retorna un error, THEN THE Sección_Citas SHALL mostrar un mensaje de error con el texto "Error al cargar las citas. Intenta de nuevo." y un botón para reintentar
7. THE Sección_Citas SHALL ordenar las citas por fecha programada descendente

### Requisito 5: Sección de Cotizaciones

**Historia de Usuario:** Como Cliente, quiero ver la lista de mis cotizaciones/valuaciones de vehículos, para consultar el estado y los detalles de cada valuación solicitada.

#### Criterios de Aceptación

1. WHEN el Cliente selecciona la pestaña "Cotizaciones", THE Sección_Cotizaciones SHALL consultar la API_Cotizaciones para obtener las valuaciones del Cliente
2. WHILE la Sección_Cotizaciones obtiene datos de la API_Cotizaciones, THE Sección_Cotizaciones SHALL mostrar un Estado_Carga con un spinner y el texto "Cargando cotizaciones..."
3. THE Sección_Cotizaciones SHALL mostrar cada Cotización en una tarjeta blanca con border-radius 16px que contenga: marca y modelo del vehículo, año, kilometraje, estado de la valuación y fecha de creación
4. THE Sección_Cotizaciones SHALL mostrar el estado de cada Cotización mediante un badge de color según el estado
5. IF la API_Cotizaciones retorna una lista vacía, THEN THE Sección_Cotizaciones SHALL mostrar un Estado_Vacío con un ícono ilustrativo y el texto "No tienes cotizaciones registradas"
6. IF la API_Cotizaciones retorna un error, THEN THE Sección_Cotizaciones SHALL mostrar un mensaje de error con el texto "Error al cargar las cotizaciones. Intenta de nuevo." y un botón para reintentar
7. THE Sección_Cotizaciones SHALL ordenar las cotizaciones por fecha de creación descendente

### Requisito 6: Endpoints del Backend para el Cliente

**Historia de Usuario:** Como Cliente, quiero que los endpoints existentes filtren correctamente mis datos, para ver únicamente mis propios pedidos, citas y cotizaciones.

#### Criterios de Aceptación

1. THE API_Pedidos SHALL filtrar los pedidos por el `user_id` del Cliente autenticado mediante Sanctum
2. THE API_Citas SHALL aceptar un parámetro `customer_uuid` para filtrar las citas del Cliente autenticado
3. THE API_Cotizaciones SHALL filtrar las valuaciones asociadas al usuario autenticado mediante la relación `valuations()` del modelo User
4. IF un Cliente no autenticado intenta acceder a la API_Pedidos, API_Citas o API_Cotizaciones, THEN THE backend SHALL retornar un error 401 con el mensaje "Unauthenticated"
5. THE API_Pedido_Detalle SHALL verificar que el Pedido solicitado pertenezca al Cliente autenticado antes de retornar los datos

### Requisito 7: Estados de Carga y Vacío Consistentes

**Historia de Usuario:** Como Cliente, quiero ver indicadores claros cuando los datos están cargando o cuando no hay registros, para entender el estado actual de cada sección.

#### Criterios de Aceptación

1. THE Estado_Carga SHALL mostrar un spinner circular animado con el color primario #1c69d4 y un texto descriptivo debajo
2. THE Estado_Vacío SHALL mostrar un ícono de Material Icons en color gris claro (#94a3b8), un texto principal descriptivo y un texto secundario con sugerencia de acción
3. THE Estado_Carga SHALL centrarse vertical y horizontalmente dentro del área de contenido de la pestaña activa
4. THE Estado_Vacío SHALL centrarse vertical y horizontalmente dentro del área de contenido de la pestaña activa
5. WHEN el Cliente cambia de pestaña, THE Página_Cuenta SHALL mostrar el Estado_Carga de la nueva sección únicamente si los datos de esa sección no han sido cargados previamente

### Requisito 8: Diseño Responsive y Consistencia Visual

**Historia de Usuario:** Como Cliente, quiero que las secciones de mi cuenta se vean correctamente en dispositivos móviles y de escritorio, para consultar mi información desde cualquier dispositivo.

#### Criterios de Aceptación

1. THE Página_Cuenta SHALL adaptar el layout de las tarjetas de cada sección a una columna en pantallas menores a 768px
2. THE Página_Cuenta SHALL utilizar el sistema de diseño existente: tarjetas blancas con border-radius 16px, sombra `0 2px 12px rgba(0,0,0,0.05)` y padding consistente
3. THE Página_Cuenta SHALL utilizar la tipografía y colores existentes del proyecto: color primario #1c69d4, texto principal #0f172a, texto secundario #64748b
4. THE Navegación_Tabs SHALL mantener un ancho máximo de 1100px centrado horizontalmente, consistente con el contenedor existente `.profile-container`
