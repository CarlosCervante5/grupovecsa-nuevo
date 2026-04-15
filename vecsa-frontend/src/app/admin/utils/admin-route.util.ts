/**
 * Mapeos donde el nombre de rol en BD no coincide con el segmento de ruta del panel.
 * `manager` tiene su propia ruta `/admin/manager` (mismo módulo que gestor).
 */
const LEGACY_ROLE_TO_ADMIN_SEGMENT: Record<string, string> = {
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
