# Documento de Requisitos — Configuración de Stripe con Switch Test/Producción

## Introducción

Este feature permite a los usuarios con rol developer o administrator gestionar las llaves de Stripe (test y producción) desde el panel de desarrollador, sin necesidad de editar manualmente el archivo `.env`. El sistema almacena las configuraciones en base de datos con la tabla `system_settings`, permite alternar entre modo test y producción mediante un toggle, y el StripeService lee dinámicamente las llaves activas con fallback al `.env`.

## Glosario

- **Sistema_Configuración**: Módulo backend que gestiona la lectura y escritura de configuraciones de Stripe en la tabla `system_settings`.
- **Panel_Developer**: Interfaz frontend del panel de desarrollador ubicada en `/admin/developer`.
- **StripeService**: Servicio backend (`App\Services\Boutique\StripeService`) que inicializa la conexión con Stripe usando las llaves configuradas.
- **Modo_Activo**: Estado que indica si el sistema usa llaves de test (`test`) o producción (`live`).
- **Llave_Secreta**: Clave secreta de Stripe (`sk_test_*` o `sk_live_*`) usada para operaciones server-side.
- **Llave_Publicable**: Clave pública de Stripe (`pk_test_*` o `pk_live_*`) usada para inicializar Stripe.js en el frontend.
- **Webhook_Secret**: Clave de verificación de firma para webhooks de Stripe (`whsec_*`).
- **SettingsController**: Controlador backend que expone los endpoints para consultar y actualizar la configuración de Stripe.

## Requisitos

### Requisito 1: Almacenamiento de configuración de Stripe en base de datos

**Historia de Usuario:** Como developer, quiero que las llaves de Stripe se almacenen en base de datos, para poder cambiarlas sin reiniciar el servidor ni editar archivos.

#### Criterios de Aceptación

1. THE Sistema_Configuración SHALL almacenar las siguientes claves en la tabla `system_settings`: `stripe_mode`, `stripe_test_publishable_key`, `stripe_test_secret_key`, `stripe_test_webhook_secret`, `stripe_live_publishable_key`, `stripe_live_secret_key`, `stripe_live_webhook_secret`.
2. THE Sistema_Configuración SHALL usar el valor `test` como valor por defecto para `stripe_mode` cuando no exista configuración previa.
3. WHEN se almacena una llave secreta o webhook secret, THE Sistema_Configuración SHALL cifrar el valor usando la función `encrypt()` de Laravel antes de guardarlo en la base de datos.
4. WHEN se lee una llave secreta o webhook secret, THE Sistema_Configuración SHALL descifrar el valor usando la función `decrypt()` de Laravel antes de retornarlo.

### Requisito 2: Endpoints de API para gestión de configuración

**Historia de Usuario:** Como developer, quiero endpoints protegidos para consultar y actualizar la configuración de Stripe, para poder gestionarla desde el panel.

#### Criterios de Aceptación

1. THE SettingsController SHALL exponer un endpoint `POST /api/settings/stripe` que retorne la configuración actual de Stripe.
2. THE SettingsController SHALL exponer un endpoint `POST /api/settings/stripe/update` que permita actualizar la configuración de Stripe.
3. THE SettingsController SHALL proteger ambos endpoints con los middlewares `auth:sanctum` y `role:developer|administrator`.
4. WHEN el endpoint de consulta retorna llaves secretas, THE SettingsController SHALL enmascarar los valores mostrando solo los últimos 4 caracteres (ejemplo: `••••••••a1b2`).
5. WHEN el endpoint de consulta retorna llaves publicables, THE SettingsController SHALL retornar el valor completo sin enmascarar.
6. WHEN se recibe una solicitud de actualización con campos vacíos, THE SettingsController SHALL conservar los valores existentes para esos campos.
7. IF un usuario sin rol developer o administrator intenta acceder a los endpoints, THEN THE SettingsController SHALL retornar un error 403.

### Requisito 3: Endpoint público de llave publicable

**Historia de Usuario:** Como frontend, quiero obtener la llave publicable activa de Stripe, para poder inicializar Stripe.js correctamente según el modo activo.

#### Criterios de Aceptación

