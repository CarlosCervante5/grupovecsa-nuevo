# Plan de Implementación: Boutique E-commerce

## Resumen

Implementación incremental del módulo de e-commerce "Boutique" para Grupo VECSA. Se inicia con la capa de datos (migraciones y modelos), luego controllers/rutas backend, después el frontend Angular (público y admin), y finalmente las integraciones externas (Stripe, Envia.com, Tidio, Brevo).

## Tareas

- [x] 1. Migraciones de base de datos
  - [x] 1.1 Crear migración para `boutique_categories`
    - Crear archivo de migración con prefijo `app_vecsa_` usando `env('DB_TABLE_PREFIX')`
    - Campos: id, uuid, name, description, active, timestamps, soft deletes
    - _Requisitos: 1.5, 1.6, 14.1, 14.2, 14.3_

  - [x] 1.2 Crear migración para `boutique_products`
    - Campos: id, uuid, category_id (FK → boutique_categories), name, description, price, sku, stock, active, timestamps, soft deletes
    - Índice unique en sku con condición de soft delete
    - _Requisitos: 2.5, 2.6, 2.7, 14.1_

  - [x] 1.3 Crear migración para `boutique_product_images`
    - Campos: id, uuid, product_id (FK), image_path, cloudinary_public_id, sort_id, status (pending/uploaded/failed), timestamps, soft deletes
    - _Requisitos: 3.5, 14.1_

  - [x] 1.4 Crear migración para `boutique_carts` y `boutique_cart_items`
    - `boutique_carts`: id, uuid, user_id (FK → users), timestamps, soft deletes
    - `boutique_cart_items`: id, uuid, cart_id (FK), product_id (FK), quantity, timestamps, soft deletes
    - _Requisitos: 6.6, 6.7, 14.1_

  - [x] 1.5 Crear migración para `boutique_orders` y `boutique_order_items`
    - `boutique_orders`: id, uuid, user_id (FK), order_number, status, subtotal, shipping_cost, total, delivery_method, campos de envío (shipping_name, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_phone), notes, timestamps, soft deletes
    - `boutique_order_items`: id, uuid, order_id (FK), product_id (FK), product_name, product_sku, quantity, unit_price, subtotal, timestamps, soft deletes
    - _Requisitos: 7.3, 7.7, 9.1, 14.1_

  - [x] 1.6 Crear migración para `boutique_payments`
    - Campos: id, uuid, order_id (FK), method, amount, status, stripe_payment_intent_id, transaction_reference, confirmed_at, timestamps, soft deletes
    - _Requisitos: 8.1, 8.8, 8.9, 14.1_

  - [x] 1.7 Crear migración para `boutique_shipments`
    - Campos: id, uuid, order_id (FK), delivery_method, carrier_name, tracking_number, envia_label_url, envia_shipment_id, dealership_id (FK nullable → dealerships), status, estimated_delivery, timestamps, soft deletes
    - _Requisitos: 9.1, 9.9, 14.1_

  - [x] 1.8 Crear migración para `boutique_inventory_movements`
    - Campos: id, uuid, product_id (FK), previous_stock, new_stock, quantity_change, reason, reference_type, reference_uuid, timestamps, soft deletes
    - _Requisitos: 12.5, 14.1_

