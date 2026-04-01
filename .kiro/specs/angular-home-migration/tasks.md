# Plan de Implementación: Migración del Home PHP a Angular

## Resumen

Migrar la página de inicio PHP (`home/index.php`) a componentes Angular standalone dentro de `vecsa-frontend`. Se crean 8 sub-componentes, un servicio público sin autenticación, y una ruta lazy-loaded en `'/'`. Se reutilizan las interfaces existentes (`HomeSlide`, `HomeTestimonial`) y la infraestructura Angular (Tailwind CSS, Angular Material, CDK).

## Tareas

- [x] 1. Preparar assets e infraestructura base
  - [x] 1.1 Copiar imágenes estáticas del home PHP a Angular
    - Copiar las imágenes de `home/assets/images/` (logos de marcas, imágenes de servicios, sucursales, fondos) a `vecsa-frontend/src/assets/images/home/`
    - No copiar las carpetas `slides/` ni `testimonials/` (esas imágenes vienen de la API)
    - _Requisitos: 3.1, 4.1, 6.1_
  - [x] 1.2 Crear el servicio HomePublicService
    - Crear `vecsa-frontend/src/app/home/services/home-public.service.ts`
    - Implementar `getSlides()` y `getTestimonials()` usando HTTP POST sin headers de autenticación
    - Reutilizar las interfaces `HomeSlidesResponse`, `HomeTestimonialsResponse`, `HomeSlide`, `HomeTestimonial` de `@interfaces/admin.interfaces`
    - Usar `environment.baseUrl` para la URL base
    - _Requisitos: 1.1_
  - [ ]* 1.3 Escribir tests unitarios para HomePublicService
    - Verificar que `getSlides()` hace POST a `/api/home/slides` sin header Authorization
    - Verificar que `getTestimonials()` hace POST a `/api/home/testimonials` sin header Authorization
    - Verificar el mapping correcto de la respuesta de la API
    - **Propiedad 1: Servicio público no envía autenticación**
    - **Valida: Requisitos 1.1**

- [x] 2. Implementar HomeComponent orquestador y enrutamiento
  - [x] 2.1 Crear el HomeComponent orquestador
    - Crear `vecsa-frontend/src/app/home/home.component.ts`, `.html`, `.css`
    - Componente standalone que importa todos los sub-componentes
    - Inyectar `HomePublicService` y usar `forkJoin` para carga paralela de slides y testimonials
    - Manejar estados de carga (`isLoading`) y error (arrays vacíos como fallback)
    - Distribuir datos a sub-componentes via `@Input()`
    - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5_
  - [x] 2.2 Configurar la ruta lazy-loaded en app-routing
    - Modificar `vecsa-frontend/src/app/app-routing.module.ts`
    - Cambiar la ruta `''` de `ComprarAutosModule` a `loadComponent` del `HomeComponent`
    - Mover `ComprarAutosModule` a la ruta `'compra-tu-auto'`
    - _Requisitos: 9.1, 9.2_
  - [x] 2.3 Ocultar el footer global en la ruta home
    - Modificar `app.component.ts` y `app.component.html` para detectar la ruta activa
    - Ocultar `<app-footer>` cuando la ruta sea `'/'` (el home tiene su propio footer)
    - _Requisitos: 7.3_
  - [ ]* 2.4 Escribir tests unitarios para HomeComponent
    - Verificar que `forkJoin` se ejecuta en `ngOnInit`
    - Verificar que errores de API resultan en arrays vacíos y `isLoading = false`
    - **Propiedad 2: Mapeo correcto de datos de la API**
    - **Propiedad 3: Carga de datos resiliente**
    - **Valida: Requisitos 1.2, 1.3, 1.4, 1.5**

- [ ] 3. Implementar HeroSliderComponent
  - [x] 3.1 Crear el componente HeroSliderComponent
    - Crear `vecsa-frontend/src/app/home/components/hero-slider/hero-slider.component.ts`, `.html`, `.css`
    - Componente standalone con `@Input() slides: HomeSlide[]`
    - Implementar autoplay cada 8000ms con pausa en hover
    - Implementar navegación por flechas, indicadores (dots), teclado (ArrowLeft/ArrowRight)
    - Implementar soporte touch/swipe con umbral de 50px
    - Renderizar imágenes responsive (desktop/mobile según viewport 768px)
    - Transiciones de opacidad con CSS
    - Ocultar sección si `slides` está vacío
    - Limpiar intervalos en `ngOnDestroy()`
    - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 10.1_
  - [ ]* 3.2 Escribir tests unitarios para HeroSliderComponent
    - Verificar navegación circular (next desde último → primero, prev desde primero → último)
    - Verificar autoplay start/stop
    - Verificar detección de swipe por umbral de 50px
    - **Propiedad 4: Navegación circular y índice válido del slider**
    - **Propiedad 5: Detección de swipe por umbral**
    - **Propiedad 10: Limpieza de timers al destruir componentes**
    - **Valida: Requisitos 2.4, 2.5, 2.6, 10.1**

- [ ] 4. Checkpoint - Verificar carga de datos y slider hero
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

