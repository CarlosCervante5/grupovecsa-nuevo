# Documento de Requisitos — Boutique E-commerce

## Introducción

Sistema de comercio electrónico completo para la sección "Boutique" de la plataforma Grupo VECSA. Permite la venta de mercancía, accesorios y productos de estilo de vida BMW. Incluye catálogo de productos, carrito de compras, proceso de checkout, gestión de pedidos, gestión de inventario y panel de administración. Se integra con el sistema existente de perfiles, roles y autenticación Sanctum.

## Glosario

- **Sistema_Boutique**: Módulo de e-commerce dentro de la plataforma Grupo VECSA
- **Catálogo**: Colección de productos organizados por categorías disponibles para la venta
- **Producto**: Artículo de mercancía, accesorio o producto lifestyle BMW disponible en la Boutique
- **Categoría**: Agrupación lógica de productos (ej. Accesorios, Ropa, Lifestyle, Refacciones)
- **Carrito**: Colección temporal de productos seleccionados por un Cliente antes de realizar la compra
- **Pedido**: Registro de una compra confirmada que contiene uno o más productos con información de envío y pago
- **Artículo_Pedido**: Línea individual dentro de un Pedido que representa un Producto con cantidad y precio
- **Cliente**: Usuario autenticado con rol "client" que realiza compras en la Boutique
- **Administrador**: Usuario autenticado con rol "administrator" o "staff" que gestiona productos, categorías y pedidos
- **Panel_Admin**: Interfaz de administración para gestionar el Catálogo, inventario y Pedidos
- **Pago**: Registro del procesamiento de cobro asociado a un Pedido
- **Envío**: Información de dirección y método de entrega asociada a un Pedido
- **Inventario**: Cantidad disponible de cada Producto para la venta
- **Imagen_Producto**: Archivo de imagen asociado a un Producto, almacenado en Cloudinary mediante Jobs en cola
- **API_Boutique**: Conjunto de endpoints POST del backend Laravel que sirven datos al frontend Angular
- **Stripe**: Plataforma de procesamiento de pagos en línea con tarjeta de crédito/débito
- **Envia_com**: Servicio de logística (envia.com) para cotización, generación de guías y rastreo de envíos
- **Tidio**: Widget de chat en vivo para atención al cliente en tiempo real
- **Brevo**: Plataforma de email marketing y transaccional (antes Sendinblue) para notificaciones y campañas

## Requisitos

### Requisito 1: Gestión de Categorías de Productos

**Historia de Usuario:** Como Administrador, quiero crear y gestionar categorías de productos, para organizar el Catálogo de la Boutique de forma estructurada.

#### Criterios de Aceptación

1. THE Panel_Admin SHALL permitir al Administrador crear una Categoría con nombre, descripción y estado activo/inactivo
2. THE Panel_Admin SHALL permitir al Administrador editar el nombre, descripción y estado de una Categoría existente
3. THE Panel_Admin SHALL permitir al Administrador eliminar una Categoría mediante soft delete
4. WHEN el Administrador solicita la lista de categorías, THE API_Boutique SHALL retornar todas las Categorías ordenadas por nombre
5. THE Sistema_Boutique SHALL asignar un UUID a cada Categoría al momento de su creación
6. THE Sistema_Boutique SHALL almacenar las Categorías en una tabla con prefijo `app_vecsa_`
7. IF una Categoría contiene Productos asociados, THEN THE Sistema_Boutique SHALL impedir la eliminación de la Categoría y retornar un mensaje de error descriptivo

### Requisito 2: Gestión de Productos (CRUD)

**Historia de Usuario:** Como Administrador, quiero crear, editar y eliminar productos de la Boutique, para mantener el Catálogo actualizado con la mercancía disponible.

#### Criterios de Aceptación

1. THE Panel_Admin SHALL permitir al Administrador crear un Producto con: nombre, descripción, precio, SKU, Categoría, cantidad en Inventario y estado activo/inactivo
2. THE Panel_Admin SHALL permitir al Administrador editar todos los campos de un Producto existente
3. THE Panel_Admin SHALL permitir al Administrador eliminar un Producto mediante soft delete
4. WHEN el Administrador solicita la lista de productos, THE API_Boutique SHALL retornar los Productos con paginación y filtros por Categoría, estado y término de búsqueda
5. THE Sistema_Boutique SHALL asignar un UUID a cada Producto al momento de su creación
6. THE Sistema_Boutique SHALL almacenar los Productos en una tabla con prefijo `app_vecsa_`
7. THE Sistema_Boutique SHALL validar que el SKU sea único entre todos los Productos activos

