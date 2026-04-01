# Documento de Requerimientos

## Introducción

Este documento define los requerimientos para migrar la gestión del contenido de la página de inicio (slides del hero y testimonios de Success Day) desde el panel de administración PHP standalone hacia el sistema existente vecsa-backend (Laravel) + vecsa-frontend (Angular). Esto centraliza la administración de contenido en una sola plataforma, eliminando la dependencia de archivos JSON locales y aprovechando la infraestructura existente de Cloudinary/S3 para el manejo de imágenes.

## Glosario

- **Sistema_Backend**: La aplicación Laravel (vecsa-backend) que expone endpoints API y gestiona la lógica de negocio, base de datos SQLite y carga de imágenes a Cloudinary/S3.
- **Sistema_Frontend**: La aplicación Angular (vecsa-frontend) que provee la interfaz de administración para el equipo de marketing.
- **Página_Inicio**: La página PHP pública (home/index.php) que renderiza el sitio web de Grupo VECSA para los visitantes.
- **Slide**: Un elemento del slider/hero de la Página_Inicio que contiene título, subtítulo, ofertas, botón de acción, disclaimer e imágenes para escritorio y móvil.
- **Testimonio**: Una imagen de entrega de vehículo (Success Day) mostrada en la sección de testimonios de la Página_Inicio, con texto alternativo y estado activo/inactivo.
- **API_Pública**: Endpoints del Sistema_Backend que no requieren autenticación, consumidos por la Página_Inicio.
- **API_Protegida**: Endpoints del Sistema_Backend protegidos con Sanctum, consumidos por el Sistema_Frontend.
- **Módulo_Marketing**: El módulo lazy-loaded de Angular en la ruta `/admin/marketing/` del Sistema_Frontend.

## Requerimientos

### Requerimiento 1: Migración de datos de Slides

**Historia de Usuario:** Como administrador de marketing, quiero que los slides del hero se almacenen en la base de datos del Sistema_Backend, para centralizar la gestión de contenido y eliminar la dependencia de archivos JSON locales.

#### Criterios de Aceptación

1. THE Sistema_Backend SHALL proveer una migración de base de datos que cree la tabla `home_slides` con los campos: uuid, title, subtitle, offer_main, offer_main_text, offer_sub, offer_secondary, offer_secondary_text, button_text, button_link, disclaimer, desktop_image_path, mobile_image_path, active (booleano), sort_id (entero), timestamps y soft deletes.
2. THE Sistema_Backend SHALL utilizar el prefijo de tabla `app_vecsa_` definido en la variable de entorno `DB_TABLE_PREFIX` para la tabla `home_slides`.
3. THE Sistema_Backend SHALL generar un UUID automáticamente al crear cada registro de Slide, siguiendo el patrón del modelo MarketingPromotion existente.

### Requerimiento 2: Migración de datos de Testimonios

**Historia de Usuario:** Como administrador de marketing, quiero que los testimonios de Success Day se almacenen en la base de datos del Sistema_Backend, para centralizar la gestión de contenido.

#### Criterios de Aceptación

1. THE Sistema_Backend SHALL proveer una migración de base de datos que cree la tabla `home_testimonials` con los campos: uuid, image_path, alt, active (booleano), sort_id (entero), timestamps y soft deletes.
2. THE Sistema_Backend SHALL utilizar el prefijo de tabla `app_vecsa_` definido en la variable de entorno `DB_TABLE_PREFIX` para la tabla `home_testimonials`.
3. THE Sistema_Backend SHALL generar un UUID automáticamente al crear cada registro de Testimonio, siguiendo el patrón del modelo MarketingPromotion existente.

### Requerimiento 3: API protegida CRUD de Slides

**Historia de Usuario:** Como administrador de marketing, quiero crear, consultar, actualizar y eliminar slides desde el Sistema_Frontend, para gestionar el contenido del hero de la Página_Inicio.

#### Criterios de Aceptación

1. WHEN se recibe una solicitud POST autenticada a `/api/home_slides`, THE Sistema_Backend SHALL crear un nuevo Slide con los datos validados y retornar el registro creado.
2. WHEN se recibe una solicitud POST autenticada a `/api/home_slides/search`, THE Sistema_Backend SHALL retornar la lista de todos los Slides ordenados por sort_id.
3. WHEN se recibe una solicitud POST autenticada a `/api/home_slides/update` con un uuid válido, THE Sistema_Backend SHALL actualizar los campos del Slide correspondiente.
4. WHEN se recibe una solicitud POST autenticada a `/api/home_slides/delete` con un uuid válido, THE Sistema_Backend SHALL eliminar (soft delete) el Slide correspondiente.
5. WHEN se recibe una solicitud POST autenticada a `/api/home_slides/sort_update`, THE Sistema_Backend SHALL actualizar el sort_id de cada Slide según el orden proporcionado, dentro de una transacción de base de datos.
6. WHEN se recibe una solicitud POST autenticada a `/api/home_slides/toggle` con un uuid válido, THE Sistema_Backend SHALL invertir el valor del campo active del Slide correspondiente.
7. THE Sistema_Backend SHALL validar los datos de entrada de Slides mediante una clase FormRequest dedicada, requiriendo al menos el campo title como obligatorio.
8. IF se proporciona un uuid que no corresponde a un Slide existente, THEN THE Sistema_Backend SHALL retornar un error 404 con un mensaje descriptivo.