- [x] 5. Implementar BrandsComponent y ServicesSectionComponent
  - [x] 5.1 Crear el componente BrandsComponent
    - Crear `vecsa-frontend/src/app/home/components/brands/brands.component.ts`, `.html`
    - Componente standalone con `ChangeDetectionStrategy.OnPush`
    - Grid de 6 logos de marcas con datos hardcoded (nombre, logo, URL)
    - Imágenes desde `assets/images/home/`
    - Enlaces externos con `target="_blank"`
    - _Requisitos: 3.1, 3.2, 10.3_
  - [x] 5.2 Crear el componente ServicesSectionComponent
    - Crear `vecsa-frontend/src/app/home/components/services-section/services-section.component.ts`, `.html`, `.css`
    - Componente standalone con `ChangeDetectionStrategy.OnPush`
    - Grid de 5 tarjetas de servicios con layout asimétrico (Experience 2x2)
    - Datos hardcoded de servicios (título, descripción, imagen, URL, buttonText, span)
    - Imágenes desde `assets/images/home/`
    - Responsive: columna única en mobile, grid asimétrico en desktop
    - _Requisitos: 4.1, 4.2, 4.3, 10.3_

- [x] 6. Implementar SuccessDayComponent
  - [x] 6.1 Crear el componente SuccessDayComponent
    - Crear `vecsa-frontend/src/app/home/components/success-day/success-day.component.ts`, `.html`, `.css`
    - Componente standalone con `@Input() testimonials: HomeTestimonial[]`
    - Carrusel horizontal con autoplay cada 5000ms
    - 3 tarjetas visibles en desktop (≥768px), 1 en mobile (<768px)
    - Navegación por flechas, dots, y touch/swipe
    - Wrap circular: next desde el máximo regresa a 0
    - Recalcular al cambiar viewport (window resize)
    - Ocultar sección si `testimonials` está vacío
    - Limpiar intervalos en `ngOnDestroy()`
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8, 10.2_
  - [ ]* 6.2 Escribir tests unitarios para SuccessDayComponent
    - Verificar `getVisibleCards()` retorna 3 en desktop y 1 en mobile
    - Verificar `getMaxIndex()` calcula correctamente
    - Verificar navegación circular del carrusel
    - **Propiedad 6: Tarjetas visibles del carrusel por viewport**
    - **Propiedad 7: Índice del carrusel respeta límites con wrap circular**
    - **Valida: Requisitos 5.2, 5.3, 5.5, 5.8**

- [x] 7. Implementar LocationsComponent
  - [x] 7.1 Crear el componente LocationsComponent
    - Crear `vecsa-frontend/src/app/home/components/locations/locations.component.ts`, `.html`, `.css`
    - Componente standalone con 7 sucursales hardcoded
    - Filtros: Todas, Puebla, Otros (tabs en desktop, select en mobile)
    - Selección de sucursal con información detallada
    - Deselección automática al cambiar filtro si la sucursal no pertenece al nuevo filtro
    - Mapa con imagen estática o iframe de Google Maps
    - _Requisitos: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_
  - [ ]* 7.2 Escribir tests unitarios para LocationsComponent
    - Verificar filtrado correcto por categoría
    - Verificar deselección al cambiar filtro
    - **Propiedad 8: Filtrado de sucursales correcto**
    - **Propiedad 9: Deselección de sucursal al cambiar filtro**
    - **Valida: Requisitos 6.2, 6.3, 6.4, 6.6**

- [ ] 8. Checkpoint - Verificar secciones principales
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

- [x] 9. Implementar componentes estáticos y flotantes
  - [x] 9.1 Crear el componente DisclaimerComponent
    - Crear `vecsa-frontend/src/app/home/components/disclaimer/disclaimer.component.ts`, `.html`
    - Componente standalone con `ChangeDetectionStrategy.OnPush`
    - Contenido estático de avisos legales migrado del PHP
    - _Requisitos: 7.1, 10.3_
  - [x] 9.2 Crear el componente HomeFooterComponent
    - Crear `vecsa-frontend/src/app/home/components/home-footer/home-footer.component.ts`, `.html`
    - Componente standalone con `ChangeDetectionStrategy.OnPush`
    - Columnas de enlaces: vehículos, servicios, legales, redes sociales
    - Año de copyright dinámico con `new Date().getFullYear()`
    - _Requisitos: 7.2, 10.3_
  - [x] 9.3 Crear el componente FloatingActionsComponent
    - Crear `vecsa-frontend/src/app/home/components/floating-actions/floating-actions.component.ts`, `.html`
    - Componente standalone con `ChangeDetectionStrategy.OnPush`
    - Solo WhatsApp en desktop (≥768px), WhatsApp + teléfono + email en mobile (<768px)
    - Botones flotantes posicionados en esquina inferior derecha
    - _Requisitos: 8.1, 8.2, 10.3_

- [x] 10. Integración final y cableado
  - [x] 10.1 Integrar todos los sub-componentes en HomeComponent
    - Actualizar `home.component.html` con todos los sub-componentes en orden
    - Pasar `slides` y `testimonials` via `@Input()` a los componentes que los necesitan
    - Condicionar renderizado de HeroSlider y SuccessDay según datos disponibles
    - Verificar que el footer global se oculta y el HomeFooterComponent se muestra
    - _Requisitos: 1.2, 7.3, 9.2_
  - [ ]* 10.2 Escribir tests de integración del HomeComponent
    - Verificar que la ruta `'/'` carga el HomeComponent
    - Verificar que todos los sub-componentes se renderizan
    - Verificar que el footer global se oculta en la ruta home
    - _Requisitos: 7.3, 9.1, 9.2_

- [ ] 11. Checkpoint final - Verificar migración completa
  - Asegurar que todos los tests pasan, preguntar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requisitos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- El proyecto usa archivos `.css` (no `.scss`) según la convención existente de los componentes
- Las interfaces `HomeSlide`, `HomeTestimonial`, `HomeSlidesResponse`, `HomeTestimonialsResponse` ya existen en `admin.interfaces.ts`
- Los tests usan Jasmine (framework de testing del proyecto)