- [x] 2. Modelos Eloquent
  - [x] 2.1 Crear modelo `BoutiqueCategory`
    - Tabla con prefijo dinámico, UUID en boot, SoftDeletes, fillable, hidden, casts, findByUuid
    - Relación: hasMany → BoutiqueProduct
    - _Requisitos: 1.5, 1.6, 14.1, 14.2, 14.3_

  - [x] 2.2 Crear modelo `BoutiqueProduct`
    - Relaciones: belongsTo → BoutiqueCategory, hasMany → BoutiqueProductImage, hasMany → BoutiqueCartItem, hasMany → BoutiqueOrderItem, hasMany → BoutiqueInventoryMovement
    - _Requisitos: 2.5, 2.6, 14.1, 14.2, 14.3_

  - [x] 2.3 Crear modelo `BoutiqueProductImage`
    - Relación: belongsTo → BoutiqueProduct
    - Status cast, sort_id ordering
    - _Requisitos: 3.5, 14.1_

  - [x] 2.4 Crear modelos `BoutiqueCart` y `BoutiqueCartItem`
    - BoutiqueCart: belongsTo → User, hasMany → BoutiqueCartItem
    - BoutiqueCartItem: belongsTo → BoutiqueCart, belongsTo → BoutiqueProduct
    - _Requisitos: 6.6, 6.7, 14.1_

  - [x] 2.5 Crear modelos `BoutiqueOrder` y `BoutiqueOrderItem`
    - BoutiqueOrder: belongsTo → User, hasMany → BoutiqueOrderItem, hasOne → BoutiquePayment, hasOne → BoutiqueShipment
    - BoutiqueOrderItem: belongsTo → BoutiqueOrder, belongsTo → BoutiqueProduct
    - _Requisitos: 7.7, 14.1, 14.2, 14.3_

  - [x] 2.6 Crear modelo `BoutiquePayment`
    - Relación: belongsTo → BoutiqueOrder
    - _Requisitos: 8.9, 14.1_

  - [x] 2.7 Crear modelo `BoutiqueShipment`
    - Relaciones: belongsTo → BoutiqueOrder, belongsTo → Dealership (nullable)
    - _Requisitos: 9.9, 14.1_

  - [x] 2.8 Crear modelo `BoutiqueInventoryMovement`
    - Relación: belongsTo → BoutiqueProduct
    - _Requisitos: 12.5, 14.1_

  - [ ]* 2.9 Escribir test de propiedad para UUID en modelos
    - **Propiedad 1: Asignación de UUID en todos los modelos**
    - **Valida: Requisitos 1.5, 2.5, 6.7, 7.7, 8.9, 9.9, 14.2**

  - [ ]* 2.10 Escribir test de propiedad para soft delete
    - **Propiedad 2: Soft delete oculta registros sin eliminarlos**
    - **Valida: Requisitos 1.3, 2.3, 14.3**

- [x] 3. Checkpoint — Verificar migraciones y modelos
  - Ejecutar migraciones, verificar que todas las tablas se crean correctamente. Asegurar que todos los tests pasan. Preguntar al usuario si hay dudas.

- [x] 4. Services backend
  - [x] 4.1 Crear `BoutiqueInventoryService`
    - Métodos: reduceStock, restoreStock, manualAdjust — todos crean BoutiqueInventoryMovement
    - Lógica de reducción/restauración de inventario con registro de movimientos
    - _Requisitos: 7.4, 7.5, 8.7, 11.4, 12.2, 12.5_

  - [x] 4.2 Crear `StripeService`
    - Métodos: createPaymentIntent, verifyWebhookSignature, processPaymentSucceeded
    - Usar STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET desde .env
    - _Requisitos: 8.3, 8.4, 8.5_

  - [x] 4.3 Crear `EnviacomService`
    - Métodos: getShippingQuotes, createShipment, trackShipment
    - Usar variables ENVIACOM_* desde .env
    - _Requisitos: 9.3, 9.5, 9.6_

  - [ ]* 4.4 Escribir test de propiedad para inventario
    - **Propiedad 28: Registro de movimientos de inventario**
    - **Valida: Requisitos 12.2, 12.5**

- [x] 5. FormRequests
  - [x] 5.1 Crear FormRequests para categorías y productos
    - `StoreBoutiqueCategoryRequest`, `DeleteBoutiqueCategoryRequest`
    - `StoreBoutiqueProductRequest`, `DeleteBoutiqueProductRequest`
    - Validación de SKU único, campos requeridos, tipos de datos
    - _Requisitos: 1.1, 2.1, 2.7_

  - [x] 5.2 Crear FormRequests para imágenes de producto
    - `StoreBoutiqueProductImageRequest`, `SortBoutiqueProductImageRequest`
    - Validación de archivo de imagen, product_uuid existente
    - _Requisitos: 3.1, 3.3_

  - [x] 5.3 Crear FormRequests para carrito
    - `AddToCartRequest`, `UpdateCartItemRequest`, `RemoveCartItemRequest`
    - Validación de product_uuid, quantity > 0
    - _Requisitos: 6.1, 6.2, 6.3_

  - [x] 5.4 Crear FormRequests para checkout, pedidos y pagos
    - `CreateBoutiqueOrderRequest`: datos de envío obligatorios, método de pago, método de entrega
    - `UpdateBoutiqueOrderStatusRequest`, `ConfirmManualPaymentRequest`
    - `UpdateInventoryRequest`, `ShippingQuoteRequest`
    - _Requisitos: 7.1, 7.2, 8.2, 9.3, 11.3, 12.2_

