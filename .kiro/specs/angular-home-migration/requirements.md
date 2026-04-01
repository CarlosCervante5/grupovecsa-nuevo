# Documento de Requisitos: Migración del Home PHP a Angular

## Introducción

Este documento define los requisitos para migrar la página de inicio actual (`home/index.php`) — un sitio PHP standalone con Tailwind CSS — hacia componentes Angular dentro de la aplicación SPA `vecsa-frontend`. La migración replica fielmente el diseño visual y la funcionalidad interactiva del home PHP (hero slider, marcas, servicios, carrusel de testimonios, sucursales, disclaimer, footer, botones flotantes) usando componentes Angular standalone, un servicio público sin autenticación, y la infraestructura existente del proyecto Angular.

## Glosario

- **HomeComponent**: Componente Angular orquestador que carga datos de la API y los distribuye a los sub-componentes via `@Input()`
- **HeroSliderComponent**: Componente Angular que renderiza el slider de imágenes hero con autoplay, navegación y soporte touch
- **BrandsComponent**: Componente Angular que muestra el grid de logos de marcas con enlaces externos
- **ServicesSectionComponent**: Componente Angular que muestra el grid de tarjetas de servicios con layout asimétrico
- **SuccessDayComponent**: Componente Angular que renderiza el carrusel horizontal de testimonios Success Day
- **LocationsComponent**: Componente Angular que muestra la lista de sucursales con filtros y mapa
- **DisclaimerComponent**: Componente Angular que muestra los avisos legales estáticos
- **HomeFooterComponent**: Componente Angular que renderiza el footer completo del home con columnas de enlaces
- **FloatingActionsComponent**: Componente Angular que muestra los botones flotantes de contacto (WhatsApp, teléfono, email)
- **HomePublicService**: Servicio Angular que consume los endpoints públicos de la API Laravel sin autenticación
- **HomeSlide**: Interfaz TypeScript existente que define la estructura de un slide del hero
- **HomeTestimonial**: Interfaz TypeScript existente que define la estructura de un testimonio
- **Angular_Router**: Sistema de enrutamiento de Angular que gestiona la navegación y lazy loading

## Requisitos

### Requisito 1: Carga de datos del home desde la API pública

**User Story:** Como usuario, quiero que la página de inicio cargue los slides y testimonios desde la API, para que vea contenido dinámico actualizado.

#### Criterios de Aceptación

1. WHEN el HomeComponent se inicializa, THE HomePublicService SHALL realizar peticiones HTTP POST paralelas a `/api/home/slides` y `/api/home/testimonials` sin incluir headers de autenticación
2. WHEN la API responde exitosamente, THE HomeComponent SHALL asignar los slides activos al array `slides` y los testimonios activos al array `testimonials`
3. IF la API de slides falla o no responde, THEN THE HomeComponent SHALL asignar un array vacío a `slides` y continuar la carga del resto de secciones
4. IF la API de testimonials falla o no responde, THEN THE HomeComponent SHALL asignar un array vacío a `testimonials` y continuar la carga del resto de secciones
5. WHILE los datos se están cargando, THE HomeComponent SHALL mantener el estado `isLoading` en `true` hasta que ambas peticiones completen o fallen

### Requisito 2: Hero Slider con autoplay y navegación

**User Story:** Como usuario, quiero ver un slider de imágenes hero que rote automáticamente y que pueda navegar manualmente, para descubrir las ofertas y promociones destacadas.

#### Criterios de Aceptación

1. WHEN el HeroSliderComponent recibe slides via `@Input()`, THE HeroSliderComponent SHALL iniciar un autoplay que cambia de slide cada 8000 milisegundos
2. WHEN el usuario hace click en la flecha siguiente o anterior, THE HeroSliderComponent SHALL detener el autoplay, navegar al slide correspondiente, y reiniciar el autoplay
3. WHEN el usuario hace hover sobre el slider, THE HeroSliderComponent SHALL detener el autoplay, y reiniciarlo cuando el cursor salga del área del slider
4. WHEN el usuario realiza un swipe horizontal con distancia mayor a 50 píxeles, THE HeroSliderComponent SHALL navegar al slide siguiente (swipe izquierda) o anterior (swipe derecha)
5. WHEN el slider está en el último slide y se invoca `nextSlide()`, THE HeroSliderComponent SHALL navegar al primer slide (índice 0)
6. WHEN el slider está en el primer slide y se invoca `prevSlide()`, THE HeroSliderComponent SHALL navegar al último slide
7. WHEN un slide está activo, THE HeroSliderComponent SHALL mostrar la imagen desktop en viewports de 768px o más y la imagen mobile en viewports menores a 768px
8. WHEN el array de slides está vacío, THE HeroSliderComponent SHALL ocultar la sección hero del DOM

### Requisito 3: Sección de marcas

**User Story:** Como usuario, quiero ver los logos de las marcas del grupo (BMW, MINI, Motorrad, Premium Selection, ABCars, Chevrolet), para acceder rápidamente al inventario de cada marca.

#### Criterios de Aceptación

1. THE BrandsComponent SHALL renderizar un grid de 6 logos de marcas con sus respectivos enlaces externos
2. WHEN el usuario hace click en un logo de marca, THE BrandsComponent SHALL abrir el enlace correspondiente en una nueva pestaña del navegador

### Requisito 4: Sección de servicios

**User Story:** Como usuario, quiero ver las tarjetas de servicios (Boutique, Rewards, Experience, Car Care, Promociones), para explorar las ofertas del grupo.

#### Criterios de Aceptación

