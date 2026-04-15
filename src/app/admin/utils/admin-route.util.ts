/**
 * El login devuelve nombres de rol de BD (p. ej. `manager` del seeder legacy).
 * Las rutas de Angular usan el segmento del panel (p. ej. `gestor`).
 */
const LEGACY_ROLE_TO_ADMIN_SEGMENT: Record<string, string> = {
  manager: 'gestor',
  technician: 'bodywork_paint_technician',
  /** Variante corta o datos legacy */
  admin: 'administrator',
};

export function adminRouteSegmentForRole(role: string | null | undefined): string {
  const r = (role ?? '').trim().toLowerCase();
  if (!r) {
    return '';
  }
  return LEGACY_ROLE_TO_ADMIN_SEGMENT[r] ?? r;
}

/** Ruta base del panel del usuario (sin cliente). */
export function adminDashboardUrl(role: string | null | undefined): string {
  const seg = adminRouteSegmentForRole(role);
  return seg ? `/admin/${seg}` : '/';
}