- [x] 6. Controllers y rutas backend — Admin (Categorías, Productos, Imágenes)
  - [x] 6.1 Crear `BoutiqueCategoryController` con métodos: search, store, update, delete
    - Validar que no se pueda eliminar categoría con productos asociados (error CATEGORY_HAS_PRODUCTS)
    - Usar ApiResponseHelper para todas las respuestas
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.7_

  - [x] 6.2 Crear `BoutiqueProductController` con métodos: search, store, update, delete
    - Paginación, filtros por categoría/estado/búsqueda
    - Validación de SKU único (error SKU_ALREADY_EXISTS)
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.7_

  - [x] 6.3 Crear `BoutiqueProductImageController` con métodos: store, sortUpdate, delete
    - Despachar Job `UploadBoutiqueProductImage` al subir imagen
    - _Requisitos: 3.1, 3.2, 3.3, 3.4_

  - [x] 6.4 Crear Job `UploadBoutiqueProductImage`
    - Subir a Cloudinary, copiar a S3, eliminar de Cloudinary, actualizar BD
    - Seguir patrón de `UploadHomeSlideImage` existente
    - Manejo de errores: reintentos, status pending/uploaded/failed
    - _Requisitos: 3.1, 3.5, 3.6, 14.5_

  - [ ]* 6.5 Escribir test de propiedad para categorías
    - **Propiedad 3: Round-trip de categorías**
    - **Valida: Requisitos 1.1, 1.2, 1.4**

  - [ ]* 6.6 Escribir test de propiedad para protección de eliminación de categoría
    - **Propiedad 4: Protección de eliminación de categoría con productos**
    - **Valida: Requisito 1.7**

  - [ ]* 6.7 Escribir test de propiedad para unicidad de SKU
    - **Propiedad 5: Unicidad de SKU entre productos activos**
    - **Valida: Requisito 2.7**

- [x] 7. Controllers y rutas backend — Catálogo público
  - [x] 7.1 Crear `BoutiqueCatalogController` con métodos: search, detail, categories
    - search: productos activos con paginación, filtros por categoría, rango de precio, búsqueda
    - detail: detalle completo con galería de imágenes y productos relacionados de la misma categoría
    - categories: lista de categorías activas ordenadas por nombre
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.4, 5.5_

  - [ ]* 7.2 Escribir test de propiedad para filtrado del catálogo
    - **Propiedad 6: Filtrado correcto del catálogo público**
    - **Valida: Requisitos 4.1, 4.2, 4.5, 2.4**

  - [ ]* 7.3 Escribir test de propiedad para productos agotados
    - **Propiedad 8: Productos agotados marcados correctamente**
    - **Valida: Requisitos 5.4, 12.3**

- [x] 8. Controllers y rutas backend — Carrito de compras
  - [x] 8.1 Crear `BoutiqueCartController` con métodos: get, add, update, remove
    - get: retornar carrito del usuario con items, precios y totales
    - add: crear/actualizar carrito, limitar cantidad al stock disponible
    - update: modificar cantidad de item
    - remove: eliminar item del carrito
    - _Requisitos: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ]* 8.2 Escribir test de propiedad para totales del carrito
    - **Propiedad 11: Invariante de totales del carrito**
    - **Valida: Requisito 6.4**

  - [ ]* 8.3 Escribir test de propiedad para límite de stock en carrito
    - **Propiedad 12: Cantidad en carrito limitada al stock disponible**
    - **Valida: Requisito 6.5**

