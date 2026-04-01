# Documento de Requerimientos — Sistema de Atributos y Variantes de Producto

## Introducción

Sistema de atributos y variantes de producto estilo WooCommerce para la Boutique VECSA. Reemplaza el esquema fijo actual de variantes (color, talla) por un sistema flexible donde se definen atributos personalizados (ej. Color, Talla, Material, Estilo), cada uno con múltiples valores, y se generan combinaciones de variantes automáticamente (producto cartesiano). Cada variante resultante tiene su propio SKU, precio, stock y estado individual.

## Glosario

- **Sistema_Atributos**: Módulo backend (Laravel) que gestiona la creación, lectura, actualización y eliminación de atributos de producto y sus valores.
- **Atributo**: Característica configurable de un producto (ej. Color, Talla, Material). Se almacena en la tabla `boutique_product_attributes`.
- **Valor_Atributo**: Opción específica dentro de un atributo (ej. "Rojo" dentro de Color). Se almacena en la tabla `boutique_product_attribute_values`.
- **Variante**: Combinación única de valores de atributos asignados a un producto. Se almacena en `boutique_product_variants` con referencia a los valores de atributo que la componen.
- **Generador_Variantes**: Componente que calcula el producto cartesiano de los valores de atributos seleccionados para crear todas las combinaciones posibles de variantes.
- **Panel_Tienda**: Interfaz de administración Angular en `/admin/store` (StoreLayoutComponent) donde se gestionan productos, pedidos y configuración de la boutique.
- **Formulario_Producto**: Sección del Panel_Tienda dedicada a crear/editar productos, incluyendo la gestión de atributos y variantes.
- **Producto_Cartesiano**: Operación matemática que genera todas las combinaciones posibles entre conjuntos de valores de atributos.
- **Tabla_Pivote**: Tabla intermedia que relaciona variantes con sus valores de atributo (`boutique_variant_attribute_values`).

## Requerimientos

### Requerimiento 1: CRUD de Atributos de Producto

**Historia de Usuario:** Como administrador de la tienda, quiero crear y gestionar atributos personalizados de producto (ej. Color, Talla, Material), para poder definir las características variables de mis productos de forma flexible.

#### Criterios de Aceptación

1. THE Sistema_Atributos SHALL almacenar cada Atributo con los campos: id, uuid, name (único, máximo 100 caracteres), created_at, updated_at.
2. WHEN el administrador envía una solicitud de creación de Atributo con un nombre válido, THE Sistema_Atributos SHALL crear el Atributo y retornar el registro creado con código HTTP 201.
3. WHEN el administrador envía una solicitud de creación de Atributo con un nombre que ya existe, THE Sistema_Atributos SHALL rechazar la solicitud y retornar un error con código HTTP 400 y código de error `ATTRIBUTE_NAME_EXISTS`.
4. WHEN el administrador envía una solicitud de listado de Atributos, THE Sistema_Atributos SHALL retornar todos los Atributos existentes ordenados alfabéticamente por nombre, incluyendo sus Valores_Atributo asociados.
5. WHEN el administrador envía una solicitud de actualización de Atributo con un uuid válido y un nombre nuevo, THE Sistema_Atributos SHALL actualizar el nombre del Atributo y retornar el registro actualizado.
6. WHEN el administrador envía una solicitud de eliminación de Atributo que no está asignado a ningún producto, THE Sistema_Atributos SHALL eliminar el Atributo y todos sus Valores_Atributo asociados.
7. IF el administrador intenta eliminar un Atributo que está asignado a uno o más productos, THEN THE Sistema_Atributos SHALL rechazar la eliminación y retornar un error con código HTTP 409 y código de error `ATTRIBUTE_IN_USE`.

### Requerimiento 2: CRUD de Valores de Atributo

**Historia de Usuario:** Como administrador de la tienda, quiero agregar, editar y eliminar valores dentro de cada atributo (ej. Rojo, Azul, Negro dentro de Color), para definir las opciones disponibles para cada característica de producto.

#### Criterios de Aceptación

1. THE Sistema_Atributos SHALL almacenar cada Valor_Atributo con los campos: id, uuid, attribute_id (FK), value (máximo 100 caracteres), color_hex (nullable, máximo 20 caracteres), sort_order (entero, default 0), created_at, updated_at.
2. WHEN el administrador envía una solicitud de creación de Valor_Atributo con un attribute_uuid y un value válidos, THE Sistema_Atributos SHALL crear el Valor_Atributo asociado al Atributo correspondiente y retornar el registro con código HTTP 201.
3. WHEN el administrador envía una solicitud de creación de Valor_Atributo con un value que ya existe dentro del mismo Atributo, THE Sistema_Atributos SHALL rechazar la solicitud y retornar un error con código HTTP 400 y código de error `VALUE_ALREADY_EXISTS`.
4. WHEN el administrador envía una solicitud de actualización de Valor_Atributo con un uuid válido, THE Sistema_Atributos SHALL actualizar los campos proporcionados (value, color_hex, sort_order) y retornar el registro actualizado.
5. WHEN el administrador envía una solicitud de eliminación de Valor_Atributo que no está asociado a ninguna Variante, THE Sistema_Atributos SHALL eliminar el Valor_Atributo.
6. IF el administrador intenta eliminar un Valor_Atributo que está asociado a una o más Variantes existentes, THEN THE Sistema_Atributos SHALL rechazar la eliminación y retornar un error con código HTTP 409 y código de error `VALUE_IN_USE`.

