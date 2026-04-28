export interface ManualSection {
  title: string;
  bullets: string[];
}

export interface PanelManual {
  slug: string;
  title: string;
  icon: string;
  route: string;
  summary: string;
  sections: ManualSection[];
}

export const PANEL_MANUALS: PanelManual[] = [
  {
    slug: 'developer',
    title: 'Panel Developer',
    icon: 'code',
    route: '/admin/developer',
    summary: 'Configuracion avanzada, monitoreo y gestion tecnica.',
    sections: [
      { title: 'Objetivo', bullets: ['Administrar configuraciones globales y herramientas tecnicas.'] },
      { title: 'Acciones clave', bullets: ['Gestion de usuarios/roles/permisos.', 'Monitoreo API y herramientas de soporte.'] },
    ],
  },
  {
    slug: 'administrator',
    title: 'Panel Administrador',
    icon: 'admin_panel_settings',
    route: '/admin/administrator',
    summary: 'Control administrativo, permisos y coordinacion de paneles.',
    sections: [
      { title: 'Objetivo', bullets: ['Gestionar operacion interna y accesos.'] },
      { title: 'Acciones clave', bullets: ['Usuarios y permisos.', 'Navegacion a paneles operativos.'] },
    ],
  },
  {
    slug: 'gerente',
    title: 'Panel Gerente',
    icon: 'manage_accounts',
    route: '/admin/gerente',
    summary: 'Seguimiento de KPIs comerciales y operativos.',
    sections: [
      { title: 'Objetivo', bullets: ['Supervisar desempeno y carga operativa.'] },
      { title: 'Acciones clave', bullets: ['Revisar metricas.', 'Dar seguimiento con paneles operativos.'] },
    ],
  },
  {
    slug: 'gestor',
    title: 'Panel Gestor',
    icon: 'manage_accounts',
    route: '/admin/gestor',
    summary: 'Ejecucion de tareas de coordinacion segun permisos.',
    sections: [
      { title: 'Objetivo', bullets: ['Operar tareas transversales del dia.'] },
      { title: 'Acciones clave', bullets: ['Atender pendientes por modulo.', 'Escalar bloqueos a administracion.'] },
    ],
  },
  {
    slug: 'receptionist',
    title: 'Panel Recepcion',
    icon: 'assignment_ind',
    route: '/admin/receptionist',
    summary: 'Captura inicial de clientes y canalizacion de citas.',
    sections: [
      { title: 'Objetivo', bullets: ['Registrar solicitudes y datos de contacto.'] },
      { title: 'Acciones clave', bullets: ['Captura de citas.', 'Canalizacion a valuador/citas.'] },
    ],
  },
  {
    slug: 'valuator',
    title: 'Panel Valuador',
    icon: 'price_check',
    route: '/admin/valuator',
    summary: 'Gestion de citas de valuacion y checklist por unidad.',
    sections: [
      { title: 'Objetivo', bullets: ['Completar flujo de valuacion por unidad.'] },
      { title: 'Acciones clave', bullets: ['Citas de valuacion.', 'Checklist y avance de estatus.'] },
    ],
  },
  {
    slug: 'appointment-manager',
    title: 'Panel Citas',
    icon: 'event',
    route: '/admin/appointment_manager',
    summary: 'Asignacion de citas de valuacion a valuadores.',
    sections: [
      { title: 'Objetivo', bullets: ['Distribuir carga de citas.'] },
      { title: 'Acciones clave', bullets: ['Dashboard de citas.', 'Asignacion en /assign-valuations.'] },
    ],
  },
  {
    slug: 'staff',
    title: 'Panel Staff',
    icon: 'badge',
    route: '/admin/staff',
    summary: 'Operacion interna de tareas de soporte.',
    sections: [
      { title: 'Objetivo', bullets: ['Ejecutar tareas internas habilitadas.'] },
      { title: 'Acciones clave', bullets: ['Seguimiento de procesos asignados.'] },
    ],
  },
  {
    slug: 'spare-parts',
    title: 'Panel Refacciones',
    icon: 'settings',
    route: '/admin/spare_parts',
    summary: 'Control de estatus de refacciones en valuaciones.',
    sections: [
      { title: 'Objetivo', bullets: ['Actualizar avance de refacciones por unidad.'] },
      { title: 'Acciones clave', bullets: ['Seguimiento de pendientes.', 'Actualizacion de estatus.'] },
    ],
  },
  {
    slug: 'seller',
    title: 'Panel Vendedor (unificado)',
    icon: 'sell',
    route: '/admin/seller',
    summary: 'Dashboard de citas, links de referidos e inventario compartible.',
    sections: [
      { title: 'Objetivo', bullets: ['Generar y compartir oportunidades por unidad.'] },
      { title: 'Acciones clave', bullets: ['Metricas de citas.', 'Link general de referidos.', 'Link referido por unidad en inventario.'] },
      { title: 'Roles unificados', bullets: ['strega-manager y strega-administrator usan este panel.', 'technician y bodywork_paint_technician redirigen a este panel.'] },
    ],
  },
  {
    slug: 'benchmark',
    title: 'Benchmark ADS',
    icon: 'analytics',
    route: '/admin/benchmark',
    summary: 'Herramienta transversal para comparativos y escaneos.',
    sections: [
      { title: 'Requisito', bullets: ['Permiso access benchmark.'] },
      { title: 'Uso', bullets: ['Consultar competidores e historial de escaneos.'] },
    ],
  },
  {
    slug: 'store',
    title: 'Tienda (Store Management)',
    icon: 'storefront',
    route: '/admin/store',
    summary: 'Operacion de pedidos, clientes, puntos y cupones.',
    sections: [
      { title: 'Requisito', bullets: ['Permiso access store_management.'] },
      { title: 'Uso', bullets: ['Gestion comercial y operativa de boutique.'] },
    ],
  },
];