1. THE ServicesSectionComponent SHALL renderizar un grid de 5 tarjetas de servicios donde la tarjeta "VECSA Experience" ocupa 2 columnas y 2 filas en viewports de escritorio
2. WHEN el usuario hace click en una tarjeta de servicio, THE ServicesSectionComponent SHALL abrir el enlace correspondiente en una nueva pestaña del navegador
3. WHILE el viewport es menor a 768px, THE ServicesSectionComponent SHALL mostrar las tarjetas en una sola columna con aspect ratio 4:3

### Requisito 5: Carrusel de testimonios Success Day

**User Story:** Como usuario, quiero ver un carrusel de fotos de testimonios Success Day, para conocer las experiencias de otros clientes.

#### Criterios de Aceptación

1. WHEN el SuccessDayComponent recibe testimonios via `@Input()`, THE SuccessDayComponent SHALL iniciar un autoplay que avanza el carrusel cada 5000 milisegundos
2. WHEN el viewport es de 768px o más, THE SuccessDayComponent SHALL mostrar 3 tarjetas visibles simultáneamente
3. WHEN el viewport es menor a 768px, THE SuccessDayComponent SHALL mostrar 1 tarjeta visible
4. WHEN el usuario hace click en las flechas de navegación, THE SuccessDayComponent SHALL desplazar el carrusel al grupo de tarjetas correspondiente
5. WHEN el carrusel alcanza el último grupo de tarjetas y se invoca `nextSlide()`, THE SuccessDayComponent SHALL regresar al inicio (índice 0)
6. WHEN el array de testimonios está vacío, THE SuccessDayComponent SHALL ocultar la sección Success Day del DOM
7. WHEN el usuario realiza un swipe horizontal, THE SuccessDayComponent SHALL navegar al grupo siguiente o anterior según la dirección del swipe
8. WHEN el viewport cambia de tamaño, THE SuccessDayComponent SHALL recalcular el número de tarjetas visibles y ajustar el índice actual si excede el nuevo máximo

### Requisito 6: Sección de sucursales con filtros

**User Story:** Como usuario, quiero ver la lista de sucursales del grupo con filtros por ubicación, para encontrar la agencia más cercana.

#### Criterios de Aceptación

1. THE LocationsComponent SHALL renderizar las 7 sucursales del grupo con nombre, dirección, teléfono e imagen
2. WHEN el usuario selecciona el filtro "Todas", THE LocationsComponent SHALL mostrar todas las sucursales
3. WHEN el usuario selecciona el filtro "Puebla", THE LocationsComponent SHALL mostrar solo las sucursales cuyo atributo `filter` sea `'puebla'`
4. WHEN el usuario selecciona el filtro "Otros", THE LocationsComponent SHALL mostrar solo las sucursales cuyo atributo `filter` sea `'otros'`
5. WHEN el usuario selecciona una sucursal, THE LocationsComponent SHALL mostrar la información detallada de esa sucursal
6. WHEN el filtro activo cambia y la sucursal seleccionada no pertenece al nuevo filtro, THE LocationsComponent SHALL deseleccionar la sucursal actual

### Requisito 7: Componentes estáticos (Disclaimer y Footer)

**User Story:** Como usuario, quiero ver la información legal y el footer completo del home, para acceder a enlaces de vehículos, servicios, legales y redes sociales.

#### Criterios de Aceptación

1. THE DisclaimerComponent SHALL renderizar los avisos legales estáticos (ofertas, disponibilidad, garantías, información legal)
2. THE HomeFooterComponent SHALL renderizar columnas de enlaces de vehículos, servicios, legales, redes sociales, y el año de copyright dinámico
3. WHEN el usuario navega a la ruta raíz del home, THE HomeComponent SHALL ocultar el footer global de la aplicación y mostrar el HomeFooterComponent

### Requisito 8: Botones flotantes de contacto

**User Story:** Como usuario, quiero tener acceso rápido a botones de contacto (WhatsApp, teléfono, email), para comunicarme fácilmente con el grupo.

#### Criterios de Aceptación

1. WHILE el viewport es de 768px o más, THE FloatingActionsComponent SHALL mostrar solo el botón de WhatsApp como botón flotante
2. WHILE el viewport es menor a 768px, THE FloatingActionsComponent SHALL mostrar los botones de WhatsApp, teléfono y email como botones flotantes

### Requisito 9: Enrutamiento y lazy loading

**User Story:** Como desarrollador, quiero que el módulo del home se cargue de forma lazy en la ruta raíz, para optimizar el tiempo de carga inicial de la aplicación.

#### Criterios de Aceptación

1. WHEN el usuario navega a la ruta raíz `'/'`, THE Angular_Router SHALL cargar el HomeComponent de forma lazy mediante `loadComponent`
2. THE HomeComponent SHALL ser un componente standalone que importa todos los sub-componentes necesarios

### Requisito 10: Limpieza de recursos y rendimiento

**User Story:** Como desarrollador, quiero que los componentes con timers limpien sus recursos al destruirse, para evitar memory leaks.

#### Criterios de Aceptación

1. WHEN el HeroSliderComponent se destruye, THE HeroSliderComponent SHALL limpiar todos los intervalos de autoplay activos
2. WHEN el SuccessDayComponent se destruye, THE SuccessDayComponent SHALL limpiar todos los intervalos de autoplay activos
3. THE HomeComponent SHALL usar `ChangeDetectionStrategy.OnPush` en los componentes estáticos (BrandsComponent, ServicesSectionComponent, DisclaimerComponent, HomeFooterComponent, FloatingActionsComponent) para reducir ciclos de detección de cambios