### Requerimiento 4: API protegida CRUD de Testimonios

**Historia de Usuario:** Como administrador de marketing, quiero crear, consultar y eliminar testimonios desde el Sistema_Frontend, para gestionar la sección Success Day de la Página_Inicio.

#### Criterios de Aceptación

1. WHEN se recibe una solicitud POST autenticada a `/api/home_testimonials` con una imagen válida, THE Sistema_Backend SHALL crear un nuevo Testimonio, procesar la carga de imagen y retornar el registro creado.
2. WHEN se recibe una solicitud POST autenticada a `/api/home_testimonials/search`, THE Sistema_Backend SHALL retornar la lista de todos los Testimonios ordenados por sort_id.
3. WHEN se recibe una solicitud POST autenticada a `/api/home_testimonials/delete` con un uuid válido, THE Sistema_Backend SHALL eliminar (soft delete) el Testimonio correspondiente.
4. WHEN se recibe una solicitud POST autenticada a `/api/home_testimonials/sort_update`, THE Sistema_Backend SHALL actualizar el sort_id de cada Testimonio según el orden proporcionado, dentro de una transacción de base de datos.
5. WHEN se recibe una solicitud POST autenticada a `/api/home_testimonials/toggle` con un uuid válido, THE Sistema_Backend SHALL invertir el valor del campo active del Testimonio correspondiente.
6. THE Sistema_Backend SHALL validar que la imagen proporcionada sea de tipo jpeg, png, jpg, gif o webp y no exceda 5 MB.
7. IF se proporciona un uuid que no corresponde a un Testimonio existente, THEN THE Sistema_Backend SHALL retornar un error 404 con un mensaje descriptivo.

### Requerimiento 5: Carga de imágenes de Slides a Cloudinary/S3

**Historia de Usuario:** Como administrador de marketing, quiero que las imágenes de los slides se almacenen en Cloudinary/S3, para aprovechar la infraestructura de CDN existente y mantener consistencia con el resto del sistema.

#### Criterios de Aceptación

1. WHEN se crea o actualiza un Slide con imágenes, THE Sistema_Backend SHALL procesar la carga de imágenes mediante un Job en cola, siguiendo el patrón de UploadPromotionImage existente.
2. THE Sistema_Backend SHALL subir las imágenes de Slides a Cloudinary usando una carpeta base definida por la variable de entorno `CLOUDINARY_HOME_SLIDES_FOLDER_BASE`.
3. WHEN la carga a Cloudinary es exitosa, THE Sistema_Backend SHALL transferir la imagen a S3 y almacenar la URL de CloudFront en el campo desktop_image_path o mobile_image_path del Slide.
4. IF la carga de imagen falla, THEN THE Sistema_Backend SHALL registrar el error en el log y retornar un mensaje de error descriptivo.

### Requerimiento 6: Carga de imágenes de Testimonios a Cloudinary/S3

**Historia de Usuario:** Como administrador de marketing, quiero que las imágenes de testimonios se almacenen en Cloudinary/S3, para mantener consistencia con el manejo de imágenes del sistema.

#### Criterios de Aceptación

1. WHEN se crea un Testimonio con una imagen, THE Sistema_Backend SHALL procesar la carga de imagen mediante un Job en cola, siguiendo el patrón de UploadPromotionImage existente.
2. THE Sistema_Backend SHALL subir las imágenes de Testimonios a Cloudinary usando una carpeta base definida por la variable de entorno `CLOUDINARY_HOME_TESTIMONIALS_FOLDER_BASE`.
3. WHEN la carga a Cloudinary es exitosa, THE Sistema_Backend SHALL transferir la imagen a S3 y almacenar la URL de CloudFront en el campo image_path del Testimonio.
4. IF la carga de imagen falla, THEN THE Sistema_Backend SHALL registrar el error en el log y retornar un mensaje de error descriptivo.

### Requerimiento 7: API pública para la Página de Inicio

**Historia de Usuario:** Como visitante del sitio web, quiero que la Página_Inicio cargue los slides y testimonios desde la API del Sistema_Backend, para ver contenido actualizado sin depender de archivos JSON locales.

#### Criterios de Aceptación

