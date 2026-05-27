/** Roles que pueden asignarse a más de una sucursal (inventario / boutique filtrado por pivote). */
export const ROLES_WITH_MULTI_DEALERSHIP = [
  'marketing',
  'gestor',
  'manager',
  'staff',
  'receptionist',
  'valuator',
  'appointment_manager',
  'seller',
  'spare_parts',
  'bodywork_paint_technician',
  'technician',
  'gerente',
  'strega-seller',
  'strega-manager',
  'strega-administrator',
] as const;

const MULTI_SET = new Set<string>(ROLES_WITH_MULTI_DEALERSHIP);

export function roleAllowsMultipleDealerships(role: string | null | undefined): boolean {
  const r = (role ?? '').trim().toLowerCase();
  return r !== '' && MULTI_SET.has(r);
}
