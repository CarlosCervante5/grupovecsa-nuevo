# 📖 Guía de Uso - WooCommerce Auto Discount

## 🚀 Inicio Rápido

### Paso 1: Activar el Plugin
1. Ve a **WordPress Admin** → **Plugins**
2. Busca "WooCommerce Auto Discount"
3. Haz clic en **Activar**

### Paso 2: Acceder a la Configuración
1. En el menú lateral de WordPress, busca **"Descuentos Auto"**
2. Haz clic para abrir el panel de configuración

## ⚙️ Configuración Detallada

### 1️⃣ Configuración General

**Habilitar Descuentos**
- **Toggle ON**: Los descuentos se aplicarán automáticamente
- **Toggle OFF**: Los descuentos están desactivados (útil para pausar temporalmente)

### 2️⃣ Configuración de Descuentos

**Tipo de Descuento**
- **Porcentaje (%)**: El descuento se calcula como % del precio
  - Ejemplo: 20% de descuento en un producto de $100 = $20 de descuento
- **Monto Fijo ($)**: Descuento de una cantidad fija
  - Ejemplo: $50 de descuento en cualquier producto

**Valor del Descuento**
- Ingresa solo números
- Ejemplos válidos: `10`, `20.5`, `15`, `25`
- **Importante**: Debe ser mayor a 0

### 3️⃣ Aplicar Descuento a

**Opción 1: Por Categoría**
1. Selecciona **"Categoría"** en el menú desplegable
2. En el selector múltiple, verás todas tus categorías de WooCommerce
3. **Para seleccionar múltiples categorías**:
   - **Windows**: Mantén presionado `Ctrl` y haz clic en cada categoría
   - **Mac**: Mantén presionado `Cmd` (⌘) y haz clic en cada categoría
4. Las categorías seleccionadas se resaltarán en azul
5. El contador mostrará cuántas categorías has seleccionado

**Opción 2: Por Etiqueta**
1. Selecciona **"Etiqueta"** en el menú desplegable
2. En el selector múltiple, verás todas tus etiquetas de WooCommerce
3. **Para seleccionar múltiples etiquetas**:
   - **Windows**: Mantén presionado `Ctrl` y haz clic en cada etiqueta
   - **Mac**: Mantén presionado `Cmd` (⌘) y haz clic en cada etiqueta
4. Las etiquetas seleccionadas se resaltarán en azul
5. El contador mostrará cuántas etiquetas has seleccionado

**💡 Consejos**:
- ✅ Puedes seleccionar múltiples categorías o etiquetas a la vez
- ✅ Solo una opción está activa (categoría O etiqueta, no ambas)
- ✅ El contador te ayuda a saber cuántas has seleccionado
- ⚠️ Si no seleccionas ninguna, el descuento no se aplicará

### 4️⃣ Configuración del Badge

**Mostrar Badge**
- **ON**: El badge aparecerá en los productos con descuento
- **OFF**: Los productos tendrán descuento pero sin badge visual

**Texto del Badge**
- Personaliza el texto que aparecerá
- Ejemplos:
  - `¡OFERTA!`
  - `SALE`
  - `DESCUENTO`
  - `PROMO`
  - `HOT DEAL`

**Mostrar Monto de Descuento**
- **ON**: Muestra el % o $ junto al texto
  - Ejemplo: "¡OFERTA! -20%" o "SALE -$50"
- **OFF**: Solo muestra el texto
  - Ejemplo: "¡OFERTA!" o "SALE"

**Estilo del Badge**
- **Moderno**: Bordes redondeados con sombra elegante
- **Clásico**: Bordes rectos con borde de color
- **Minimalista**: Diseño simple y limpio

**Colores**
- **Color del Badge**: Color de fondo del badge
- **Color del Texto**: Color del texto dentro del badge
- Haz clic en el selector de color para elegir
- **Vista Previa**: Verás cómo se ve en tiempo real

## 📋 Resumen de Configuración

Antes de guardar, verás un resumen que muestra:
- ✅ Estado (Activo/Inactivo)
- 💰 Valor del descuento configurado
- 🎯 Cuántas categorías o etiquetas seleccionaste
- 🏷️ Si el badge está visible

Si algo está mal configurado, verás una **advertencia en amarillo**.