1. WHEN se recibe una solicitud POST a `/api/home/slides`, THE Sistema_Backend SHALL retornar la lista de Slides activos ordenados por sort_id, sin requerir autenticación.
2. WHEN se recibe una solicitud POST a `/api/home/testimonials`, THE Sistema_Backend SHALL retornar la lista de Testimonios activos ordenados por sort_id, sin requerir autenticación.
3. THE Sistema_Backend SHALL aplicar el middleware `bandwidth_usage` a los endpoints de la API_Pública, siguiendo el patrón de las rutas públicas existentes.

### Requerimiento 8: Interfaz de gestión de Slides en el Sistema_Frontend

**Historia de Usuario:** Como administrador de marketing, quiero una página dentro del Módulo_Marketing para gestionar los slides del hero, para crear, editar, reordenar y activar/desactivar slides con una interfaz visual.

#### Criterios de Aceptación

1. THE Sistema_Frontend SHALL proveer una página de gestión de Slides accesible desde la ruta `/admin/marketing/home-slides` dentro del Módulo_Marketing.
2. THE Sistema_Frontend SHALL mostrar la lista de Slides en una tabla o grid con columnas para: imagen miniatura, título, estado activo/inactivo y acciones (editar, eliminar, toggle).
3. WHEN el usuario arrastra y suelta un Slide en la lista, THE Sistema_Frontend SHALL enviar la solicitud de reordenamiento al Sistema_Backend usando el endpoint sort_update.
4. WHEN el usuario hace clic en el botón de crear Slide, THE Sistema_Frontend SHALL mostrar un formulario con campos para: título, subtítulo, offer_main, offer_main_text, offer_sub, offer_secondary, offer_secondary_text, button_text, button_link, disclaimer, imagen de escritorio e imagen móvil.
5. WHEN el usuario hace clic en el toggle de estado, THE Sistema_Frontend SHALL enviar la solicitud al endpoint toggle y actualizar la vista.
6. THE Sistema_Frontend SHALL agregar una tarjeta de navegación "Home Slides" en el dashboard del Módulo_Marketing, siguiendo el patrón de la tarjeta "Vehículos" existente.

### Requerimiento 9: Interfaz de gestión de Testimonios en el Sistema_Frontend

**Historia de Usuario:** Como administrador de marketing, quiero una página dentro del Módulo_Marketing para gestionar los testimonios de Success Day, para subir imágenes, reordenar y activar/desactivar testimonios.

#### Criterios de Aceptación

1. THE Sistema_Frontend SHALL proveer una página de gestión de Testimonios accesible desde la ruta `/admin/marketing/home-testimonials` dentro del Módulo_Marketing.
2. THE Sistema_Frontend SHALL mostrar la lista de Testimonios en un grid visual con la imagen, texto alternativo, estado activo/inactivo y acciones (eliminar, toggle).
3. WHEN el usuario arrastra y suelta un Testimonio en el grid, THE Sistema_Frontend SHALL enviar la solicitud de reordenamiento al Sistema_Backend usando el endpoint sort_update.
4. WHEN el usuario hace clic en el botón de subir Testimonio, THE Sistema_Frontend SHALL mostrar un formulario con campos para: imagen y texto alternativo (alt).
5. WHEN el usuario hace clic en el toggle de estado, THE Sistema_Frontend SHALL enviar la solicitud al endpoint toggle y actualizar la vista.
6. THE Sistema_Frontend SHALL agregar una tarjeta de navegación "Testimonios" en el dashboard del Módulo_Marketing, siguiendo el patrón de la tarjeta "Vehículos" existente.

### Requerimiento 10: Actualización de la Página de Inicio PHP

**Historia de Usuario:** Como desarrollador, quiero que la Página_Inicio consuma datos de la API del Sistema_Backend en lugar de archivos JSON locales, para que los cambios realizados desde el panel de administración se reflejen en el sitio público.

#### Criterios de Aceptación

1. THE Página_Inicio SHALL obtener los slides activos mediante una solicitud HTTP a la API_Pública del Sistema_Backend en lugar de leer el archivo slides.json.
2. THE Página_Inicio SHALL obtener los testimonios activos mediante una solicitud HTTP a la API_Pública del Sistema_Backend en lugar de leer el archivo testimonials.json.
3. THE Página_Inicio SHALL renderizar las imágenes de slides usando las URLs de CloudFront almacenadas en desktop_image_path y mobile_image_path.
4. THE Página_Inicio SHALL renderizar las imágenes de testimonios usando las URLs de CloudFront almacenadas en image_path.
5. IF la API_Pública no responde o retorna un error, THEN THE Página_Inicio SHALL mostrar las secciones de slides y testimonios vacías sin interrumpir la carga del resto de la página.
6. THE Página_Inicio SHALL almacenar la URL base de la API en una variable de configuración para facilitar el cambio entre entornos.
