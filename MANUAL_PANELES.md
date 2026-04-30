# Manual de Paneles — Grupo VECSA

Este documento resume el uso operativo de cada panel interno, sus rutas principales y flujos recomendados.

## 1) Panel Developer

- **Ruta:** `/admin/developer`
- **Perfil objetivo:** equipo técnico / administración avanzada
- **Objetivo:** configuración global, monitoreo y operación técnica

### Qué puedes hacer

- Gestionar usuarios, roles y permisos
- Revisar herramientas de soporte técnico y monitoreo API
- Acceder a configuraciones de tienda y módulos administrativos

### Flujo recomendado

1. Entrar a dashboard developer
2. Revisar estado general del sistema
3. Ir a la sección específica (usuarios, roles, monitor, etc.)
4. Guardar cambios y validar con una cuenta de prueba

---

## 2) Panel Administrador

- **Ruta:** `/admin/administrator`
- **Perfil objetivo:** administración de operación
- **Objetivo:** gestión administrativa, permisos y coordinación de paneles

### Qué puedes hacer

- Gestionar usuarios y permisos
- Navegar a paneles internos por permisos (gestor, recepción, valuador, citas, staff, refacciones, vendedor)
- Acceder a herramientas transversales (tienda, benchmark, marketing, inventario)

### Flujo recomendado

1. Revisar dashboard administrativo
2. Validar permisos por rol en matriz
3. Entrar al panel operativo correspondiente
4. Confirmar que el usuario final ve solo lo necesario

---

## 3) Panel Gerente

- **Ruta:** `/admin/gerente`
- **Perfil objetivo:** gerencia operativa
- **Objetivo:** seguimiento consolidado de ventas, citas, valuaciones y operación comercial

### Qué puedes hacer

- Ver métricas y gráficas de operación
- Entrar a paneles clave (gestor, recepción, valuador, citas, staff, refacciones, vendedor)
- Acceder a herramientas de negocio (tienda, benchmark, marketing, inventario)

### Flujo recomendado

1. Revisar KPIs del dashboard
2. Detectar cuellos de botella (citas/valuaciones/pedidos)
3. Entrar al panel operativo implicado
4. Dar seguimiento en la siguiente revisión diaria

---

## 4) Panel Gestor

- **Ruta:** `/admin/gestor`
- **Perfil objetivo:** coordinación comercial/operativa
- **Objetivo:** administrar tareas transversales según permisos

### Qué puedes hacer

- Navegar a funcionalidades habilitadas por permisos granulares
- Consultar benchmark y módulos auxiliares si aplica

### Flujo recomendado

1. Revisar pendientes del día
2. Ejecutar operación por módulo autorizado
3. Escalar bloqueos a administrador/gerencia

---

## 5) Panel Recepción

- **Ruta:** `/admin/receptionist`
- **Perfil objetivo:** recepción y atención inicial
- **Objetivo:** registrar y canalizar solicitudes/citas

### Qué puedes hacer

- Capturar solicitudes y datos de contacto
- Dar seguimiento a recepción de clientes

### Flujo recomendado

1. Registrar solicitud del cliente
2. Validar datos de contacto y sucursal
3. Canalizar hacia valuador/citas según proceso

---

## 6) Panel Valuador

- **Ruta:** `/admin/valuator`
- **Perfil objetivo:** valuación
- **Objetivo:** gestionar valuaciones y checklist por unidad

### Qué puedes hacer

- Ver dashboard de valuaciones
- Gestionar citas de valuación
- Abrir checklist y avanzar estatus de cada unidad

### Flujo recomendado

1. Revisar citas asignadas
2. Iniciar checklist por unidad
3. Completar información técnica/comercial
4. Dejar unidad lista para cotización/seguimiento

---

## 7) Panel Citas

- **Ruta:** `/admin/appointment_manager`
- **Subruta principal:** `/admin/appointment_manager/assign-valuations`
- **Perfil objetivo:** asignación de citas
- **Objetivo:** distribuir citas de valuación a valuadores

### Qué puedes hacer

- Ver dashboard de citas
- Asignar valuador a cada cita
- Consultar estatus general de carga

### Flujo recomendado

1. Revisar citas no asignadas
2. Asignar valuador disponible
3. Confirmar que la cita quedó ligada correctamente

---

## 8) Panel Staff

- **Ruta:** `/admin/staff`
- **Perfil objetivo:** equipo operativo staff
- **Objetivo:** ejecución de tareas internas asignadas

### Qué puedes hacer

- Operar módulos habilitados para staff
- Dar seguimiento a procesos internos de soporte

---

## 9) Panel Refacciones

- **Ruta:** `/admin/spare_parts`
- **Perfil objetivo:** refacciones
- **Objetivo:** seguimiento de estatus de refacciones en valuaciones

### Qué puedes hacer

- Revisar solicitudes vinculadas a valuaciones
- Actualizar avance y estatus de refacciones

### Flujo recomendado

1. Abrir unidades con pendiente de refacciones
2. Actualizar estatus/cotización
3. Notificar al flujo de valuación cuando quede resuelto

---

## 10) Panel Vendedor (incluye casos unificados)

- **Rutas base:**
  - `/admin/seller`
  - `/admin/strega-seller`
  - `/admin/strega-manager` (unificado a vendedor)
  - `/admin/strega-administrator` (unificado a vendedor)
  - `/admin/technician` y `/admin/bodywork_paint_technician` redirigen a vendedor
- **Perfil objetivo:** ventas y difusión de inventario
- **Objetivo:** gestionar citas/valuaciones asignadas y compartir unidades con enlace de referido

### Qué puedes hacer

- Ver dashboard de citas (totales, pendientes, en progreso, completadas)
- Copiar link general de referidos
- Ver inventario y copiar link de referido por unidad

### Flujo recomendado

1. Revisar métricas de citas del día
2. Copiar link general de referido para campañas rápidas
3. Buscar unidad en inventario
4. Copiar link referido por unidad y compartir al cliente

---

## 11) Herramientas transversales

### Benchmark ADS

- **Ruta:** `/admin/benchmark` (o dentro de cada panel por su menú)
- **Requiere permiso:** `access benchmark`

### Tienda (Store Management)

- **Ruta:** `/admin/store`
- **Requiere permiso:** `access store_management`

### Inventario de vehículos

- **Ruta central:** `/admin/vehicle-inventory` (redirige al panel correspondiente)
- **Acceso:** depende de rol/permisos configurados

---

## 12) Buenas prácticas operativas

- Validar sesión y rol antes de iniciar turno
- Usar solo paneles autorizados por permiso
- Evitar operación duplicada en dos paneles sobre la misma unidad
- Ante error de acceso, cerrar sesión y volver a iniciar para refrescar permisos

