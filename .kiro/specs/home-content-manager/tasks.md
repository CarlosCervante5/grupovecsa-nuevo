# Plan de Implementación: Home Content Manager

## Resumen

Migración de la gestión de contenido de la página de inicio (slides hero y testimonios Success Day) desde archivos JSON locales hacia vecsa-backend (Laravel) + vecsa-frontend (Angular). Se implementa backend primero (migraciones → modelos → requests → controllers → jobs → rutas), luego frontend Angular, luego actualización de la página PHP pública.

## Tareas

- [x] 1. Crear migraciones de base de datos
  - [x] 1.1 Crear migración para la tabla `home_slides`
    - Crear archivo de migración en `vecsa-backend/database/migrations/` con todos los campos definidos en el diseño: id, sort_id, uuid, title, subtitle, offer_main, offer_main_text, offer_sub, offer_secondary, offer_secondary_text, button_text, button_link, disclaimer, desktop_image_path, mobile_image_path, active, timestamps, soft deletes
    - Usar el prefijo de tabla `DB_TABLE_PREFIX` siguiendo el patrón de `create_marketing_promotions_table.php`
    - _Requerimientos: 1.1, 1.2_

  - [x] 1.2 Crear migración para la tabla `home_testimonials`
    - Crear archivo de migración con campos: id, sort_id, uuid, image_path, alt, active, timestamps, soft deletes
    - Usar el prefijo de tabla `DB_TABLE_PREFIX` siguiendo el patrón existente
    - _Requerimientos: 2.1, 2.2_

- [x] 2. Crear modelos Eloquent
  - [x] 2.1 Crear modelo `HomeSlide`
    - Crear `vecsa-backend/app/Models/HomeSlide.php` siguiendo el patrón de `MarketingPromotion`
    - Incluir: UUID boot, SoftDeletes, $fillable, $hidden, $casts (active → boolean), Carbon accessors, findByUuid()
    - Usar tabla con prefijo dinámico en constructor
    - _Requerimientos: 1.3, 3.1_

  - [x] 2.2 Crear modelo `HomeTestimonial`
    - Crear `vecsa-backend/app/Models/HomeTestimonial.php` siguiendo el patrón de `MarketingPromotion`
    - Incluir: UUID boot, SoftDeletes, $fillable, $hidden, $casts (active → boolean), Carbon accessors, findByUuid()
    - _Requerimientos: 2.3, 4.1_

  - [ ]* 2.3 Escribir test de propiedad para generación automática de UUID
    - **Propiedad 1: Generación automática de UUID**
    - Verificar que al crear HomeSlide y HomeTestimonial se asigna un UUID v4 válido y único
    - **Valida: Requerimientos 1.3, 2.3**

- [x] 3. Crear FormRequests de validación
  - [x] 3.1 Crear `StoreHomeSlideRequest`
    - Crear en `vecsa-backend/app/Http/Requests/HomeSlides/StoreHomeSlideRequest.php`
    - Validar: title requerido, campos opcionales como strings, imágenes opcionales (mimes: jpeg,png,jpg,gif,webp, max: 5120)
    - _Requerimientos: 3.7_

  - [x] 3.2 Crear `DeleteHomeSlideRequest`, `UpdateSortHomeSlideRequest`, `ToggleHomeSlideRequest`
    - DeleteHomeSlideRequest: validar uuid requerido
    - UpdateSortHomeSlideRequest: validar array de image_order con uuid y sort_id
    - ToggleHomeSlideRequest: validar uuid requerido
    - _Requerimientos: 3.4, 3.5, 3.6_

  - [x] 3.3 Crear `StoreHomeTestimonialRequest`
    - Crear en `vecsa-backend/app/Http/Requests/HomeTestimonials/StoreHomeTestimonialRequest.php`
    - Validar: image requerida (mimes: jpeg,png,jpg,gif,webp, max: 5120), alt opcional string
    - _Requerimientos: 4.6_

  - [x] 3.4 Crear `DeleteHomeTestimonialRequest`, `UpdateSortHomeTestimonialRequest`, `ToggleHomeTestimonialRequest`
    - Misma estructura que los de slides, validando uuid y arrays de orden
    - _Requerimientos: 4.3, 4.4, 4.5_

