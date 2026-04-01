# Plan de Implementación: Sistema de Atributos y Variantes de Producto

## Resumen

Implementación incremental del sistema de atributos dinámicos y generación de variantes por producto cartesiano. Se crean migraciones, modelos, controlador de atributos, se modifican controladores existentes, y se actualiza el frontend Angular para reemplazar el editor fijo de variantes por uno basado en atributos.

## Tareas

- [x] 1. Crear migraciones de base de datos
  - [x] 1.1 Crear migración para tabla `boutique_product_attributes`
    - Campos: id, uuid, name (unique, max 100), timestamps
    - _Requerimientos: 1.1_
  - [x] 1.2 Crear migración para tabla `boutique_product_attribute_values`
    - Campos: id, uuid, attribute_id (FK), value (max 100), color_hex (nullable, max 20), sort_order (default 0), timestamps
    - Restricción unique compuesta: (attribute_id, value)
    - _Requerimientos: 2.1_
  - [x] 1.3 Crear migración para tabla pivote `boutique_product_attribute_product`
    - Campos: id, product_id (FK cascade), attribute_id (FK cascade)
    - Restricción unique compuesta: (product_id, attribute_id)
    - _Requerimientos: 3.1_
  - [x] 1.4 Crear migración para tabla pivote `boutique_variant_attribute_values`
    - Campos: id, variant_id (FK cascade), attribute_value_id (FK cascade)
    - Restricción unique compuesta: (variant_id, attribute_value_id)
    - _Requerimientos: 4.3_
  - [x] 1.5 Crear migración para agregar columna `price` (decimal 10,2 nullable) a `boutique_product_variants`
    - _Requerimientos: 7.5_

- [x] 2. Crear modelos Eloquent nuevos
  - [x] 2.1 Crear modelo `BoutiqueProductAttribute` en `app/Models/Boutique/`
    - Fillable: name. Hidden: id, updated_at. UUID auto-generado en boot.
    - Relaciones: `values()` hasMany BoutiqueProductAttributeValue, `products()` belongsToMany BoutiqueProduct
    - Método `findByUuid()` estático
    - Usar patrón de tabla dinámica con `DB_TABLE_PREFIX` como los modelos existentes
    - _Requerimientos: 1.1, 1.2, 1.4_
  - [x] 2.2 Crear modelo `BoutiqueProductAttributeValue` en `app/Models/Boutique/`
    - Fillable: attribute_id, value, color_hex, sort_order. Hidden: id, attribute_id, updated_at. UUID auto-generado.
    - Relaciones: `attribute()` belongsTo, `variants()` belongsToMany BoutiqueProductVariant
    - Método `findByUuid()` estático
    - _Requerimientos: 2.1, 2.2_
  - [x] 2.3 Crear modelo pivote `BoutiqueVariantAttributeValue` en `app/Models/Boutique/`
    - timestamps = false. Fillable: variant_id, attribute_value_id
    - _Requerimientos: 4.3_

- [x] 3. Modificar modelos Eloquent existentes
  - [x] 3.1 Modificar `BoutiqueProductVariant`: agregar `price` a fillable, cast `price` como `decimal:2`, agregar relación `attributeValues()` belongsToMany, agregar accessor `getEffectivePriceAttribute()`
    - _Requerimientos: 5.2, 7.5, 8.4, 8.5_
  - [x] 3.2 Modificar `BoutiqueProduct`: agregar relación `attributes()` belongsToMany, agregar relación `allVariants()` hasMany sin filtro de active
    - _Requerimientos: 3.1, 3.3_