### Requisito 3: Gestión de Imágenes de Productos

**Historia de Usuario:** Como Administrador, quiero subir y gestionar imágenes de los productos, para que los Clientes puedan ver los artículos antes de comprarlos.

#### Criterios de Aceptación

1. WHEN el Administrador sube una imagen para un Producto, THE Sistema_Boutique SHALL almacenar el archivo temporalmente y despachar un Job en cola para subirlo a Cloudinary
2. THE Sistema_Boutique SHALL permitir asociar múltiples imágenes a un Producto
3. THE Panel_Admin SHALL permitir al Administrador reordenar las imágenes de un Producto mediante un campo sort_id
4. THE Panel_Admin SHALL permitir al Administrador eliminar una Imagen_Producto individual
5. THE Sistema_Boutique SHALL almacenar la URL de Cloudinary y el public_id de cada Imagen_Producto en la base de datos
6. WHEN el Job de subida a Cloudinary falla, THE Sistema_Boutique SHALL registrar el error en el log y mantener el registro de la Imagen_Producto con estado pendiente

### Requisito 4: Catálogo Público de Productos

**Historia de Usuario:** Como Cliente, quiero navegar el catálogo de productos de la Boutique, para descubrir y seleccionar artículos que deseo comprar.

#### Criterios de Aceptación

1. WHEN un Cliente accede a la ruta /boutique, THE Sistema_Boutique SHALL mostrar el Catálogo con los Productos activos organizados por Categoría
2. THE Sistema_Boutique SHALL permitir al Cliente filtrar Productos por Categoría, rango de precio y término de búsqueda
3. THE Sistema_Boutique SHALL mostrar para cada Producto: imagen principal, nombre, precio y disponibilidad en Inventario
4. WHEN un Cliente selecciona un Producto del Catálogo, THE Sistema_Boutique SHALL navegar a la vista de detalle del Producto
5. THE Sistema_Boutique SHALL implementar paginación en el Catálogo para cargar los Productos de forma eficiente
6. THE Sistema_Boutique SHALL cargar el módulo de la Boutique de forma lazy-loaded en el frontend Angular

### Requisito 5: Vista de Detalle de Producto

**Historia de Usuario:** Como Cliente, quiero ver la información completa de un producto, para tomar una decisión de compra informada.

#### Criterios de Aceptación

1. WHEN un Cliente accede al detalle de un Producto, THE Sistema_Boutique SHALL mostrar: nombre, descripción completa, precio, galería de imágenes, disponibilidad en Inventario y Categoría
2. THE Sistema_Boutique SHALL permitir al Cliente navegar entre las imágenes del Producto en una galería
3. THE Sistema_Boutique SHALL mostrar un botón para agregar el Producto al Carrito
4. IF el Producto tiene Inventario igual a cero, THEN THE Sistema_Boutique SHALL deshabilitar el botón de agregar al Carrito y mostrar el texto "Agotado"
5. THE Sistema_Boutique SHALL mostrar productos relacionados de la misma Categoría en la vista de detalle

### Requisito 6: Gestión del Carrito de Compras

**Historia de Usuario:** Como Cliente, quiero agregar productos a un carrito de compras, para acumular artículos antes de realizar mi pedido.

#### Criterios de Aceptación

1. WHEN un Cliente autenticado agrega un Producto al Carrito, THE Sistema_Boutique SHALL crear o actualizar el registro del Carrito en la base de datos asociado al Cliente
2. THE Sistema_Boutique SHALL permitir al Cliente modificar la cantidad de cada Producto en el Carrito
3. THE Sistema_Boutique SHALL permitir al Cliente eliminar un Producto del Carrito
4. WHEN un Cliente consulta el Carrito, THE Sistema_Boutique SHALL mostrar: lista de productos, cantidad de cada uno, precio unitario, subtotal por línea y total general
5. IF un Cliente intenta agregar una cantidad mayor al Inventario disponible de un Producto, THEN THE Sistema_Boutique SHALL limitar la cantidad al máximo disponible y notificar al Cliente
6. THE Sistema_Boutique SHALL persistir el Carrito en la base de datos para que el Cliente lo recupere en sesiones posteriores
7. THE Sistema_Boutique SHALL asignar un UUID a cada registro del Carrito al momento de su creación


