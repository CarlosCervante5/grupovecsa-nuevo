/** Permisos Spatie para vistas del panel gestor (deben existir en BD y asignarse por rol). */
export const GESTOR_FEATURE_PERMISSIONS = {
  promotions: 'access gestor_promotions',
  scheduledEvents: 'access gestor_scheduled_events',
  rewards: 'access gestor_rewards',
} as const;

const ALL_GESTOR_FEATURE_VALUES = Object.values(GESTOR_FEATURE_PERMISSIONS);

/**
 * Si el rol es `gestor` o `manager` y aún no tiene ninguno de los permisos granulares del
 * módulo marketing, se asume acceso completo a esas vistas (mismo criterio: comparten
 * `GestorModule` / URL distinta). Si ya hay permisos `access gestor_*`, solo se usan esos.
 */
export function expandLegacyGestorPermissions(
  permissions: string[],
  role: string | null | undefined,
): string[] {
  const r = (role ?? '').trim().toLowerCase();
  if (r !== 'gestor' && r !== 'manager') {
    return permissions;
  }
  const hasAny = ALL_GESTOR_FEATURE_VALUES.some((p) => permissions.includes(p));
  if (hasAny) {
    return permissions;
  }
  return [...permissions, ...ALL_GESTOR_FEATURE_VALUES];
}