- [x] 4. Checkpoint — Ejecutar migraciones y verificar que los modelos se instancian correctamente
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Crear BoutiqueAttributeController con CRUD de atributos y valores
  - [x] 5.1 Crear `BoutiqueAttributeController` en `app/Http/Controllers/Boutique/`
    - Método `list()`: retorna atributos ordenados alfabéticamente con sus valores. Usar `ApiResponseHelper`.
    - _Requerimientos: 1.4_
  - [x] 5.2 Implementar método `store()` para crear atributo
    - Validar nombre no vacío, max 100 chars, único. Retornar 201 o error 400 `ATTRIBUTE_NAME_EXISTS`.
    - _Requerimientos: 1.2, 1.3_
  - [x] 5.3 Implementar método `update()` para actualizar nombre de atributo
    - Validar uuid existente, nombre único excluyendo el actual.
    - _Requerimientos: 1.5_
  - [x] 5.4 Implementar método `delete()` para eliminar atributo
    - Verificar que no esté asignado a productos. Si está en uso, retornar 409 `ATTRIBUTE_IN_USE`. Si no, eliminar atributo y valores en cascada.
    - _Requerimientos: 1.6, 1.7_
  - [x] 5.5 Implementar método `storeValue()` para crear valor de atributo
    - Validar attribute_uuid, value no vacío, max 100 chars, unique(attribute_id, value). Aceptar color_hex y sort_order opcionales. Retornar 201 o error 400 `VALUE_ALREADY_EXISTS`.
    - _Requerimientos: 2.2, 2.3_
  - [x] 5.6 Implementar método `updateValue()` para actualizar valor de atributo
    - Validar uuid existente, actualizar value, color_hex, sort_order.
    - _Requerimientos: 2.4_
  - [x] 5.7 Implementar método `deleteValue()` para eliminar valor de atributo
    - Verificar que no esté asociado a variantes. Si está en uso, retornar 409 `VALUE_IN_USE`. Si no, eliminar.
    - _Requerimientos: 2.5, 2.6_
  - [ ]* 5.8 Escribir test de propiedad para CRUD de atributos
    - **Propiedad 1: Round-trip de CRUD de Atributos**
    - **Valida: Requerimientos 1.1, 1.2, 1.5**
  - [ ]* 5.9 Escribir test de propiedad para unicidad de nombre de atributo
    - **Propiedad 2: Unicidad de nombre de Atributo**
    - **Valida: Requerimiento 1.3**
  - [ ]* 5.10 Escribir test de propiedad para listado ordenado
    - **Propiedad 3: Listado de Atributos ordenado alfabéticamente**
    - **Valida: Requerimiento 1.4**
  - [ ]* 5.11 Escribir test de propiedad para eliminación de atributo
    - **Propiedad 4: Eliminación de Atributo no asignado elimina valores en cascada**
    - **Valida: Requerimiento 1.6**
  - [ ]* 5.12 Escribir test de propiedad para rechazo de eliminación de atributo en uso
    - **Propiedad 5: Eliminación de Atributo en uso es rechazada**
    - **Valida: Requerimiento 1.7**
  - [ ]* 5.13 Escribir test de propiedad para CRUD de valores
    - **Propiedad 6: Round-trip de CRUD de Valores de Atributo**
    - **Valida: Requerimientos 2.1, 2.2, 2.4**
  - [ ]* 5.14 Escribir test de propiedad para unicidad de valor
    - **Propiedad 7: Unicidad de valor dentro de un Atributo**
    - **Valida: Requerimiento 2.3**
  - [ ]* 5.15 Escribir test de propiedad para eliminación de valor
    - **Propiedad 8: Eliminación de Valor no usado vs en uso**
    - **Valida: Requerimientos 2.5, 2.6**

- [x] 6. Registrar rutas del BoutiqueAttributeController en `api.php`
  - Agregar rutas bajo `boutique/admin/` con middleware `bandwidth_usage`, `auth:sanctum`:
    - `attributes/list`, `attributes/store`, `attributes/update`, `attributes/delete`
    - `attribute-values/store`, `attribute-values/update`, `attribute-values/delete`
  - Agregar `use` del controlador al inicio del archivo
  - _Requerimientos: 1.2, 1.4, 1.5, 1.6, 2.2, 2.4, 2.5_