- [x] 4. Crear Jobs de carga de imágenes
  - [x] 4.1 Crear `UploadHomeSlideImage` Job
    - Crear en `vecsa-backend/app/Jobs/UploadHomeSlideImage.php` siguiendo el patrón de `UploadPromotionImage`
    - Subir imagen a Cloudinary usando `CLOUDINARY_HOME_SLIDES_FOLDER_BASE`, transferir a S3, actualizar campo desktop_image_path o mobile_image_path en el modelo
    - Incluir reintentos (tries=5, backoff=60), validación de inputs, limpieza de temporales
    - _Requerimientos: 5.1, 5.2, 5.3, 5.4_

  - [x] 4.2 Crear `UploadHomeTestimonialImage` Job
    - Crear en `vecsa-backend/app/Jobs/UploadHomeTestimonialImage.php` siguiendo el patrón de `UploadPromotionImage`
    - Subir imagen a Cloudinary usando `CLOUDINARY_HOME_TESTIMONIALS_FOLDER_BASE`, transferir a S3, actualizar campo image_path en el modelo
    - _Requerimientos: 6.1, 6.2, 6.3, 6.4_

  - [x] 4.3 Agregar variables de entorno de Cloudinary
    - Agregar `CLOUDINARY_HOME_SLIDES_FOLDER_BASE` y `CLOUDINARY_HOME_TESTIMONIALS_FOLDER_BASE` en `vecsa-backend/.env` y `.env.example`
    - _Requerimientos: 5.2, 6.2_

- [x] 5. Crear Controllers del backend
  - [x] 5.1 Crear `HomeSlideController`
    - Crear en `vecsa-backend/app/Http/Controllers/HomeSlides/HomeSlideController.php`
    - Implementar métodos: search (ordenado por sort_id), store (crear slide + despachar jobs de imagen), update (actualizar campos + despachar jobs si hay imágenes nuevas), delete (soft delete por uuid), sortUpdate (reordenar en transacción), toggle (invertir active)
    - Usar ApiResponseHelper para todas las respuestas, siguiendo el patrón de PromotionController
    - _Requerimientos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.8_

  - [x] 5.2 Crear `HomeTestimonialController`
    - Crear en `vecsa-backend/app/Http/Controllers/HomeTestimonials/HomeTestimonialController.php`
    - Implementar métodos: search, store (crear testimonio + despachar job de imagen), delete, sortUpdate, toggle
    - _Requerimientos: 4.1, 4.2, 4.3, 4.4, 4.5, 4.7_

  - [x] 5.3 Crear `HomePublicController`
    - Crear en `vecsa-backend/app/Http/Controllers/Home/HomePublicController.php`
    - Implementar métodos: slides (retornar slides activos ordenados por sort_id), testimonials (retornar testimonios activos ordenados por sort_id)
    - Sin autenticación, solo middleware bandwidth_usage
    - _Requerimientos: 7.1, 7.2_

- [x] 6. Registrar rutas API y checkpoint backend
  - [x] 6.1 Agregar rutas en `vecsa-backend/routes/api.php`
    - Agregar bloque público: `home/slides`, `home/testimonials` con middleware `bandwidth_usage`
    - Agregar bloque protegido: `home_slides/` (store, search, update, delete, sort_update, toggle) con middleware `bandwidth_usage` + `auth:sanctum`
    - Agregar bloque protegido: `home_testimonials/` (store, search, delete, sort_update, toggle) con middleware `bandwidth_usage` + `auth:sanctum`
    - Agregar imports de los nuevos controllers
    - _Requerimientos: 3.1-3.8, 4.1-4.7, 7.1-7.3_

- [x] 7. Checkpoint - Verificar backend completo
  - Ensure all tests pass, ask the user if questions arise.
  - Ejecutar migraciones y verificar que las tablas se crean correctamente
  - Verificar que los endpoints responden correctamente

- [x] 8. Crear servicios e interfaces Angular
  - [x] 8.1 Agregar interfaces `HomeSlide` y `HomeTestimonial` en el archivo de interfaces del frontend
    - Agregar las interfaces HomeSlide, HomeTestimonial, HomeSlidesResponse, HomeTestimonialsResponse en el archivo de interfaces existente
    - _Requerimientos: 8.1, 9.1_

  - [x] 8.2 Crear `HomeSlideService`
    - Crear servicio Angular con métodos: search, store, update, delete, sortUpdate, toggle
    - Usar HttpClient con baseUrl del environment, siguiendo el patrón de servicios existentes
    - _Requerimientos: 8.1, 8.3, 8.4, 8.5_

  - [x] 8.3 Crear `HomeTestimonialService`
    - Crear servicio Angular con métodos: search, store, delete, sortUpdate, toggle
    - _Requerimientos: 9.1, 9.3, 9.4, 9.5_

