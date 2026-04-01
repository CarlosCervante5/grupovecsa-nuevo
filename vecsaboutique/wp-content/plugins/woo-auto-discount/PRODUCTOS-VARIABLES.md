# 🔄 Soporte para Productos Variables - WooCommerce Auto Discount

## ¿Qué son los Productos Variables?

Los **productos variables** en WooCommerce son productos que tienen múltiples **variaciones** basadas en atributos como:
- Talla (S, M, L, XL)
- Color (Rojo, Azul, Verde)
- Material (Algodón, Poliéster, Seda)
- etc.

Cada variación puede tener su propio precio, stock y SKU.

## 🎯 Cómo Funciona el Descuento en Productos Variables

### 1️⃣ **Configuración**

El plugin aplica descuentos a productos variables de la misma manera que a productos simples:

1. Configura tu descuento en **"Descuentos Auto"**
2. Selecciona las categorías o etiquetas donde están tus productos variables
3. Guarda la configuración

### 2️⃣ **Visualización en la Tienda**

#### En la Lista de Productos (Shop):
```
┌─────────────────────────┐
│ 🏷️ ¡OFERTA! -20%       │ ← Badge visible
│                         │
│   [Imagen Camiseta]     │
│                         │
│   Camiseta Deportiva    │
│   $500 - $800           │ ← Rango de precios
│                         │
│   [Seleccionar opciones]│
└─────────────────────────┘
```

#### En la Página del Producto:

**Antes de seleccionar variación:**
```
🏷️ ¡OFERTA! -20%

Camiseta Deportiva
$500 - $800

Talla: [ Selecciona una opción ▼ ]
Color: [ Selecciona una opción ▼ ]

[ Limpiar ]
```

**Después de seleccionar variación:**
```
🏷️ ¡OFERTA! -20%

Camiseta Deportiva

Talla: M
Color: Rojo

┌──────────────────────────────────────┐
│ Precio:                              │
│ $500 → $400                         │ ← Precio con descuento
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│ 🎉 ¡Descuento aplicado!              │
│ Esta variación tiene un descuento    │
│ de 20% (Ahorras: $100)              │
└──────────────────────────────────────┘

Cantidad: [1] [Agregar al carrito]
```

### 3️⃣ **Características del Soporte para Variables**

#### ✅ **Actualización en Tiempo Real**
- El precio se actualiza **instantáneamente** cuando seleccionas una variación
- No necesitas recargar la página
- Animación suave al cambiar el precio

#### ✅ **Precio Tachado**
- Muestra el precio original tachado: ~~$500~~
- Muestra el precio con descuento en verde: **$400**
- Cálculo automático del ahorro

#### ✅ **Mensaje Informativo**
Cuando seleccionas una variación con descuento, aparece un mensaje azul que muestra:
- El porcentaje o monto del descuento
- El ahorro exacto en dinero
- Animación de entrada suave

#### ✅ **Aplicación Correcta en el Carrito**
- El descuento se aplica al precio de la variación específica
- Se respetan los precios individuales de cada variación
- El carrito muestra el descuento aplicado

### 4️⃣ **Ejemplo Práctico**

**Producto: Camiseta Deportiva**

**Variaciones:**
| Talla | Color | Precio Original | Con 20% Descuento |
|-------|-------|-----------------|-------------------|
| S     | Rojo  | $500           | $400              |
| M     | Rojo  | $600           | $480              |
| L     | Azul  | $700           | $560              |
| XL    | Verde | $800           | $640              |

**Flujo del Usuario:**

1. **Ve el producto** en la lista → Badge "¡OFERTA! -20%" visible
2. **Hace clic** en el producto
3. **Selecciona** Talla: M, Color: Rojo
4. **Ve el precio actualizado**:
   - ~~$600~~ → **$480**
   - Mensaje: "🎉 ¡Descuento aplicado! Esta variación tiene un descuento de 20% (Ahorras: $120)"
5. **Agrega al carrito** → El carrito muestra $480

## 🎨 **Estilos Visuales**

### Contenedor de Precio de Variación
- Fondo gris claro con borde verde a la izquierda
- Animación cuando cambia el precio (destello azul)
- Padding cómodo para lectura

### Mensaje de Descuento
- Fondo azul claro con borde azul a la izquierda
- Texto en negrita para el porcentaje/monto
- Animación de entrada desde arriba
- Se oculta automáticamente al limpiar selección

### Selectores de Variación
- Bordes redondeados
- Focus highlight en azul
- Padding amplio para fácil selección
- Responsive (100% width en móvil)

## 🔧 **Cómo Funciona Técnicamente**