### Requerimiento 3: Asignación de Atributos a Productos

**Historia de Usuario:** Como administrador de la tienda, quiero asignar atributos específicos a cada producto y seleccionar qué valores de esos atributos aplican, para definir las dimensiones de variación del producto.

#### Criterios de Aceptación

1. THE Sistema_Atributos SHALL mantener una relación muchos-a-muchos entre productos y atributos mediante la tabla pivote `boutique_product_attribute_product` con los campos: id, product_id (FK), attribute_id (FK).
2. WHEN el administrador asigna atributos a un producto enviando un arreglo de attribute_uuids y para cada atributo un arreglo de value_uuids seleccionados, THE Sistema_Atributos SHALL registrar las asociaciones producto-atributo y almacenar los valores seleccionados.
3. WHEN el administrador consulta los atributos de un producto, THE Sistema_Atributos SHALL retornar la lista de Atributos asignados al producto con sus Valores_Atributo seleccionados.
4. WHEN el administrador modifica los atributos asignados a un producto, THE Sistema_Atributos SHALL sincronizar las asociaciones, agregando las nuevas y eliminando las que ya no aplican.
5. IF el administrador elimina un atributo de un producto que tiene Variantes generadas con valores de ese atributo, THEN THE Sistema_Atributos SHALL advertir al administrador que las variantes afectadas serán eliminadas antes de proceder.

### Requerimiento 4: Generación Automática de Variantes (Producto Cartesiano)

**Historia de Usuario:** Como administrador de la tienda, quiero generar automáticamente todas las combinaciones posibles de variantes a partir de los valores de atributos seleccionados, para no tener que crear cada variante manualmente.

#### Criterios de Aceptación

1. WHEN el administrador solicita generar variantes para un producto con atributos y valores seleccionados, THE Generador_Variantes SHALL calcular el Producto_Cartesiano de todos los valores seleccionados de cada atributo asignado.
2. WHEN el Generador_Variantes calcula las combinaciones, THE Generador_Variantes SHALL crear una Variante por cada combinación única, con los campos: uuid, product_id, sku (auto-generado como `{product_sku}-{valor1}-{valor2}-...`), price (heredado del producto padre), stock (default 0), active (default true).
3. THE Generador_Variantes SHALL registrar en la Tabla_Pivote `boutique_variant_attribute_values` la relación entre cada Variante y los Valores_Atributo que la componen, con los campos: id, variant_id (FK), attribute_value_id (FK).
4. WHEN ya existen variantes previas para el producto, THE Generador_Variantes SHALL preservar las variantes cuya combinación de valores siga siendo válida (manteniendo su SKU, precio y stock personalizados) y eliminar las variantes cuya combinación ya no exista en la nueva selección.
5. IF el producto tiene un solo atributo con N valores seleccionados, THEN THE Generador_Variantes SHALL generar exactamente N variantes.
6. IF el producto tiene M atributos con N1, N2, ..., NM valores seleccionados respectivamente, THEN THE Generador_Variantes SHALL generar exactamente N1 × N2 × ... × NM variantes.
7. IF el número total de combinaciones supera 100, THEN THE Sistema_Atributos SHALL rechazar la generación y retornar un error con código HTTP 400 y mensaje indicando que el límite máximo de variantes por producto es 100.

### Requerimiento 5: Gestión Individual de Variantes

**Historia de Usuario:** Como administrador de la tienda, quiero editar individualmente el SKU, precio, stock y estado de cada variante generada, para poder personalizar cada combinación según las necesidades del negocio.

#### Criterios de Aceptación

1. WHEN el administrador consulta las variantes de un producto, THE Sistema_Atributos SHALL retornar la lista de Variantes con sus campos editables (sku, price, stock, active) y la descripción legible de la combinación de atributos (ej. "Rojo / M / Algodón").
2. WHEN el administrador actualiza los campos de una Variante individual (sku, price, stock, active), THE Sistema_Atributos SHALL validar y guardar los cambios, retornando la Variante actualizada.
3. WHEN el administrador actualiza el SKU de una Variante, THE Sistema_Atributos SHALL verificar que el nuevo SKU sea único entre todas las variantes activas del mismo producto.
4. IF el administrador envía un SKU duplicado dentro del mismo producto, THEN THE Sistema_Atributos SHALL rechazar la actualización y retornar un error con código HTTP 400 y código de error `VARIANT_SKU_DUPLICATE`.
5. WHEN el administrador desea agregar una variante manual sin usar el generador, THE Sistema_Atributos SHALL permitir crear una Variante individual especificando los valores de atributo, SKU, precio y stock.
6. WHEN el administrador elimina una Variante individual, THE Sistema_Atributos SHALL eliminar la Variante y sus registros en la Tabla_Pivote asociados.

