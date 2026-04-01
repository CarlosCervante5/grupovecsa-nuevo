# 📝 Registro de Cambios - WooCommerce Auto Discount

## [1.0.2] - 2025-08-15

### 🎉 Nuevo
- **Soporte Completo para Productos Variables**
  - Los descuentos se aplican a todas las variaciones del producto
  - Actualización de precio en tiempo real al seleccionar variación
  - Precio original tachado y precio con descuento destacado
  - Mensaje informativo mostrando el ahorro al seleccionar variación
  - Animación suave al cambiar entre variaciones
  - JavaScript optimizado para detectar cambios de variación
  
- **Estilos para Productos Variables**
  - Contenedor de precio con diseño destacado
  - Animación de "highlight" cuando cambia el precio
  - Badge con animación "pulse" en productos variables
  - Selectores de variación mejorados con focus highlight
  - Mensaje de descuento con animación de entrada
  - Diseño responsive para móviles

### 📚 Documentación
- Agregado **PRODUCTOS-VARIABLES.md** con guía completa
- Ejemplos prácticos de uso con productos variables
- Casos de uso y mejores prácticas
- Solución de problemas específicos de variaciones

## [1.0.1] - 2025-08-15

### ✅ Corregido
- **Selectores Múltiples Mejorados**
  - Agregado atributo `size="10"` para mejor visualización
  - Mensajes de ayuda más claros sobre cómo seleccionar múltiples opciones
  - Contador dinámico de categorías/etiquetas seleccionadas
  - Mensajes informativos cuando no hay opciones disponibles

- **Botón de Guardar Mejorado**
  - Botón más grande y visible con icono 💾
  - Estilos mejorados con hover y efectos de transición
  - Diseño responsive para móviles (100% width)
  - Posicionamiento claro al final del formulario

- **Sección de Resumen**
  - Nuevo panel de resumen antes de guardar
  - Muestra estado actual de la configuración
  - Advertencias visuales si falta configuración
  - Colores diferenciados para estados activo/inactivo

- **Valores por Defecto**
  - Agregado `wp_parse_args` para asegurar que todas las opciones tengan valores
  - Previene errores cuando el plugin se activa por primera vez
  - Garantiza que los arrays de categorías/etiquetas existan

- **Estilos Mejorados**
  - Selectores múltiples con padding y bordes mejorados
  - Opciones con hover highlight
  - Cajas de información con colores diferenciados (info, warning, success)
  - Mejor espaciado y tipografía

### 📚 Documentación
- Agregado **GUIA-USO.md** con instrucciones detalladas paso a paso
- Ejemplos prácticos de configuración
- Preguntas frecuentes (FAQ)
- Solución de problemas comunes

## [1.0.0] - 2025-08-15

### 🎉 Lanzamiento Inicial

#### Características Principales
- **Descuentos Automáticos**
  - Descuentos por porcentaje (%)
  - Descuentos por monto fijo ($)
  - Aplicación automática en el carrito

- **Filtros de Productos**
  - Selección múltiple de categorías
  - Selección múltiple de etiquetas
  - Toggle entre categoría y etiqueta

- **Badges Personalizables**
  - 3 estilos: Moderno, Clásico, Minimalista
  - Texto personalizable
  - Colores personalizables (fondo y texto)
  - Mostrar/ocultar porcentaje de descuento
  - Vista previa en tiempo real

- **Visualización de Precios**
  - Precio original tachado
  - Precio con descuento destacado
  - Compatible con todos los temas de WooCommerce

- **Panel de Administración**
  - Interfaz intuitiva con tarjetas organizadas
  - Color pickers para badges
  - Toggles para activar/desactivar funciones
  - Validación de formulario con JavaScript

#### Compatibilidad
- WordPress 5.8+
- WooCommerce 6.0+
- PHP 7.4+
- Compatible con HPOS (High-Performance Order Storage)

#### Archivos Incluidos
- `woo-auto-discount.php` - Archivo principal del plugin
- `templates/admin-settings.php` - Template de configuración
- `assets/css/admin.css` - Estilos del panel de administración
- `assets/css/frontend.css` - Estilos de badges para la tienda
- `assets/js/admin.js` - JavaScript para interactividad
- `README.md` - Documentación técnica
- `GUIA-USO.md` - Guía de uso paso a paso
- `CHANGELOG.md` - Este archivo

---

## 🔜 Próximas Características Planeadas

### [1.1.0] - Futuro
- [ ] Programación de descuentos (fecha inicio/fin)
- [ ] Descuentos por horarios específicos
- [ ] Límite de uso por descuento
- [ ] Descuentos acumulativos
- [ ] Exportar/Importar configuración

### [1.2.0] - Futuro
- [ ] Descuentos por rol de usuario
- [ ] Descuentos por cantidad (compra X, descuento Y)
- [ ] Cupones automáticos generados
- [ ] Notificaciones por email de descuentos aplicados
- [ ] Estadísticas de descuentos aplicados

### [1.3.0] - Futuro
- [ ] Múltiples reglas de descuento simultáneas
- [ ] Descuentos por productos específicos
- [ ] Descuentos por variaciones
- [ ] Descuentos progresivos (más compras = más descuento)
- [ ] API REST para integración externa

---

## 📋 Notas de Versión

### Formato del Changelog
Este changelog sigue las convenciones de [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/).

### Tipos de Cambios
- **✅ Corregido** - Corrección de bugs
- **🎉 Agregado** - Nuevas características
- **🔄 Cambiado** - Cambios en funcionalidad existente
- **⚠️ Deprecado** - Características que serán removidas
- **🗑️ Removido** - Características removidas
- **🔒 Seguridad** - Parches de seguridad

---

## 🤝 Contribuciones

Si encuentras un bug o tienes una sugerencia, por favor:
1. Crea un issue en el repositorio
2. Describe detalladamente el problema o sugerencia
3. Incluye capturas de pantalla si es posible
4. Especifica tu versión de WordPress, WooCommerce y PHP

---

## 📄 Licencia

GPL v2 or later - Ver LICENSE para más detalles.


