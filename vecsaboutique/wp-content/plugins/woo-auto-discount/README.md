# WooCommerce Auto Discount

Plugin de WordPress/WooCommerce que aplica descuentos automáticos a productos por categoría o etiqueta y muestra badges de oferta personalizables.

## 🚀 Características

- ✅ **Descuentos Automáticos** - Aplica descuentos por porcentaje o monto fijo
- ✅ **Filtros Flexibles** - Selecciona productos por categoría o etiqueta
- ✅ **Badges Personalizables** - 3 estilos diferentes (Moderno, Clásico, Minimalista)
- ✅ **Vista de Precios** - Muestra precio original tachado y precio con descuento
- ✅ **Aplicación en Carrito** - Descuento aplicado automáticamente al agregar al carrito
- ✅ **Compatible con HPOS** - Compatible con High-Performance Order Storage de WooCommerce
- ✅ **Interfaz Intuitiva** - Panel de administración fácil de usar
- ✅ **Vista Previa en Tiempo Real** - Ve cómo se verá tu badge antes de guardar

## 📋 Requisitos

- WordPress 5.8 o superior
- WooCommerce 6.0 o superior
- PHP 7.4 o superior

## 📦 Instalación

1. Descarga el plugin o clona este repositorio
2. Sube la carpeta `woo-auto-discount` a `/wp-content/plugins/`
3. Activa el plugin desde el menú "Plugins" en WordPress
4. Ve a **Descuentos Auto** en el menú de administración

## ⚙️ Configuración

### Configuración General

1. **Habilitar Descuentos**: Activa o desactiva los descuentos automáticos

### Configuración de Descuentos

1. **Tipo de Descuento**: Selecciona entre:
   - **Porcentaje (%)**: Ej: 10% de descuento
   - **Monto Fijo ($)**: Ej: $50 de descuento

2. **Valor del Descuento**: Ingresa el valor numérico del descuento

### Aplicar Descuento a

1. **Aplicar por**: Selecciona entre:
   - **Categoría**: Aplica a productos en categorías específicas
   - **Etiqueta**: Aplica a productos con etiquetas específicas

2. **Seleccionar Categorías/Etiquetas**: Elige múltiples categorías o etiquetas

### Configuración del Badge

1. **Mostrar Badge**: Activa para mostrar el badge en los productos
2. **Texto del Badge**: Personaliza el texto (ej: "¡OFERTA!", "SALE", "DESCUENTO")
3. **Mostrar Monto de Descuento**: Muestra el % o monto en el badge
4. **Estilo del Badge**: Elige entre:
   - **Moderno**: Bordes redondeados con sombra
   - **Clásico**: Bordes rectos con borde de color
   - **Minimalista**: Diseño simple y limpio
5. **Color del Badge**: Personaliza el color de fondo
6. **Color del Texto**: Personaliza el color del texto

## 🎨 Estilos de Badge

### Moderno
```css
border-radius: 20px;
box-shadow: 0 2px 8px rgba(0,0,0,0.15);
```

### Clásico
```css
border-radius: 0;
border: 2px solid currentColor;
```

### Minimalista
```css
border-radius: 2px;
font-weight: normal;
letter-spacing: 1px;
```

## 🔧 Funciones del Plugin

### Aplicación de Descuentos

El plugin aplica descuentos automáticamente cuando:
1. Un producto califica (categoría o etiqueta seleccionada)
2. El producto se agrega al carrito
3. El descuento se calcula y se muestra como una tarifa negativa

### Visualización de Precios

- Muestra el precio original tachado
- Muestra el precio con descuento en verde
- Compatible con productos simples y variables

### Badges de Oferta

- Se muestran en la esquina superior izquierda del producto
- Aparecen en:
  - Lista de productos (shop)
  - Página de producto individual
  - Widgets de productos
  - Productos relacionados

## 📱 Responsive

El plugin es totalmente responsive y se adapta a:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🎯 Casos de Uso

### Ejemplo 1: Descuento del 20% en Categoría "Electrónica"
```
Tipo: Porcentaje
Valor: 20
Aplicar a: Categoría
Categoría: Electrónica
Badge: ¡OFERTA! -20%
```

### Ejemplo 2: $50 de descuento en productos con etiqueta "Clearance"
```
Tipo: Monto Fijo
Valor: 50
Aplicar a: Etiqueta
Etiqueta: Clearance
Badge: SALE -$50
```

### Ejemplo 3: 15% en múltiples categorías
```
Tipo: Porcentaje
Valor: 15
Aplicar a: Categoría
Categorías: Ropa, Calzado, Accesorios
Badge: DESCUENTO -15%
```

## 🔍 Hooks y Filtros

### Filtros Disponibles

```php
// Modificar si un producto califica para descuento
apply_filters('woo_auto_discount_product_qualifies', $qualifies, $product_id);

// Modificar el monto del descuento
apply_filters('woo_auto_discount_calculate', $discount, $price, $product_id);

// Modificar el texto del badge
apply_filters('woo_auto_discount_badge_text', $badge_text, $product_id);
```

### Acciones Disponibles

```php
// Antes de aplicar el descuento
do_action('woo_auto_discount_before_apply', $cart);

// Después de aplicar el descuento
do_action('woo_auto_discount_after_apply', $cart, $discount_amount);
```

## 🐛 Solución de Problemas

### El badge no aparece
1. Verifica que "Mostrar Badge" esté activado
2. Asegúrate de que el producto esté en la categoría/etiqueta seleccionada
3. Limpia el caché del navegador y del plugin de caché

### El descuento no se aplica
1. Verifica que "Habilitar Descuentos" esté activado
2. Confirma que el producto califica según tu configuración
3. Revisa que el valor del descuento sea mayor a 0

### Los colores no se guardan
1. Asegúrate de hacer clic en "Guardar Cambios"
2. Verifica que no haya errores de JavaScript en la consola

## 📝 Changelog

### Version 1.0.0
- Lanzamiento inicial
- Descuentos por categoría y etiqueta
- Badges personalizables con 3 estilos
- Vista previa en tiempo real
- Compatible con HPOS

## 👨‍💻 Desarrollo

### Estructura de Archivos
```
woo-auto-discount/
├── woo-auto-discount.php      # Archivo principal
├── README.md                   # Documentación
├── assets/
│   ├── css/
│   │   ├── admin.css          # Estilos admin
│   │   └── frontend.css       # Estilos frontend
│   └── js/
│       └── admin.js           # JavaScript admin
└── templates/
    └── admin-settings.php     # Template de configuración
```

## 📄 Licencia

GPL v2 or later

## 🤝 Soporte

Para reportar bugs o solicitar características, por favor crea un issue en el repositorio.

## 🎉 Créditos

Desarrollado por [Tu Nombre]


