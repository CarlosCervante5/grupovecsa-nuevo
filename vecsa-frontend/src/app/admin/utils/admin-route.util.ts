/**
 * Mapeos donde el nombre de rol en BD no coincide con el segmento de ruta del panel.
 */
const LEGACY_ROLE_TO_ADMIN_SEGMENT: Record<string, string> = {
  admin: 'administrator',
};

/**
 * Segmentos bajo `/admin` que son la entrada principal (home) de un rol interno tras el login.
 * No incluye herramientas transversales (`benchmark`, `store`) salvo que tengan rol dedicado.
 */
export const ADMIN_PANEL_HOME_SEGMENTS: readonly string[] = [
  'marketing',
  'gestor',
  'manager',
  'staff',
  'receptionist',
  'valuator',
  'appointment_manager',
  'administrator',
  'bodywork_paint_technician',
  'technician',
  'spare_parts',
  'developer',
  'gerente',
  'seller',
  'strega-seller',
  'strega-manager',
  'strega-administrator',
] as const;

const ADMIN_PANEL_HOME_SET = new Set(ADMIN_PANEL_HOME_SEGMENTS);

export function adminRouteSegmentForRole(role: string | null | undefined): string {
  const r = (role ?? '').trim().toLowerCase();
  if (!r || r === 'client') {
    return '';
  }
  return LEGACY_ROLE_TO_ADMIN_SEGMENT[r] ?? r;
}

export function isAdminPanelHomeSegment(segment: string | null | undefined): boolean {
  if (!segment) {
    return false;
  }
  return ADMIN_PANEL_HOME_SET.has(segment.trim().toLowerCase());
}

/**
 * Base del panel hojalatería según si la URL es `/admin/technician` o `/admin/bodywork_paint_technician`.
 */
export function adminBodyworkPanelBaseFromRouterUrl(urlPath: string): string {
  const path = (urlPath || '').split('?')[0];
  const match = path.match(/^\/admin\/(technician|bodywork_paint_technician)(?:\/|$)/);
  if (match) {
    return `/admin/${match[1]}`;
  }
  return '/admin/bodywork_paint_technician';
}

/** Ruta base del panel del usuario (sin cliente). */
export function adminDashboardUrl(role: string | null | undefined): string {
  const seg = adminRouteSegmentForRole(role);
  return seg ? `/admin/${seg}` : '/';
}

/**
 * Panel “principal” para volver desde herramientas solo desarrollador (`/admin/developer`).
 * El home de sesión suele seguir siendo `/admin/developer`; aquí se apunta al panel operativo administrador,
 * igual que benchmark/inventario (acceso vía mismo criterio de `validate_role` en backend).
 */
export function adminPrimaryPanelUrl(role: string | null | undefined): string {
  const seg = adminRouteSegmentForRole(role);
  if (seg === 'developer') {
    return '/admin/administrator';
  }
  return adminDashboardUrl(role);
}

/**
 * Inventario de vehículos embebido en el layout del panel (no la URL plana `/admin/vehicle-inventory`).
 * Marketing conserva el segmento `vehicles`. Developer se trata como administrador.
 */
export function adminVehicleInventoryUrl(role: string | null | undefined): string | null {
  let seg = adminRouteSegmentForRole(role);
  if (seg === 'developer') {
    seg = 'administrator';
  }
  if (!seg) {
    return null;
  }
  if (seg === 'marketing') {
    return '/admin/marketing/vehicles';
  }
  if (seg === 'administrator' || seg === 'gestor' || seg === 'manager' || seg === 'gerente') {
    return `/admin/${seg}/vehicle-inventory`;
  }
  return null;
}

/**
 * Benchmark ADS bajo el layout del panel del rol. Developer usa el panel administrador.
 */
export function adminBenchmarkUrl(role: string | null | undefined): string | null {
  let seg = adminRouteSegmentForRole(role);
  if (!seg) {
    return null;
  }
  if (seg === 'developer') {
    seg = 'administrator';
  }
  return `/admin/${seg}/benchmark`;
}