- [x] 9. Crear componente de gestión de Slides en Angular
  - [x] 9.1 Crear `HomeSlidesComponent`
    - Crear componente en `vecsa-frontend/src/app/admin/marketing/pages/home-slides/`
    - Implementar: tabla/lista con drag-drop (CDK DragDrop) para reordenar, formulario para crear/editar slide con todos los campos (título, subtítulo, ofertas, botón, disclaimer, imágenes desktop/mobile), toggle de estado activo/inactivo, botón de eliminar
    - Usar Angular Material para UI, siguiendo patrones del módulo marketing existente
    - _Requerimientos: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 10. Crear componente de gestión de Testimonios en Angular
  - [x] 10.1 Crear `HomeTestimonialsComponent`
    - Crear componente en `vecsa-frontend/src/app/admin/marketing/pages/home-testimonials/`
    - Implementar: grid visual con drag-drop para reordenar, formulario de upload con campo de imagen y alt text, toggle de estado, botón de eliminar
    - _Requerimientos: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 11. Integrar componentes en el módulo Marketing
  - [x] 11.1 Actualizar `marketing-routing.module.ts`
    - Agregar rutas: `home-slides` → HomeSlidesComponent, `home-testimonials` → HomeTestimonialsComponent
    - _Requerimientos: 8.1, 9.1_

  - [x] 11.2 Actualizar `marketing.module.ts`
    - Declarar HomeSlidesComponent y HomeTestimonialsComponent en el módulo
    - _Requerimientos: 8.1, 9.1_

  - [x] 11.3 Agregar tarjetas de navegación en el Dashboard
    - Agregar tarjeta "Home Slides" con icono `fi fi-rr-picture` y permalink `/admin/marketing/home-slides`
    - Agregar tarjeta "Testimonios" con icono `fi fi-rr-star` y permalink `/admin/marketing/home-testimonials`
    - Seguir el patrón de la tarjeta "Vehículos" existente en `dashboard.component.ts`
    - _Requerimientos: 8.6, 9.6_

- [x] 12. Checkpoint - Verificar frontend completo
  - Ensure all tests pass, ask the user if questions arise.
  - Verificar que las páginas de slides y testimonios cargan correctamente en el navegador

- [x] 13. Actualizar la Página de Inicio PHP
  - [x] 13.1 Modificar `dynamic-slider.php` para consumir API
    - Reemplazar lectura de `slides.json` por llamada HTTP POST a `{API_BASE_URL}/api/home/slides`
    - Renderizar imágenes usando URLs de CloudFront (desktop_image_path, mobile_image_path)
    - Implementar fallback: si la API falla, mostrar sección vacía sin interrumpir la carga de la página
    - Definir variable `$api_base_url` configurable
    - _Requerimientos: 10.1, 10.3, 10.5, 10.6_

  - [x] 13.2 Modificar `index.php` sección de testimonios para consumir API
    - Reemplazar lectura de `testimonials.json` por llamada HTTP POST a `{API_BASE_URL}/api/home/testimonials`
    - Renderizar imágenes usando URLs de CloudFront (image_path)
    - Implementar fallback: si la API falla, mostrar sección vacía
    - _Requerimientos: 10.2, 10.4, 10.5, 10.6_

- [ ] 14. Checkpoint final y tests de propiedades
  - [ ]* 14.1 Escribir test de propiedad para búsqueda ordenada
    - **Propiedad 2: Búsqueda ordenada por sort_id**
    - **Valida: Requerimientos 3.2, 4.2**

  - [ ]* 14.2 Escribir test de propiedad para creación de Slide preserva datos
    - **Propiedad 3: Creación de Slide preserva datos**
    - **Valida: Requerimiento 3.1**

  - [ ]* 14.3 Escribir test de propiedad para actualización de Slide
    - **Propiedad 4: Actualización de Slide modifica campos correctamente**
    - **Valida: Requerimiento 3.3**

  - [ ]* 14.4 Escribir test de propiedad para soft delete
    - **Propiedad 5: Soft delete excluye de consultas normales**
    - **Valida: Requerimientos 3.4, 4.3**

  - [ ]* 14.5 Escribir test de propiedad para reordenamiento
    - **Propiedad 6: Reordenamiento actualiza sort_ids correctamente**
    - **Valida: Requerimientos 3.5, 4.4**

  - [ ]* 14.6 Escribir test de propiedad para toggle round-trip
    - **Propiedad 7: Toggle de estado es round-trip**
    - **Valida: Requerimientos 3.6, 4.5**

  - [ ]* 14.7 Escribir test de propiedad para validación de Slide
    - **Propiedad 8: Validación rechaza datos inválidos de Slide**
    - **Valida: Requerimiento 3.7**

  - [ ]* 14.8 Escribir test de propiedad para validación de imagen
    - **Propiedad 9: Validación de imagen rechaza archivos inválidos**
    - **Valida: Requerimiento 4.6**

  - [ ]* 14.9 Escribir test de propiedad para UUID inexistente
    - **Propiedad 10: UUID inexistente retorna 404**
    - **Valida: Requerimientos 3.8, 4.7**

  - [ ]* 14.10 Escribir test de propiedad para API pública filtra activos
    - **Propiedad 11: API pública filtra solo activos y ordena por sort_id**
    - **Valida: Requerimientos 7.1, 7.2**

- [x] 15. Checkpoint final - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requerimientos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Los tests de propiedades validan propiedades universales de correctitud
- El orden de implementación es: backend (migraciones → modelos → requests → controllers → jobs → rutas) → frontend (servicios → componentes → integración) → página PHP