### Requisito 7: Proceso de Checkout

**Historia de Usuario:** Como Cliente, quiero completar la compra de los productos en mi carrito, para recibir los artículos que seleccioné.

#### Criterios de Aceptación

1. WHEN un Cliente inicia el checkout, THE Sistema_Boutique SHALL solicitar la información de Envío: nombre completo, dirección, ciudad, estado, código postal y teléfono de contacto
2. THE Sistema_Boutique SHALL permitir al Cliente revisar el resumen del Pedido antes de confirmar: productos, cantidades, precios, costo de envío y total
3. WHEN un Cliente confirma el Pedido, THE Sistema_Boutique SHALL crear el registro del Pedido con estado "pendiente" y los Artículos_Pedido correspondientes
4. WHEN un Pedido es confirmado, THE Sistema_Boutique SHALL reducir el Inventario de cada Producto según la cantidad comprada
5. IF el Inventario de un Producto es insuficiente al momento de confirmar el Pedido, THEN THE Sistema_Boutique SHALL rechazar el Pedido y notificar al Cliente sobre los productos sin disponibilidad
6. WHEN un Pedido es creado, THE Sistema_Boutique SHALL vaciar el Carrito del Cliente
7. THE Sistema_Boutique SHALL asignar un UUID y un número de pedido legible a cada Pedido al momento de su creación

### Requisito 8: Gestión de Pagos

**Historia de Usuario:** Como Cliente, quiero pagar mi pedido de forma segura con tarjeta o transferencia, para completar la transacción de compra.

#### Criterios de Aceptación

1. WHEN un Cliente confirma un Pedido, THE Sistema_Boutique SHALL crear un registro de Pago asociado al Pedido con estado "pendiente"
2. THE Sistema_Boutique SHALL soportar los métodos de pago: tarjeta de crédito/débito vía Stripe, transferencia bancaria y pago en sucursal
3. WHEN el Cliente selecciona pago con tarjeta, THE Sistema_Boutique SHALL crear un Stripe PaymentIntent en el backend y mostrar el formulario de pago de Stripe Elements en el frontend
4. WHEN Stripe confirma el pago exitoso mediante webhook, THE Sistema_Boutique SHALL actualizar el estado del Pago a "completado" y el estado del Pedido a "pagado"
5. IF el pago con Stripe falla, THEN THE Sistema_Boutique SHALL mantener el Pedido en estado "pendiente" y notificar al Cliente del error
6. WHEN el Administrador marca un Pago manual (transferencia/sucursal) como recibido, THE Sistema_Boutique SHALL actualizar el estado del Pago a "completado" y el estado del Pedido a "pagado"
7. IF un Pago manual no es recibido dentro de 72 horas, THEN THE Sistema_Boutique SHALL marcar el Pedido como "cancelado" y restaurar el Inventario de los Productos
8. THE Sistema_Boutique SHALL almacenar en el registro de Pago: método de pago, monto, referencia de transacción (stripe_payment_intent_id para Stripe), y fecha de confirmación
9. THE Sistema_Boutique SHALL asignar un UUID a cada registro de Pago al momento de su creación

### Requisito 9: Gestión de Envíos con Envia.com

**Historia de Usuario:** Como Cliente, quiero que mis productos sean enviados de forma confiable con seguimiento, para recibir mis compras en la dirección correcta.

#### Criterios de Aceptación

