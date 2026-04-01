# Plan de Implementación: Configuración de Stripe con Switch Test/Producción

## Resumen

Implementar el módulo de configuración dinámica de Stripe que permite gestionar llaves test/producción desde el panel developer, con almacenamiento cifrado en base de datos y resolución dinámica en StripeService.

## Tareas

- [x] 1. Crear migración y modelo SystemSetting
  - [x] 1.1 Crear migración `create_system_settings_table` con columnas `id`, `key` (unique), `value` (text nullable), `created_at`, `updated_at` y prefijo de tabla `app_vecsa_`
    - _Requisitos: 1.1_
  - [x] 1.2 Crear modelo `SystemSetting` en `app/Models/SystemSetting.php` con métodos estáticos `get()`, `set()`, `getEncrypted()`, `setEncrypted()`
    - `get(key, default)` lee valor en texto plano de la tabla
    - `set(key, value)` escribe/actualiza valor en texto plano (upsert por key)
    - `getEncrypted(key, default)` lee y descifra con `decrypt()`, captura `DecryptException` y retorna `default` si falla
    - `setEncrypted(key, value)` cifra con `encrypt()` y almacena
    - Tabla: `app_vecsa_system_settings`, fillable: `[key, value]`
    - _Requisitos: 1.1, 1.2, 1.3, 1.4_
  - [ ]* 1.3 Escribir test de propiedad para round-trip de cifrado
    - **Propiedad 1: Round-trip de cifrado de llaves secretas**
    - **Valida: Requisitos 1.3, 1.4**
  - [ ]* 1.4 Escribir test de propiedad para round-trip key-value
    - **Propiedad 2: Round-trip de almacenamiento key-value**
    - **Valida: Requisito 1.1**

- [x] 2. Checkpoint - Verificar migración y modelo
  - Ejecutar migración y asegurar que los tests pasan. Preguntar al usuario si hay dudas.

- [x] 3. Crear SettingsController y rutas API
  - [x] 3.1 Crear `SettingsController` en `app/Http/Controllers/Settings/SettingsController.php` con tres métodos:
    - `stripe()`: retorna configuración actual con llaves secretas enmascaradas (últimos 4 chars) y publicables completas
    - `updateStripe()`: actualiza configuración, conserva valores existentes para campos vacíos, valida prefijos de llaves
    - `publishableKey()`: retorna llave publicable según modo activo
    - Validación de prefijos: `pk_test_`, `pk_live_`, `sk_test_`, `sk_live_`, `whsec_` — solo se validan campos con contenido
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 3.1, 3.2, 3.3, 3.4, 3.5, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_
  - [x] 3.2 Registrar rutas en `routes/api.php` bajo prefijo `settings` con middlewares `auth:sanctum` y `role:developer|administrator` para stripe y stripe/update, y solo `auth:sanctum` para `stripe/publishable_key`
    - _Requisitos: 2.3, 3.2_
  - [ ]* 3.3 Escribir test de propiedad para enmascaramiento según tipo de llave
    - **Propiedad 3: Enmascaramiento correcto según tipo de llave**
    - **Valida: Requisitos 2.4, 2.5**
  - [ ]* 3.4 Escribir test de propiedad para preservación de campos vacíos
    - **Propiedad 4: Preservación de campos vacíos en actualización**
    - **Valida: Requisito 2.6**
  - [ ]* 3.5 Escribir test de propiedad para resolución de llave publicable según modo
    - **Propiedad 5: Resolución de llave publicable según modo activo**
    - **Valida: Requisitos 3.3, 3.4**
  - [ ]* 3.6 Escribir test de propiedad para validación de prefijos
    - **Propiedad 8: Validación de prefijos de llaves**
    - **Valida: Requisitos 6.1, 6.2, 6.3, 6.4, 6.5, 6.6**
  - [ ]* 3.7 Escribir test de propiedad para autorización de endpoints
    - **Propiedad 9: Autorización de endpoints**
    - **Valida: Requisitos 2.3, 2.7**

- [x] 4. Modificar StripeService para lectura dinámica
  - [x] 4.1 Modificar el constructor de `StripeService` en `app/Services/Boutique/StripeService.php` para leer `stripe_mode` desde `SystemSetting::get()`, resolver `secret_key` y `webhook_secret` según modo con `SystemSetting::getEncrypted()`, y usar fallback a `env()` si retorna null
    - _Requisitos: 4.1, 4.2, 4.3, 4.4, 4.5_
  - [ ]* 4.2 Escribir test de propiedad para resolución de llaves según modo
    - **Propiedad 6: Resolución de llaves en StripeService según modo**
    - **Valida: Requisitos 4.1, 4.3, 4.4**
  - [ ]* 4.3 Escribir test de propiedad para resolución dinámica por instanciación
    - **Propiedad 7: Resolución dinámica por instanciación**
    - **Valida: Requisito 4.5**

- [x] 5. Checkpoint - Verificar backend completo
  - Asegurar que todos los tests pasan y que los endpoints responden correctamente. Preguntar al usuario si hay dudas.

- [x] 6. Implementar sección stripe_config en el panel developer (Frontend Angular)
  - [x] 6.1 Agregar botón de navegación `stripe_config` en el sidebar del `DeveloperDashboardComponent`, siguiendo el patrón existente de `role_matrix` y `benchmark`
    - Agregar caso `stripe_config` en `selectSection()` para cargar la configuración al seleccionar
    - Agregar propiedades al componente: `stripeConfig`, `stripeLoading`, `stripeSaving`, `stripeError`, `stripeSuccess`
    - _Requisitos: 5.1_
  - [x] 6.2 Crear template HTML de la sección stripe_config en `dashboard.component.html`
    - Toggle para alternar entre modo `test` y `live`
    - Badge indicador: verde para test, rojo para producción
    - Campos de entrada para llaves test (publishable, secret, webhook) y producción (publishable, secret, webhook)
    - Botón "Guardar Configuración"
    - Notificaciones de éxito/error
    - Los campos de llaves secretas muestran valor enmascarado; al editar, el usuario escribe la llave completa
    - _Requisitos: 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 5.9, 5.10_
  - [x] 6.3 Implementar métodos `loadStripeConfig()` y `saveStripeConfig()` en el componente
    - `loadStripeConfig()` llama a `POST /api/settings/stripe` y carga los datos en `stripeConfig`
    - `saveStripeConfig()` llama a `POST /api/settings/stripe/update` con los valores editados, muestra notificación de éxito o error
    - _Requisitos: 5.7, 5.8, 5.9, 5.10_
  - [x] 6.4 Agregar estilos CSS para la sección stripe_config en `dashboard.component.css`
    - Estilos para el toggle, badge, campos de entrada, botón de guardar y notificaciones
    - Seguir el patrón visual existente del panel developer
    - _Requisitos: 5.2, 5.5_

- [x] 7. Checkpoint final - Verificar integración completa
  - Asegurar que todos los tests pasan y que la sección stripe_config funciona correctamente en el panel developer. Preguntar al usuario si hay dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Los tests de propiedad validan propiedades universales de correctitud
- El backend usa PHP/Laravel y el frontend usa TypeScript/Angular