- [x] 9. Controllers y rutas backend — Checkout y Pedidos
  - [x] 9.1 Crear `BoutiqueCheckoutController` con métodos: shippingQuote, createOrder, createPaymentIntent
    - shippingQuote: consultar EnviacomService para cotizaciones
    - createOrder: verificar stock, crear Order + OrderItems + Payment + Shipment, reducir inventario, vaciar carrito, generar order_number (BOUT-YYYYMMDD-XXXX)
    - createPaymentIntent: crear Stripe PaymentIntent y retornar client_secret
    - _Requisitos: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7, 8.1, 8.3, 9.3, 9.4_

  - [x] 9.2 Crear `BoutiqueOrderController` con métodos: search, detail
    - search: pedidos del cliente autenticado ordenados por fecha desc
    - detail: detalle completo con order_items, envío, pago
    - _Requisitos: 10.1, 10.2, 10.3, 10.4_

  - [x] 9.3 Crear `BoutiqueShippingController` con método: track
    - Consultar rastreo vía EnviacomService
    - _Requisito: 9.6_

  - [ ]* 9.4 Escribir test de propiedad para invariantes de creación de pedido
    - **Propiedad 14: Invariantes de creación de pedido**
    - **Valida: Requisitos 7.3, 7.4, 7.6, 7.7, 8.1**

  - [ ]* 9.5 Escribir test de propiedad para rechazo por stock insuficiente
    - **Propiedad 15: Rechazo de pedido por stock insuficiente**
    - **Valida: Requisito 7.5**

- [x] 10. Controllers y rutas backend — Pagos y Admin de Pedidos
  - [x] 10.1 Crear `BoutiquePaymentController` con métodos: stripeWebhook, confirmManual
    - stripeWebhook: verificar signature, procesar payment_intent.succeeded → Payment completado, Order pagado
    - confirmManual: admin confirma pago transferencia/sucursal → Payment completado, Order pagado
    - _Requisitos: 8.4, 8.5, 8.6_

  - [x] 10.2 Crear `BoutiqueAdminOrderController` con métodos: search, detail, updateStatus, generateLabel, metrics
    - updateStatus: transiciones válidas de estado, cancelación restaura inventario
    - generateLabel: generar guía vía EnviacomService, actualizar shipment
    - metrics: total pedidos, pendientes, ingresos del período
    - _Requisitos: 11.1, 11.2, 11.3, 11.4, 11.5, 9.5, 9.7_

  - [x] 10.3 Crear `BoutiqueInventoryController` con métodos: update, movements
    - update: ajuste manual de stock con registro de movimiento
    - movements: historial de movimientos de un producto
    - _Requisitos: 12.1, 12.2, 12.4, 12.5_

  - [x] 10.4 Crear Job `CancelUnpaidBoutiqueOrders`
    - Scheduled job: cancelar pedidos con pago manual pendiente >72h
    - Restaurar inventario de productos del pedido cancelado
    - Registrar en Kernel schedule (cada hora)
    - _Requisitos: 8.7, 11.4_

  - [ ]* 10.5 Escribir test de propiedad para webhook de Stripe
    - **Propiedad 17: Transición de estado por webhook de Stripe**
    - **Valida: Requisito 8.4**

  - [ ]* 10.6 Escribir test de propiedad para cancelación restaura inventario
    - **Propiedad 19: Cancelación de pedidos restaura inventario**
    - **Valida: Requisitos 8.7, 11.4**

  - [ ]* 10.7 Escribir test de propiedad para máquina de estados
    - **Propiedad 23: Máquina de estados válida para pedidos y envíos**
    - **Valida: Requisitos 9.7, 11.3**

- [x] 11. Registrar rutas API en `routes/api.php`
  - Agregar todas las rutas POST del módulo boutique agrupadas bajo prefijo `boutique`
  - Rutas públicas con middleware `bandwidth_usage`
  - Rutas cliente con middleware `auth:sanctum`
  - Rutas admin con middleware `auth:sanctum` + role `administrator|staff`
  - Ruta webhook Stripe sin auth (verificación por signature)
  - _Requisitos: 13.1, 13.2, 13.5, 14.6_

- [x] 12. Checkpoint — Verificar backend completo
  - Asegurar que todos los endpoints responden correctamente. Ejecutar todos los tests. Preguntar al usuario si hay dudas.

