export interface LegalDocumentDef {
  slug: string;
  label: string;
  icon: string;
  publicPath: string;
  summary: string;
}

export const LEGAL_DOCUMENTS: readonly LegalDocumentDef[] = [
  {
    slug: 'privacidad',
    label: 'Aviso de privacidad',
    icon: 'privacy_tip',
    publicPath: '/aviso-privacidad',
    summary: 'Tratamiento y protección de datos personales.',
  },
  {
    slug: 'condiciones',
    label: 'Condiciones de uso',
    icon: 'gavel',
    publicPath: '/condiciones-uso',
    summary: 'Términos de uso del sitio y servicios VECSA.',
  },
  {
    slug: 'devoluciones',
    label: 'Políticas de devolución',
    icon: 'assignment_return',
    publicPath: '/politicas-devolucion',
    summary: 'Devoluciones, cancelaciones y reembolsos.',
  },
  {
    slug: 'lealtad',
    label: 'Programa de lealtad',
    icon: 'loyalty',
    publicPath: '/programa-lealtad',
    summary: 'Programa de recompensas y puntos.',
  },
  {
    slug: 'cookies',
    label: 'Uso de cookies',
    icon: 'cookie',
    publicPath: '/uso-cookies',
    summary: 'Cookies y tecnologías de rastreo.',
  },
];