1. THE Sistema_Boutique SHALL almacenar la información de Envío asociada a cada Pedido: nombre del destinatario, dirección completa, ciudad, estado, código postal y teléfono
2. THE Sistema_Boutique SHALL soportar los métodos de entrega: envío a domicilio vía Envia.com y recolección en sucursal
3. WHEN el Cliente selecciona envío a domicilio durante el checkout, THE Sistema_Boutique SHALL consultar la API de Envia.com para obtener cotizaciones de envío con diferentes paqueterías y tiempos de entrega
4. THE Sistema_Boutique SHALL mostrar al Cliente las opciones de envío con precio y tiempo estimado de entrega para que seleccione su preferencia
5. WHEN el Administrador marca un Pedido como "en_preparacion", THE Sistema_Boutique SHALL generar la guía de envío mediante la API de Envia.com y almacenar el número de rastreo
6. THE Sistema_Boutique SHALL permitir al Cliente consultar el estado de rastreo de su envío mediante el número de guía de Envia.com
7. WHEN el Administrador actualiza el estado de Envío, THE Sistema_Boutique SHALL registrar el cambio con los estados: "pendiente", "en_preparacion", "enviado", "entregado"
8. WHEN el método de entrega es recolección en sucursal, THE Sistema_Boutique SHALL permitir al Cliente seleccionar una sucursal de la lista de Dealerships existentes
9. THE Sistema_Boutique SHALL asignar un UUID a cada registro de Envío al momento de su creación

### Requisito 10: Seguimiento de Pedidos por el Cliente

**Historia de Usuario:** Como Cliente, quiero ver el historial y estado de mis pedidos, para dar seguimiento a mis compras.

#### Criterios de Aceptación

1. WHEN un Cliente accede a la sección de pedidos, THE Sistema_Boutique SHALL mostrar la lista de Pedidos del Cliente ordenados por fecha de creación descendente
2. THE Sistema_Boutique SHALL mostrar para cada Pedido: número de pedido, fecha, estado, total y cantidad de artículos
3. WHEN un Cliente selecciona un Pedido, THE Sistema_Boutique SHALL mostrar el detalle completo: Artículos_Pedido, información de Envío, estado de Pago y estado de Envío
4. THE Sistema_Boutique SHALL mostrar los estados del Pedido de forma visual con indicadores de progreso: pendiente → pagado → en_preparacion → enviado → entregado

### Requisito 11: Panel de Administración de Pedidos

**Historia de Usuario:** Como Administrador, quiero gestionar los pedidos de la Boutique, para procesar las ventas y dar seguimiento a las entregas.

#### Criterios de Aceptación

1. WHEN el Administrador accede al panel de pedidos, THE Panel_Admin SHALL mostrar la lista de todos los Pedidos con filtros por estado, fecha y término de búsqueda
2. THE Panel_Admin SHALL permitir al Administrador ver el detalle completo de un Pedido: Artículos_Pedido, datos del Cliente, información de Envío y estado de Pago
3. THE Panel_Admin SHALL permitir al Administrador actualizar el estado del Pedido: pendiente, pagado, en_preparacion, enviado, entregado, cancelado
4. WHEN el Administrador cancela un Pedido, THE Sistema_Boutique SHALL restaurar el Inventario de los Productos del Pedido cancelado
5. THE Panel_Admin SHALL mostrar un resumen con métricas: total de pedidos, pedidos pendientes, ingresos del período

### Requisito 12: Gestión de Inventario

**Historia de Usuario:** Como Administrador, quiero controlar el inventario de productos, para asegurar que la disponibilidad mostrada a los Clientes sea precisa.

#### Criterios de Aceptación

1. THE Panel_Admin SHALL mostrar la cantidad en Inventario de cada Producto en la lista de productos
2. THE Panel_Admin SHALL permitir al Administrador actualizar manualmente la cantidad en Inventario de un Producto
3. WHEN el Inventario de un Producto llega a cero, THE Sistema_Boutique SHALL marcar el Producto como "agotado" en el Catálogo
4. WHILE el Inventario de un Producto es menor o igual a 5 unidades, THE Panel_Admin SHALL mostrar una alerta visual de stock bajo
5. THE Sistema_Boutique SHALL registrar cada movimiento de Inventario con: cantidad anterior, cantidad nueva, motivo del cambio y fecha

### Requisito 13: Integración con Sistema de Autenticación Existente

**Historia de Usuario:** Como Cliente, quiero usar mi cuenta existente de Grupo VECSA para comprar en la Boutique, para no tener que crear una cuenta nueva.