### Requerimiento 6: Interfaz de Atributos y Variantes en el Formulario de Producto

**Historia de Usuario:** Como administrador de la tienda, quiero gestionar atributos y variantes directamente desde el formulario de edición/creación de producto en el Panel_Tienda, para tener un flujo integrado estilo WooCommerce.

#### Criterios de Aceptación

1. WHEN el administrador activa el toggle "Producto con variantes" en el Formulario_Producto, THE Panel_Tienda SHALL mostrar la sección de gestión de atributos con un selector para agregar atributos existentes o crear nuevos.
2. WHEN el administrador selecciona un Atributo, THE Panel_Tienda SHALL mostrar los Valores_Atributo disponibles como checkboxes o tags seleccionables, permitiendo también crear nuevos valores inline.
3. WHEN el administrador ha seleccionado al menos un atributo con al menos un valor, THE Panel_Tienda SHALL habilitar el botón "Generar variantes".
4. WHEN el administrador hace clic en "Generar variantes", THE Panel_Tienda SHALL enviar la solicitud al backend, recibir las variantes generadas y mostrarlas en una tabla editable.
5. THE Panel_Tienda SHALL mostrar cada Variante en una fila de tabla con columnas: combinación de atributos (texto legible), SKU (editable), precio (editable), stock (editable), activo (toggle), y un botón de eliminar.
6. WHEN el administrador modifica un campo de una Variante en la tabla, THE Panel_Tienda SHALL marcar la fila como modificada visualmente y habilitar un botón "Guardar cambios" para persistir las modificaciones.
7. WHEN el administrador desactiva el toggle "Producto con variantes", THE Panel_Tienda SHALL ocultar la sección de atributos y variantes y mostrar los campos de precio y stock a nivel de producto.
8. THE Panel_Tienda SHALL mostrar un resumen del número total de variantes generadas y el stock total acumulado de todas las variantes activas.

### Requerimiento 7: Migración de Datos y Compatibilidad

**Historia de Usuario:** Como administrador de la tienda, quiero que los productos existentes con variantes de color/talla fijas sigan funcionando correctamente después de la migración al nuevo sistema de atributos flexibles.

#### Criterios de Aceptación

1. THE Sistema_Atributos SHALL crear automáticamente los Atributos "Color" y "Talla" durante la migración si no existen.
2. WHEN la migración se ejecuta, THE Sistema_Atributos SHALL recorrer todas las variantes existentes en `boutique_product_variants` y crear los Valores_Atributo correspondientes (extrayendo los valores únicos de los campos `color` y `size`).
3. WHEN la migración crea Valores_Atributo para el atributo "Color", THE Sistema_Atributos SHALL preservar el campo `color_hex` de la variante original en el Valor_Atributo correspondiente.
4. WHEN la migración completa la creación de Valores_Atributo, THE Sistema_Atributos SHALL crear los registros en la Tabla_Pivote `boutique_variant_attribute_values` vinculando cada variante existente con sus valores de atributo correspondientes.
5. THE Sistema_Atributos SHALL agregar los campos `price` (decimal 10,2, nullable) a la tabla `boutique_product_variants` para permitir precio individual por variante.
6. WHILE la migración se ejecuta, THE Sistema_Atributos SHALL mantener los campos legacy (`color`, `color_hex`, `size`) en la tabla de variantes para compatibilidad, marcándolos como deprecados en el modelo.
7. IF la migración encuentra variantes con campos `color` o `size` vacíos o nulos, THEN THE Sistema_Atributos SHALL omitir esos campos y crear la variante solo con los atributos que tengan valor.

### Requerimiento 8: API de Catálogo Público con Atributos

**Historia de Usuario:** Como cliente de la boutique, quiero ver los atributos y valores disponibles de un producto en la página de detalle, para poder seleccionar la combinación deseada antes de agregar al carrito.

#### Criterios de Aceptación

1. WHEN el catálogo público solicita el detalle de un producto con variantes, THE Sistema_Atributos SHALL retornar los atributos del producto con sus valores disponibles y las variantes activas con su combinación de atributos, precio, stock y SKU.
2. WHEN el cliente selecciona valores de atributos en la página de detalle, THE Panel_Tienda SHALL filtrar las variantes disponibles para mostrar solo las que coincidan con la selección parcial o completa.
3. IF una combinación de atributos seleccionada corresponde a una variante con stock 0, THEN THE Panel_Tienda SHALL mostrar la variante como "Agotada" y deshabilitar el botón de agregar al carrito.
4. WHEN una variante tiene un precio individual definido, THE Sistema_Atributos SHALL retornar el precio de la variante en lugar del precio del producto padre.
5. WHEN una variante no tiene precio individual definido (null), THE Sistema_Atributos SHALL retornar el precio del producto padre como precio de la variante.