### PHP (Backend)
```php
// El plugin intercepta las variaciones
add_filter('woocommerce_available_variation', 'add_variation_discount_data');

// Calcula el descuento para cada variación
$discount = calculate_discount($variation_price);

// Agrega datos personalizados al JSON de la variación
$variation_data['woo_auto_discount_enabled'] = true;
$variation_data['woo_auto_discount_amount'] = $discount;
```

### JavaScript (Frontend)
```javascript
// Detecta cuando se selecciona una variación
$('form.variations_form').on('found_variation', function(event, variation) {
    // Actualiza el precio visualmente
    // Muestra mensaje de descuento
    // Agrega animaciones
});
```

## 📱 **Responsive Design**

### En Escritorio:
- Selectores de variación: 200px mínimo
- Mensaje de descuento: padding completo
- Precio grande y legible (1.25em)

### En Tablet:
- Selectores mantienen tamaño
- Precio ajustado (1.1em)

### En Móvil:
- Selectores: 100% width
- Mensaje de descuento: padding reducido
- Precio optimizado para pantalla pequeña
- Badge más pequeño (10px font)

## 🎯 **Casos de Uso**

### Caso 1: Descuento en todas las tallas
```
Configuración:
- Descuento: 20%
- Aplicar a: Categoría "Ropa"

Resultado:
- Todas las variaciones de productos en "Ropa" tienen 20% off
- Cada variación muestra su precio con descuento
```

### Caso 2: Oferta en productos con etiqueta "Liquidación"
```
Configuración:
- Descuento: $50
- Aplicar a: Etiqueta "Liquidación"

Resultado:
- Todos los productos variables etiquetados tienen $50 menos
- Aplica a todas las variaciones del producto
```

### Caso 3: Black Friday
```
Configuración:
- Descuento: 30%
- Aplicar a: Múltiples categorías
- Badge: "BLACK FRIDAY -30%"

Resultado:
- Badge llamativo en todos los productos
- Precios actualizados en tiempo real
- Mensaje de ahorro en cada variación
```

## 🐛 **Solución de Problemas**

### El precio no se actualiza al seleccionar variación

**Solución:**
1. Limpia caché del navegador (Ctrl+F5)
2. Verifica que WooCommerce esté actualizado (6.0+)
3. Desactiva plugins de caché temporalmente
4. Verifica consola de JavaScript (F12) para errores

### El descuento no se aplica a algunas variaciones

**Solución:**
1. Verifica que el producto padre esté en la categoría/etiqueta seleccionada
2. Asegúrate de que la variación tenga precio regular configurado
3. Verifica en la configuración que los descuentos estén activados

### El badge no aparece en productos variables

**Solución:**
1. El badge se muestra si el producto padre califica
2. Verifica que el producto padre tenga la categoría/etiqueta correcta
3. Revisa que "Mostrar Badge" esté activado en configuración

### El mensaje de descuento no desaparece al cambiar variación

**Solución:**
1. El mensaje se actualiza automáticamente
2. Si persiste, recarga la página
3. Verifica que jQuery esté cargado correctamente

## 💡 **Consejos y Mejores Prácticas**

### ✅ **Hacer:**
- Configura precios regulares en todas las variaciones
- Usa badges llamativos para productos variables destacados
- Prueba el descuento en diferentes variaciones antes de publicar
- Verifica la visualización en móvil y escritorio

### ❌ **Evitar:**
- No dejes variaciones sin precio regular
- No uses descuentos mayores al precio del producto
- No configures descuentos en WooCommerce Y en el plugin a la vez
- No olvides verificar que el producto padre esté correctamente categorizado

## 🎉 **Ventajas del Sistema**

1. **Sin Configuración Extra**: Funciona automáticamente con productos variables
2. **Actualización Instantánea**: JavaScript rápido y eficiente
3. **UX Mejorada**: Mensajes claros sobre el descuento
4. **Visual Atractivo**: Animaciones suaves y diseño moderno
5. **Compatible**: Funciona con temas estándar de WooCommerce
6. **Responsive**: Se ve bien en todos los dispositivos
7. **Performance**: Código optimizado sin impacto en velocidad

## 📊 **Compatibilidad**

| Característica | Compatible |
|----------------|-----------|
| Productos simples | ✅ |
| Productos variables | ✅ |
| Productos agrupados | ❌ (no implementado) |
| Productos externos | ❌ (no aplica) |
| Suscripciones | ⚠️ (no probado) |
| Productos virtuales | ✅ |
| Productos descargables | ✅ |

## 🔜 **Próximas Mejoras**

- [ ] Soporte para productos agrupados
- [ ] Mostrar rango de precios con descuento en lista
- [ ] Tabla de variaciones con descuentos
- [ ] Descuentos por variación específica (no solo producto padre)
- [ ] Widget de "Ahorras hasta X% eligiendo esta variación"

---

¿Tienes preguntas o sugerencias? ¡Crea un issue en el repositorio! 🚀

