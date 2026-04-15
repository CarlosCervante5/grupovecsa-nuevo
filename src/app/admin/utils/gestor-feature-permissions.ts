/** Permisos Spatie para vistas del panel gestor (deben existir en BD y asignarse por rol). */
export const GESTOR_FEATURE_PERMISSIONS = {
  promotions: 'access gestor_promotions',
  scheduledEvents: 'access gestor_scheduled_events',
  rewards: 'access gestor_rewards',
} as const;

const ALL_GESTOR_FEATURE_VALUES = Object.values(GESTOR_FEATURE_PERMISSIONS);

/**
 * Si el rol es `gestor` y aún no tiene ninguno de los permisos granulares, se asume acceso
 * completo al módulo (compatibilidad hasta migrar datos en BD).
 */
export function expandLegacyGestorPermissions(
  permissions: string[],
  role: string | null | undefined,
): string[] {
  const r = (role ?? '').trim().toLowerCase();
  if (r !== 'gestor') {
    return permissions;
  }
  const hasAny = ALL_GESTOR_FEATURE_VALUES.some((p) => permissions.includes(p));
  if (hasAny) {
    return permissions;
  }
  return [...permissions, ...ALL_GESTOR_FEATURE_VALUES];
}
