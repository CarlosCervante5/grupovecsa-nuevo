import { Dealership } from '@interfaces/admin.interfaces';

/** Nombres de sucursal en BD (literales). */
export const VEHICLE_DEALERSHIP_VECSA_HIDALGO = 'Vecsa Hidalgo';
export const VEHICLE_DEALERSHIP_BMW_HUB_SERDAN = 'Bmw Hub Serdán';
export const VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS = 'Vecsa Angelopolis';

export const VEHICLE_MAIN_DEALERSHIP_NAMES: readonly string[] = [
  VEHICLE_DEALERSHIP_VECSA_HIDALGO,
  VEHICLE_DEALERSHIP_BMW_HUB_SERDAN,
  VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS,
];

/** Usuarios con varias sucursales: select + ubicación automática (asignación en admin). */
export const VEHICLE_DEALERSHIP_SELECT_USER_EMAILS: readonly string[] = [
  'ana.gonzalez@bmwvecsa.com',
  'admin@vecsa.com',
  'manager@vecsa.com',
];

/** Fallback por email si la API de usuario no devuelve asignaciones. */
export const VEHICLE_DEALERSHIP_SELECTABLE_NAMES_BY_EMAIL: Readonly<
  Record<string, readonly string[]>
> = {
  'ana.gonzalez@bmwvecsa.com': [
    VEHICLE_DEALERSHIP_BMW_HUB_SERDAN,
    VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS,
  ],
  'admin@vecsa.com': VEHICLE_MAIN_DEALERSHIP_NAMES,
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
  if (VEHICLE_DEALERSHIP_SELECT_USER_EMAILS.includes(key)) {
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
  if (VEHICLE_DEALERSHIP_SELECTABLE_NAMES_BY_EMAIL[key]) {
    return VEHICLE_DEALERSHIP_SELECTABLE_NAMES_BY_EMAIL[key];
  }
  /** Sin lista fija por email: solo sucursales asignadas en admin (API). */
  if (VEHICLE_DEALERSHIP_SELECT_USER_EMAILS.includes(key)) {
    return [];
  }
  return VEHICLE_MAIN_DEALERSHIP_NAMES;
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
    .split(/[,;]/)
    .map((part) => part.trim())
    .filter(Boolean);
}

export function normalizeDealershipKey(name: string): string {
  return name
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

/** Nombre literal de BD si coincide; si no, el de la API. */
export function canonicalDealershipName(name: string): string {
  const key = normalizeDealershipKey(name);
  for (const canonical of VEHICLE_MAIN_DEALERSHIP_NAMES) {
    if (normalizeDealershipKey(canonical) === key) {
      return canonical;
    }
  }
  return name.trim();
}

export function extractUserDealershipAssignment(detail: unknown): {
  ids: number[];
  names: string[];
} {
  if (!detail || typeof detail !== 'object') {
    return { ids: [], names: [] };
  }
  const row = detail as Record<string, unknown>;
  const rawIds = row['dealership_ids'];
  const rawNames = row['dealership_names'];

  const ids = Array.isArray(rawIds)
    ? rawIds
        .map((id) => Number(id))
        .filter((id) => !Number.isNaN(id) && id > 0)
    : [];

  let names: string[] = [];
  if (typeof rawNames === 'string') {
    names = parseDealershipNamesFromDetail(rawNames);
  } else if (Array.isArray(rawNames)) {
    names = rawNames.map((n) => String(n).trim()).filter(Boolean);
  }

  return {
    ids,
    names: names.map(canonicalDealershipName),
  };
}

function sortDealershipsByName(dealerships: Dealership[]): Dealership[] {
  return [...dealerships].sort((a, b) => a.name.localeCompare(b.name, 'es'));
}

/**
 * Cruza catálogo de sucursales con asignación del usuario (admin).
 * Si hay asignación en API pero no hay match en catálogo, usa los nombres de la API.
 */
export function resolveAssignedDealerships(
  catalog: Dealership[],
  assignedIds: number[],
  assignedNames: string[],
  fallbackNames: readonly string[],
): Dealership[] {
  if (assignedIds.length > 0 && catalog.length > 0) {
    const idSet = new Set(assignedIds.map((id) => Number(id)));
    const byId = catalog.filter((d) => d.id != null && idSet.has(Number(d.id)));
    if (byId.length > 0) {
      return sortDealershipsByName(byId);
    }
  }

  const apiNames = assignedNames.map(canonicalDealershipName);
  if (apiNames.length > 0 && catalog.length > 0) {
    const exact = catalog.filter((d) => apiNames.includes(d.name));
    if (exact.length > 0) {
      return sortDealershipsByName(exact);
    }

    const keySet = new Set(apiNames.map(normalizeDealershipKey));
    const byNormalized = catalog.filter((d) => keySet.has(normalizeDealershipKey(d.name)));
    if (byNormalized.length > 0) {
      return sortDealershipsByName(byNormalized);
    }
  }

  const hasApiAssignment = assignedIds.length > 0 || apiNames.length > 0;
  if (hasApiAssignment && apiNames.length > 0) {
    const resolved = apiNames.map((name) => {
      const fromCatalog =
        catalog.find((d) => normalizeDealershipKey(d.name) === normalizeDealershipKey(name)) ??
        catalog.find((d) => d.name === name);
      if (fromCatalog) {
        return fromCatalog;
      }
      return {
        name,
        location: vehicleLocationFallbackForDealershipName(name),
        description: null,
        created_at: new Date(),
      };
    });
    return sortDealershipsByName(resolved);
  }

  if (!hasApiAssignment && fallbackNames.length > 0 && catalog.length > 0) {
    const fallbackSet = new Set<string>(fallbackNames);
    const fromFallback = catalog.filter((d) => fallbackSet.has(d.name));
    if (fromFallback.length > 0) {
      return sortDealershipsByName(fromFallback);
    }
  }

  return [];
}

/** Ubicación por defecto si la API no devuelve `location`. */
export function vehicleLocationFallbackForDealershipName(dealershipName: string): string {
  const canonical = canonicalDealershipName(dealershipName);
  if (canonical === VEHICLE_DEALERSHIP_VECSA_HIDALGO) {
    return 'Hidalgo';
  }
  if (
    canonical === VEHICLE_DEALERSHIP_BMW_HUB_SERDAN ||
    canonical === VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS
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