- [x] 7. Checkpoint — Verificar que los endpoints de atributos y valores funcionan
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Implementar generación de variantes y gestión individual en BoutiqueProductController
  - [x] 8.1 Implementar método `generateVariants()` en BoutiqueProductController
    - Recibir product_uuid y array de attributeConfig [{attribute_uuid, value_uuids}]
    - Sincronizar atributos del producto en tabla pivote `boutique_product_attribute_product`
    - Calcular producto cartesiano de valores seleccionados
    - Validar límite de 100 combinaciones, retornar 400 `VARIANT_LIMIT_EXCEEDED` si excede
    - Preservar variantes existentes cuya combinación siga siendo válida
    - Crear nuevas variantes con SKU auto-generado, price null, stock 0, active true
    - Crear registros en `boutique_variant_attribute_values`
    - Eliminar variantes cuya combinación ya no aplique
    - Retornar lista de variantes con sus attribute_values
    - _Requerimientos: 3.2, 3.4, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7_
  - [x] 8.2 Implementar método `updateVariant()` en BoutiqueProductController
    - Recibir variant_uuid, campos editables: sku, price, stock, active
    - Validar unicidad de SKU entre variantes activas del mismo producto
    - Retornar 400 `VARIANT_SKU_DUPLICATE` si SKU duplicado
    - _Requerimientos: 5.2, 5.3, 5.4_
  - [x] 8.3 Implementar método `deleteVariant()` en BoutiqueProductController
    - Recibir variant_uuid, eliminar variante y registros pivote asociados
    - _Requerimientos: 5.6_
  - [x] 8.4 Modificar métodos `store()` y `update()` de BoutiqueProductController para aceptar atributos opcionales en el payload
    - Si se envía `attributes[]`, sincronizar asignación de atributos al producto
    - _Requerimientos: 3.2, 3.3_
  - [ ]* 8.5 Escribir test de propiedad para asignación de atributos a producto
    - **Propiedad 9: Round-trip de asignación de Atributos a Producto**
    - **Valida: Requerimientos 3.2, 3.3, 3.4**
  - [ ]* 8.6 Escribir test de propiedad para conteo de variantes generadas
    - **Propiedad 10: Conteo de variantes generadas es el producto cartesiano**
    - **Valida: Requerimientos 4.1, 4.5, 4.6**
  - [ ]* 8.7 Escribir test de propiedad para integridad de variantes
    - **Propiedad 11: Integridad de variantes generadas**
    - **Valida: Requerimientos 4.2, 4.3**
  - [ ]* 8.8 Escribir test de propiedad para preservación de variantes
    - **Propiedad 12: Preservación de variantes al regenerar**
    - **Valida: Requerimiento 4.4**
  - [ ]* 8.9 Escribir test de propiedad para CRUD individual de variantes
    - **Propiedad 13: CRUD individual de Variantes**
    - **Valida: Requerimientos 5.2, 5.5, 5.6**
  - [ ]* 8.10 Escribir test de propiedad para unicidad de SKU de variante
    - **Propiedad 14: Unicidad de SKU de Variante dentro del producto**
    - **Valida: Requerimientos 5.3, 5.4**
  - [ ]* 8.11 Escribir test de propiedad para descripción legible de variante
    - **Propiedad 15: Descripción legible de combinación de atributos**
    - **Valida: Requerimiento 5.1**

- [x] 9. Registrar rutas de variantes en `api.php`
  - Agregar bajo `boutique/admin/products/`:
    - `generate_variants`, `update_variant`, `delete_variant`
  - _Requerimientos: 4.1, 5.2, 5.6_

- [x] 10. Modificar BoutiqueCatalogController para incluir atributos en detalle público
  - Modificar método `detail()` para cargar atributos con valores y variantes activas con sus attribute_values
  - Incluir precio efectivo (variante o producto padre) en cada variante
  - Solo retornar variantes con active = true
  - _Requerimientos: 8.1, 8.4, 8.5_
  - [ ]* 10.1 Escribir test de propiedad para precio efectivo
    - **Propiedad 18: Resolución de precio efectivo**
    - **Valida: Requerimientos 8.4, 8.5**
  - [ ]* 10.2 Escribir test de propiedad para API pública
    - **Propiedad 19: API pública retorna atributos y variantes activas**
    - **Valida: Requerimiento 8.1**