- [x] 13. Frontend Angular — Módulo Boutique público
  - [x] 13.1 Crear módulo `BoutiqueModule` con lazy-loading en ruta `/boutique`
    - Crear `boutique.module.ts`, `boutique-routing.module.ts`
    - Registrar ruta lazy-loaded en `app-routing.module.ts`
    - Crear interfaces en `boutique/interfaces/boutique.interfaces.ts`
    - Crear guard `boutique-auth.guard.ts` para rutas que requieren autenticación
    - _Requisitos: 4.6, 14.7_

  - [x] 13.2 Crear servicios del módulo Boutique
    - `boutique-catalog.service.ts`: search, detail, categories
    - `boutique-cart.service.ts`: get, add, update, remove
    - `boutique-checkout.service.ts`: shippingQuote, createOrder, createPaymentIntent
    - `boutique-order.service.ts`: search, detail
    - `boutique-shipping.service.ts`: track
    - _Requisitos: 4.1, 6.1, 7.1, 10.1, 9.6_

  - [x] 13.3 Crear `CatalogComponent` — página de catálogo
    - Grid de productos con `ProductCardComponent`
    - Filtros por categoría, rango de precio, búsqueda
    - Paginación
    - Angular Material para UI (cards, chips, inputs, paginator)
    - _Requisitos: 4.1, 4.2, 4.3, 4.5_

  - [x] 13.4 Crear `ProductDetailComponent` — vista de detalle de producto
    - Galería de imágenes navegable
    - Info completa: nombre, descripción, precio, disponibilidad, categoría
    - Botón agregar al carrito (deshabilitado si agotado con texto "Agotado")
    - Sección de productos relacionados de la misma categoría
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5_

  - [x] 13.5 Crear `CartComponent` — página del carrito
    - Lista de productos con cantidad editable, precio unitario, subtotal por línea
    - Total general
    - Botón para proceder al checkout
    - Eliminar items del carrito
    - _Requisitos: 6.2, 6.3, 6.4_

  - [x] 13.6 Crear `CheckoutComponent` — proceso de checkout
    - Formulario de datos de envío (nombre, dirección, ciudad, estado, CP, teléfono)
    - Selección de método de entrega (envío a domicilio / recolección en sucursal)
    - Opciones de envío con precio y tiempo estimado (vía Envia.com)
    - Selección de sucursal si es recolección (lista de Dealerships)
    - Resumen del pedido antes de confirmar
    - Selección de método de pago (tarjeta, transferencia, sucursal)
    - _Requisitos: 7.1, 7.2, 8.2, 9.2, 9.3, 9.4, 9.8_

  - [x] 13.7 Crear `StripePaymentComponent` — formulario de pago con Stripe Elements
    - Integrar Stripe Elements para captura de tarjeta
    - Confirmar pago con client_secret del PaymentIntent
    - Manejo de errores de pago
    - _Requisitos: 8.3, 8.5_

  - [x] 13.8 Crear `OrderHistoryComponent` y `OrderDetailComponent`
    - Lista de pedidos del cliente con número, fecha, estado, total
    - Detalle de pedido con items, envío, pago, estado
    - Indicador visual de progreso de estados (pendiente → pagado → en_preparacion → enviado → entregado)
    - Botón de rastreo de envío
    - _Requisitos: 10.1, 10.2, 10.3, 10.4, 9.6_

- [x] 14. Checkpoint — Verificar frontend público
  - Asegurar que la navegación del módulo Boutique funciona correctamente. Verificar que los componentes renderizan sin errores. Preguntar al usuario si hay dudas.

