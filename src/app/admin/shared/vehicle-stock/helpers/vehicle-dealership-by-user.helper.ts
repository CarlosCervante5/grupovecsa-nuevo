/** Nombres de sucursal en BD (literales). */
export const VEHICLE_DEALERSHIP_VECSA_HIDALGO = 'Vecsa Hidalgo';
export const VEHICLE_DEALERSHIP_BMW_HUB_SERDAN = 'Bmw Hub Serdán';
export const VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS = 'Vecsa Angelopolis';

export const VEHICLE_MAIN_DEALERSHIP_NAMES: readonly string[] = [
  VEHICLE_DEALERSHIP_VECSA_HIDALGO,
  VEHICLE_DEALERSHIP_BMW_HUB_SERDAN,
  VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS,
];

/** Usuario con acceso a varias sucursales: elige en select. */
export const VEHICLE_DEALERSHIP_SELECT_USER_EMAIL = 'ana.gonzalez@bmwvecsa.com';

/** Fallback por email si la API de usuario no devuelve asignaciones. */
export const VEHICLE_DEALERSHIP_SELECTABLE_NAMES_BY_EMAIL: Readonly<
  Record<string, readonly string[]>
> = {
  [VEHICLE_DEALERSHIP_SELECT_USER_EMAIL]: [
    VEHICLE_DEALERSHIP_BMW_HUB_SERDAN,
    VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS,
  ],
};

/** Email (minúsculas) → sucursal fija (campos en solo lectura). */
export const VEHICLE_DEALERSHIP_NAME_BY_EMAIL: Readonly<Record<string, string>> = {
  'hub@vecsa.com': VEHICLE_DEALERSHIP_BMW_HUB_SERDAN,
  'angelopolis@vecsa.com': VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS,
};

export type VehicleDealershipFormMode = 'manual' | 'locked' | 'select';

export function vehicleDealershipFormModeForEmail(
  email: string | null | undefined,
): VehicleDealershipFormMode {
  const key = (email ?? '').trim().toLowerCase();
  if (VEHICLE_DEALERSHIP_NAME_BY_EMAIL[key]) {
    return 'locked';
  }
  if (key === VEHICLE_DEALERSHIP_SELECT_USER_EMAIL) {
    return 'select';
  }
  return 'manual';
}

export function vehicleDealershipNameForUserEmail(email: string | null | undefined): string | null {
  const key = (email ?? '').trim().toLowerCase();
  return VEHICLE_DEALERSHIP_NAME_BY_EMAIL[key] ?? null;
}

/** Nombres permitidos en select cuando no hay `dealership_ids` en el detalle del usuario. */
export function vehicleSelectableDealershipNamesForEmail(
  email: string | null | undefined,
): readonly string[] {
  const key = (email ?? '').trim().toLowerCase();
  return (
    VEHICLE_DEALERSHIP_SELECTABLE_NAMES_BY_EMAIL[key] ?? VEHICLE_MAIN_DEALERSHIP_NAMES
  );
}

/** Fallback de sucursal única (hub / angelopolis) si el detalle de usuario no trae asignaciones. */
export function vehicleLockedDealershipFallbackNamesForEmail(
  email: string | null | undefined,
): readonly string[] {
  const name = vehicleDealershipNameForUserEmail(email);
  return name ? [name] : [];
}

/** Nombres usados para resolver sucursales desde la API según el modo de formulario. */
export function vehicleDealershipFallbackNamesForEmail(
  email: string | null | undefined,
  mode: VehicleDealershipFormMode,
): readonly string[] {
  if (mode === 'select') {
    return vehicleSelectableDealershipNamesForEmail(email);
  }
  if (mode === 'locked') {
    return vehicleLockedDealershipFallbackNamesForEmail(email);
  }
  return [];
}

export function parseDealershipNamesFromDetail(
  dealershipNames: string | null | undefined,
): string[] {
  if (!dealershipNames?.trim()) {
    return [];
  }
  return dealershipNames
    .split(',')
    .map((part) => part.trim())
    .filter(Boolean);
}

/** Ubicación por defecto si la API no devuelve `location`. */
export function vehicleLocationFallbackForDealershipName(dealershipName: string): string {
  if (dealershipName === VEHICLE_DEALERSHIP_VECSA_HIDALGO) {
    return 'Hidalgo';
  }
  if (
    dealershipName === VEHICLE_DEALERSHIP_BMW_HUB_SERDAN ||
    dealershipName === VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS
  ) {
    return 'Puebla';
  }
  return '';
}

export function readSignedInUserEmail(): string | null {
  try {
    const raw = localStorage.getItem('user');
    if (!raw) {
      return null;
    }
    const user = JSON.parse(raw) as { email?: string };
    const email = user?.email?.trim();
    return email || null;
  } catch {
    return null;
  }
}

export function readSignedInUserUuid(): string | null {
  try {
    const raw = localStorage.getItem('user');
    if (!raw) {
      return null;
    }
    const user = JSON.parse(raw) as { uuid?: string };
    const uuid = user?.uuid?.trim();
    return uuid || null;
  } catch {
    return null;
  }
}