#### Criterios de Aceptación

1. THE Sistema_Boutique SHALL utilizar la autenticación Sanctum existente para proteger los endpoints que requieren sesión de Cliente
2. THE Sistema_Boutique SHALL utilizar el sistema de roles existente (Spatie) para restringir el acceso al Panel_Admin a usuarios con rol "administrator" o "staff"
3. WHEN un Cliente no autenticado intenta agregar un Producto al Carrito, THE Sistema_Boutique SHALL redirigir al Cliente a la pantalla de inicio de sesión
4. THE Sistema_Boutique SHALL asociar los Pedidos al modelo User existente mediante la relación user_id
5. THE API_Boutique SHALL seguir el patrón de rutas POST existente del backend y utilizar el middleware bandwidth_usage

### Requisito 14: Convenciones de Base de Datos y Arquitectura

**Historia de Usuario:** Como desarrollador, quiero que el módulo Boutique siga las convenciones existentes del proyecto, para mantener la consistencia del código.

#### Criterios de Aceptación

1. THE Sistema_Boutique SHALL utilizar el prefijo `app_vecsa_` en todas las tablas de base de datos del módulo
2. THE Sistema_Boutique SHALL asignar UUID (v4) a todos los modelos mediante el trait de boot existente
3. THE Sistema_Boutique SHALL utilizar soft deletes en todos los modelos del módulo
4. THE Sistema_Boutique SHALL utilizar el helper ApiResponseHelper para formatear todas las respuestas de la API
5. THE Sistema_Boutique SHALL utilizar Jobs en cola para el procesamiento de imágenes con Cloudinary
6. THE API_Boutique SHALL definir todos los endpoints como rutas POST agrupadas bajo el prefijo "boutique"
7. THE Sistema_Boutique SHALL implementar el módulo frontend como un módulo Angular lazy-loaded en la ruta /boutique


### Requisito 15: Integración con Tidio (Chat en Vivo)

**Historia de Usuario:** Como Cliente, quiero comunicarme en tiempo real con un agente de atención al cliente mientras navego la Boutique, para resolver dudas sobre productos o pedidos de forma inmediata.

#### Criterios de Aceptación

1. THE Sistema_Boutique SHALL integrar el widget de Tidio en las páginas de la Boutique (catálogo, detalle de producto, carrito y checkout)
2. THE Sistema_Boutique SHALL cargar el script de Tidio de forma asíncrona para no afectar el rendimiento de la página
3. WHEN un Cliente autenticado abre el chat de Tidio, THE Sistema_Boutique SHALL enviar al widget los datos del Cliente (nombre y email) para identificarlo en el panel de Tidio
4. THE Sistema_Boutique SHALL permitir al Administrador configurar el ID del proyecto Tidio desde las variables de entorno del backend o la configuración del frontend
5. THE Sistema_Boutique SHALL mostrar el widget de Tidio únicamente en las rutas del módulo Boutique

### Requisito 16: Integración con Brevo (Email Transaccional y Marketing)

**Historia de Usuario:** Como Cliente, quiero recibir notificaciones por email sobre mis pedidos y promociones de la Boutique, para estar informado del estado de mis compras y novedades.

#### Criterios de Aceptación

1. WHEN un Pedido es creado, THE Sistema_Boutique SHALL enviar un email de confirmación de pedido al Cliente mediante la API transaccional de Brevo
2. WHEN el estado de un Pedido cambia (pagado, en_preparacion, enviado, entregado), THE Sistema_Boutique SHALL enviar un email de actualización de estado al Cliente mediante Brevo
3. THE Sistema_Boutique SHALL utilizar plantillas de email configuradas en Brevo para mantener consistencia visual con la marca Grupo VECSA
4. WHEN un Cliente realiza su primera compra, THE Sistema_Boutique SHALL agregar al Cliente a la lista de contactos de Brevo para campañas de marketing
5. THE Panel_Admin SHALL permitir al Administrador configurar las credenciales de la API de Brevo (API key) desde las variables de entorno del backend
6. IF el envío de email mediante Brevo falla, THEN THE Sistema_Boutique SHALL registrar el error en el log y encolar un reintento automático