- [x] 15. Frontend Angular — Módulo Admin Boutique
  - [x] 15.1 Crear módulo `BoutiqueAdminModule` dentro de `/admin/administrador/pages/boutique/`
    - Crear `boutique-admin.module.ts`, `boutique-admin-routing.module.ts`
    - Registrar como ruta hija en `administrador-routing.module.ts` con lazy-loading
    - Crear servicios admin: category, product, order, inventory
    - _Requisitos: 13.2, 14.7_

  - [x] 15.2 Crear `CategoriesComponent` — gestión de categorías (admin)
    - Tabla con lista de categorías, botones crear/editar/eliminar
    - Diálogo para crear/editar categoría (nombre, descripción, estado)
    - _Requisitos: 1.1, 1.2, 1.3_

  - [x] 15.3 Crear `ProductsComponent` y `ProductFormComponent` — gestión de productos (admin)
    - Tabla con lista de productos, filtros, paginación
    - Indicador de stock bajo (≤5 unidades) con alerta visual
    - Formulario de producto: nombre, descripción, precio, SKU, categoría, stock, estado
    - Componente `ProductImageManagerComponent` para subir, reordenar y eliminar imágenes
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 12.1, 12.4_

  - [x] 15.4 Crear `OrdersComponent` y `OrderDetailComponent` — gestión de pedidos (admin)
    - Tabla de pedidos con filtros por estado, fecha, búsqueda
    - Detalle de pedido: items, cliente, envío, pago
    - Acciones: cambiar estado, confirmar pago manual, generar guía de envío
    - Resumen con métricas: total pedidos, pendientes, ingresos
    - Componente `OrderStatusBadgeComponent` para estados visuales
    - _Requisitos: 11.1, 11.2, 11.3, 11.4, 11.5, 8.6, 9.5_

  - [x] 15.5 Crear `InventoryComponent` — gestión de inventario (admin)
    - Lista de productos con stock actual
    - Ajuste manual de stock con campo de razón
    - Historial de movimientos de inventario por producto
    - Componente `InventoryAlertComponent` para alertas de stock bajo
    - _Requisitos: 12.1, 12.2, 12.4, 12.5_

- [x] 16. Checkpoint — Verificar frontend admin
  - Asegurar que el panel de administración de Boutique funciona correctamente. Verificar navegación, formularios y tablas. Preguntar al usuario si hay dudas.

- [ ]* 17. Integración con Tidio (Chat en Vivo)
  - [ ]* 17.1 Crear `TidioService` en el módulo Boutique
    - Cargar script de Tidio de forma asíncrona solo en rutas `/boutique/*`
    - Pasar datos del cliente autenticado (nombre, email) al widget vía `tidioChatApi.setVisitorData()`
    - Configurar `tidioProjectId` en `environment.ts`
    - Destruir widget al salir del módulo Boutique
    - _Requisitos: 15.1, 15.2, 15.3, 15.4, 15.5_

- [ ]* 18. Integración con Brevo (Email Transaccional)
  - [ ]* 18.1 Crear `BrevoService` en el backend
    - Métodos: sendTransactionalEmail, addContactToList
    - Usar BREVO_API_KEY, template IDs y list ID desde .env
    - _Requisitos: 16.1, 16.3, 16.4, 16.5_

  - [ ]* 18.2 Crear Job `SendBoutiqueBrevoEmail`
    - Enviar email transaccional vía API de Brevo
    - Reintentos automáticos (3 intentos, backoff 60s)
    - Registrar errores en log sin bloquear flujo principal
    - _Requisitos: 16.1, 16.2, 16.6_

  - [ ]* 18.3 Integrar despacho de emails en flujos de pedidos
    - Despachar email de confirmación al crear pedido
    - Despachar email de actualización al cambiar estado de pedido
    - Agregar contacto a lista de Brevo en primera compra
    - _Requisitos: 16.1, 16.2, 16.4_

- [x] 19. Variables de entorno y configuración final
  - Agregar variables de entorno nuevas al `.env.example`: Stripe, Envia.com, Brevo, Cloudinary boutique
  - Agregar `tidioProjectId` a `environment.ts` y `environment.prod.ts`
  - Registrar `CancelUnpaidBoutiqueOrders` en el Kernel schedule
  - _Requisitos: 8.3, 9.3, 15.4, 16.5_

- [x] 20. Checkpoint final — Verificar integración completa
  - Asegurar que todos los tests pasan. Verificar que el flujo completo funciona: catálogo → carrito → checkout → pago → pedido → envío. Preguntar al usuario si hay dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Las integraciones Tidio (17) y Brevo (18) están marcadas como opcionales ya que son nice-to-have para MVP
- Los tests de propiedades validan propiedades universales de correctitud del diseño
- Los tests unitarios validan ejemplos específicos y edge cases