- [x] 11. Checkpoint — Verificar que todos los endpoints backend funcionan correctamente
  - Ensure all tests pass, ask the user if questions arise.

- [x] 12. Crear seeder de migración de datos legacy
  - Crear `MigrateVariantsToAttributesSeeder` en `database/seeders/`
  - Crear atributos "Color" y "Talla" si no existen
  - Extraer valores únicos de campos `color` y `size` de variantes existentes
  - Crear BoutiqueProductAttributeValue por cada valor único, preservando color_hex
  - Omitir campos vacíos/nulos
  - Crear registros en `boutique_product_attribute_product`
  - Crear registros en `boutique_variant_attribute_values`
  - _Requerimientos: 7.1, 7.2, 7.3, 7.4, 7.6, 7.7_
  - [ ]* 12.1 Escribir test de propiedad para integridad de migración
    - **Propiedad 17: Integridad de migración de datos legacy**
    - **Valida: Requerimientos 7.2, 7.3, 7.4**

- [x] 13. Modificar StoreLayoutComponent — Propiedades y métodos de atributos
  - Agregar propiedades: `availableAttributes`, `selectedProductAttributes`, `newAttributeName`, interfaces `AttributeValue`, `SelectedAttribute`
  - Implementar `loadAttributes()` para cargar atributos disponibles desde el backend
  - Implementar `addAttributeToProduct()`, `removeAttributeFromProduct()`, `toggleAttributeValue()`
  - Implementar `createAttributeInline()` y `createValueInline()` para creación rápida
  - _Requerimientos: 6.1, 6.2_

- [x] 14. Modificar StoreLayoutComponent — Generación y gestión de variantes
  - Implementar `generateVariants()` que llama al endpoint backend y muestra resultado en tabla
  - Implementar `saveVariantChanges()` para persistir cambios en variantes editadas
  - Implementar `deleteVariant()` para eliminar variante individual
  - Agregar lógica de toggle "Producto con variantes" que muestra/oculta sección de atributos
  - Mostrar resumen de total de variantes y stock acumulado
  - _Requerimientos: 6.3, 6.4, 6.5, 6.6, 6.7, 6.8_
  - [ ]* 14.1 Escribir test de propiedad para stock total
    - **Propiedad 16: Stock total es la suma de stocks de variantes activas**
    - **Valida: Requerimiento 6.8**

- [x] 15. Modificar template HTML de StoreLayoutComponent
  - Agregar sección de atributos con selector dropdown y checkboxes de valores
  - Agregar botón "Generar variantes" y tabla editable de variantes
  - Columnas de tabla: combinación de atributos, SKU (input), precio (input), stock (input), activo (toggle), eliminar (botón)
  - Agregar indicador visual de filas modificadas y botón "Guardar cambios"
  - Agregar toggle "Producto con variantes"
  - Agregar resumen de variantes totales y stock acumulado
  - _Requerimientos: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.8_

- [ ] 16. Modificar vista pública de detalle de producto (product-detail)
  - Mostrar selectores de atributos (dropdowns o botones) para filtrar variantes
  - Filtrar variantes disponibles según selección parcial/completa de atributos
  - Mostrar precio efectivo de la variante seleccionada
  - Mostrar estado "Agotada" y deshabilitar botón de agregar al carrito si stock = 0
  - _Requerimientos: 8.1, 8.2, 8.3, 8.4, 8.5_
  - [ ]* 16.1 Escribir test de propiedad para filtrado de variantes
    - **Propiedad 20: Filtrado de variantes por selección de atributos**
    - **Valida: Requerimiento 8.2**

- [x] 17. Checkpoint final — Verificar integración completa
  - Ensure all tests pass, ask the user if questions arise.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia requerimientos específicos para trazabilidad
- Los checkpoints aseguran validación incremental
- Los tests de propiedades validan propiedades universales de correctitud
- Los tests unitarios validan ejemplos específicos y edge cases