1. THE SettingsController SHALL exponer un endpoint `POST /api/settings/stripe/publishable_key` que retorne la llave publicable correspondiente al modo activo.
2. THE SettingsController SHALL proteger este endpoint con el middleware `auth:sanctum` sin restricción de rol.
3. WHEN el modo activo es `test`, THE SettingsController SHALL retornar la llave `stripe_test_publishable_key`.
4. WHEN el modo activo es `live`, THE SettingsController SHALL retornar la llave `stripe_live_publishable_key`.
5. IF no existe llave publicable configurada en base de datos, THEN THE SettingsController SHALL retornar un valor vacío.

### Requisito 4: StripeService lee configuración dinámica con fallback

**Historia de Usuario:** Como sistema, quiero que StripeService lea las llaves desde la base de datos con fallback al `.env`, para que el cambio de modo sea inmediato sin reiniciar.

#### Criterios de Aceptación

1. WHEN StripeService se inicializa, THE StripeService SHALL intentar leer las llaves desde la tabla `system_settings` según el `stripe_mode` activo.
2. IF no existen llaves configuradas en la tabla `system_settings`, THEN THE StripeService SHALL usar los valores de las variables de entorno `STRIPE_SECRET_KEY` y `STRIPE_WEBHOOK_SECRET` como fallback.
3. WHEN el modo activo es `test`, THE StripeService SHALL usar `stripe_test_secret_key` y `stripe_test_webhook_secret`.
4. WHEN el modo activo es `live`, THE StripeService SHALL usar `stripe_live_secret_key` y `stripe_live_webhook_secret`.
5. THE StripeService SHALL resolver las llaves en cada instanciación para reflejar cambios de configuración sin reiniciar el servidor.

### Requisito 5: Interfaz de configuración en el Panel Developer

**Historia de Usuario:** Como developer, quiero una sección "Configuración Stripe" en el panel developer, para gestionar las llaves y el modo activo visualmente.

#### Criterios de Aceptación

1. THE Panel_Developer SHALL mostrar una sección "Configuración Stripe" accesible desde el menú lateral con el ícono `credit_card`.
2. THE Panel_Developer SHALL mostrar un toggle que permita alternar entre "Modo Test" y "Modo Producción".
3. THE Panel_Developer SHALL mostrar campos de entrada para las llaves de test: Publishable Key, Secret Key, Webhook Secret.
4. THE Panel_Developer SHALL mostrar campos de entrada para las llaves de producción: Publishable Key, Secret Key, Webhook Secret.
5. THE Panel_Developer SHALL mostrar un indicador visual del modo activo actual (badge con color verde para test, rojo para producción).
6. WHEN se muestran llaves secretas en los campos de entrada, THE Panel_Developer SHALL enmascarar el valor mostrando solo los últimos 4 caracteres.
7. THE Panel_Developer SHALL mostrar un botón "Guardar Configuración" que envíe los cambios al endpoint de actualización.
8. WHEN el usuario guarda la configuración exitosamente, THE Panel_Developer SHALL mostrar una notificación de éxito.
9. IF ocurre un error al guardar, THEN THE Panel_Developer SHALL mostrar un mensaje de error descriptivo.
10. WHEN el usuario cambia el toggle de modo, THE Panel_Developer SHALL incluir el nuevo modo en la solicitud de guardado.

### Requisito 6: Validación de llaves de Stripe

**Historia de Usuario:** Como developer, quiero que el sistema valide el formato de las llaves antes de guardarlas, para evitar configuraciones incorrectas.

#### Criterios de Aceptación

1. WHEN se recibe una llave publicable de test, THE Sistema_Configuración SHALL validar que comience con el prefijo `pk_test_`.
2. WHEN se recibe una llave publicable de producción, THE Sistema_Configuración SHALL validar que comience con el prefijo `pk_live_`.
3. WHEN se recibe una llave secreta de test, THE Sistema_Configuración SHALL validar que comience con el prefijo `sk_test_`.
4. WHEN se recibe una llave secreta de producción, THE Sistema_Configuración SHALL validar que comience con el prefijo `sk_live_`.
5. WHEN se recibe un webhook secret, THE Sistema_Configuración SHALL validar que comience con el prefijo `whsec_`.
6. IF una llave no cumple con el formato esperado, THEN THE Sistema_Configuración SHALL retornar un error de validación con el mensaje específico del campo incorrecto.