## 💾 Guardar Cambios

1. Revisa el resumen de configuración
2. Haz clic en el botón azul grande **"💾 Guardar Cambios"**
3. Verás un mensaje de confirmación verde: "✅ Configuración guardada exitosamente!"
4. Los cambios se aplicarán de inmediato

## 🎯 Ejemplos Prácticos

### Ejemplo 1: 20% en Electrónica
```
✅ Habilitar: ON
📊 Tipo: Porcentaje
💯 Valor: 20
🎯 Aplicar a: Categoría → Electrónica
🏷️ Badge: ¡OFERTA! -20%
🎨 Color: Rojo (#ff0000)
```

**Resultado**: 
- Laptop de $1,000 → $800
- Badge rojo "¡OFERTA! -20%" en la esquina

### Ejemplo 2: $50 en productos con etiqueta "Clearance"
```
✅ Habilitar: ON
📊 Tipo: Monto Fijo
💰 Valor: 50
🎯 Aplicar a: Etiqueta → Clearance
🏷️ Badge: SALE -$50
🎨 Color: Verde (#00a32a)
```

**Resultado**: 
- Cualquier producto con etiqueta "Clearance" tiene $50 menos
- Badge verde "SALE -$50"

### Ejemplo 3: 15% en múltiples categorías
```
✅ Habilitar: ON
📊 Tipo: Porcentaje
💯 Valor: 15
🎯 Aplicar a: Categoría → Ropa, Calzado, Accesorios (3 categorías)
🏷️ Badge: DESCUENTO -15%
🎨 Color: Azul (#2271b1)
```

**Resultado**: 
- Todos los productos en esas 3 categorías tienen 15% de descuento
- Badge azul "DESCUENTO -15%"

## 🛒 Cómo se Ve en la Tienda

### En la Lista de Productos
```
┌─────────────────────┐
│ 🏷️ ¡OFERTA! -20%   │
│                     │
│  [Imagen Producto]  │
│                     │
│  Laptop HP          │
│  $799 → $639        │
│                     │
│  [Agregar al Cart]  │
└─────────────────────┘
```

### En el Carrito
```
Carrito de Compras
──────────────────────────
Laptop HP × 1 ........... $639.00
Descuento aplicado ....... -$160.00
──────────────────────────
Subtotal ................. $639.00
```

## ❓ Preguntas Frecuentes

**P: ¿Puedo aplicar descuentos a múltiples categorías?**
R: ¡Sí! Mantén presionado Ctrl (Windows) o Cmd (Mac) y selecciona todas las que quieras.

**P: ¿El descuento se aplica automáticamente?**
R: Sí, cuando un cliente agrega el producto al carrito, el descuento se aplica automáticamente.

**P: ¿Puedo cambiar los colores del badge?**
R: Sí, usa los selectores de color para personalizar el fondo y el texto.

**P: ¿Qué pasa si no selecciono ninguna categoría o etiqueta?**
R: El plugin te mostrará una advertencia y el descuento no se aplicará a ningún producto.

**P: ¿Puedo pausar los descuentos sin perder mi configuración?**
R: Sí, simplemente desactiva el toggle "Habilitar Descuentos". Tu configuración se guardará.

**P: ¿El badge aparece en todas partes?**
R: Sí, aparece en:
- Lista de productos (shop)
- Página de producto individual
- Widgets de productos
- Productos relacionados

## 🐛 Solución de Problemas

**El selector está vacío**
- Asegúrate de tener productos con categorías/etiquetas creadas
- Ve a Productos → Categorías para crear algunas

**No puedo seleccionar múltiples opciones**
- Asegúrate de mantener presionado Ctrl (Windows) o Cmd (Mac)
- Haz clic en cada opción una por una

**El botón de guardar no aparece**
- Desplázate hacia abajo, está al final del formulario
- Es un botón azul grande con el texto "💾 Guardar Cambios"

**Los cambios no se guardan**
- Verifica que hayas hecho clic en "Guardar Cambios"
- Busca el mensaje de confirmación verde
- Revisa que tu usuario tenga permisos de administrador

## 📞 Soporte

Si tienes problemas o preguntas adicionales, por favor contacta al desarrollador o revisa la documentación completa en README.md.


