import { Dealership } from '@interfaces/admin.interfaces';

/** Nombres de sucursal en BD (literales). */
export const VEHICLE_DEALERSHIP_VECSA_HIDALGO = 'Vecsa Hidalgo';
export const VEHICLE_DEALERSHIP_BMW_HUB_SERDAN = 'Bmw Hub Serdán';
export const VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS = 'Vecsa Angelopolis';
export const VEHICLE_DEALERSHIP_VECSA_OAXACA = 'vecsa oaxaca';
export const VEHICLE_DEALERSHIP_VECSA_VERACRUZ = 'vecsa veracruz';

export const VEHICLE_MAIN_DEALERSHIP_NAMES: readonly string[] = [
  VEHICLE_DEALERSHIP_VECSA_HIDALGO,
  VEHICLE_DEALERSHIP_BMW_HUB_SERDAN,
  VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS,
  VEHICLE_DEALERSHIP_VECSA_OAXACA,
  VEHICLE_DEALERSHIP_VECSA_VERACRUZ,
];

/** Roles con inventario sin restricción por pivote (alineado al backend). */
export const VEHICLE_INVENTORY_BYPASS_ROLES: readonly string[] = [
  'administrator',
  'developer',
  'gerente',
  'admin',
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
 * Agrupa duplicados históricos en BD (typos, alias, mayúsculas).
 * Sucursales nuevas sin alias propio conservan clave única por nombre normalizado.
 */
export function dealershipDedupeBucketKey(name: string): string {
  const key = normalizeDealershipKey(name);
  if (!key) {
    return '';
  }

  const hidalgoKey = normalizeDealershipKey(VEHICLE_DEALERSHIP_VECSA_HIDALGO);
  const angelopolisKey = normalizeDealershipKey(VEHICLE_DEALERSHIP_VECSA_ANGELOPOLIS);
  const hubKey = normalizeDealershipKey(VEHICLE_DEALERSHIP_BMW_HUB_SERDAN);
  const oaxacaKey = normalizeDealershipKey(VEHICLE_DEALERSHIP_VECSA_OAXACA);
  const veracruzKey = normalizeDealershipKey(VEHICLE_DEALERSHIP_VECSA_VERACRUZ);

  if (
    key === hidalgoKey ||
    key === 'hidalgo' ||
    key === 'vecsa pachuca' ||
    key.includes('pachuca')
  ) {
    return hidalgoKey;
  }
  if (key.includes('angelopolis')) {
    return angelopolisKey;
  }
  if ((key.includes('hub') || key.includes('bmw')) && key.includes('serdan')) {
    return hubKey;
  }
  if (key.includes('oaxaca')) {
    return oaxacaKey;
  }
  if (key.includes('veracruz')) {
    return veracruzKey;
  }
  if (key.includes('vecsa') && key.includes('puebla') && !key.includes('angelopolis')) {
    return normalizeDealershipKey('vecsa puebla');
  }

  return key;
}

function scoreDealershipRecordForDedupe(d: Dealership): number {
  let score = 0;
  const name = d.name.trim();
  const loc = (d.location ?? '').trim();

  for (const main of VEHICLE_MAIN_DEALERSHIP_NAMES) {
    if (normalizeDealershipKey(main) === normalizeDealershipKey(name)) {
      score += 120;
      break;
    }
  }

  if (loc.length >= 40) {
    score += 50;
  } else if (loc.length >= 15) {
    score += 30;
  } else if (loc.length >= 5) {
    score += 10;
  }

  if (/^[A-ZÁÉÍÓÚÑ]/.test(name)) {
    score += 15;
  }
  if (name.split(/\s+/).length >= 2) {
    score += 5;
  }

  if (d.id != null && d.id > 0) {
    score += Math.min(d.id / 500, 8);
  }

  if (d.state?.trim()) {
    score += 25;
  }
  if (d.phone?.trim()) {
    score += 20;
  }
  if (d.image_url?.trim()) {
    score += 30;
  }
  if (d.latitude != null && d.longitude != null) {
    score += 25;
  }

  return score;
}

/** Una entrada por sucursal real; ante duplicados conserva el registro más completo. */
export function dedupeDealershipCatalog(catalog: Dealership[]): Dealership[] {
  const buckets = new Map<string, Dealership>();

  for (const dealership of catalog) {
    const bucketKey = dealershipDedupeBucketKey(dealership.name);
    if (!bucketKey || bucketKey.length < 2) {
      continue;
    }

    const current = buckets.get(bucketKey);
    if (
      !current ||
      scoreDealershipRecordForDedupe(dealership) > scoreDealershipRecordForDedupe(current)
    ) {
      buckets.set(bucketKey, dealership);
    }
  }

  return sortDealershipsByName([...buckets.values()]);
}

/** Sucursales principales conocidas (fallback si la API no responde). */
export function resolveMainDealershipsFromCatalog(catalog: Dealership[]): Dealership[] {
  const resolved: Dealership[] = [];

  for (const mainName of VEHICLE_MAIN_DEALERSHIP_NAMES) {
    const canonical = canonicalDealershipName(mainName);
    const fromCatalog =
      catalog.find((d) => normalizeDealershipKey(d.name) === normalizeDealershipKey(mainName)) ??
      catalog.find((d) => d.name === canonical);

    if (fromCatalog) {
      resolved.push(fromCatalog);
      continue;
    }

    resolved.push({
      name: canonical,
      location: vehicleLocationFallbackForDealershipName(canonical),
      description: null,
      created_at: new Date(),
    });
  }

  return sortDealershipsByName(resolved);
}

/**
 * Usuario puede elegir cualquier sucursal del catálogo API (nuevas altas incluidas).
 * Alineado con DealershipAccessService::INVENTORY_BYPASS_ROLES en backend.
 */
export function hasUnrestrictedVehicleDealershipAccess(
  email: string | null | undefined,
  role: string | null | undefined,
): boolean {
  if (isVehicleInventoryBypassRole(role)) {
    return true;
  }
  const key = (email ?? '').trim().toLowerCase();
  const byEmail = VEHICLE_DEALERSHIP_SELECTABLE_NAMES_BY_EMAIL[key];
  return byEmail != null && byEmail.length >= VEHICLE_MAIN_DEALERSHIP_NAMES.length;
}

/**
 * Opciones del select según rol/permiso y asignación en admin.
 * - Acceso sin restricción → todo el catálogo API (sucursales nuevas visibles al crearse).
 * - Resto → solo sucursales asignadas al usuario (pivote dealership_user).
 */
export function resolveSelectableDealershipsForVehicleForm(
  catalog: Dealership[],
  email: string | null | undefined,
  role: string | null | undefined,
  assigned: Dealership[],
  fallbackNames: readonly string[],
): Dealership[] {
  if (hasUnrestrictedVehicleDealershipAccess(email, role)) {
    if (catalog.length > 0) {
      return dedupeDealershipCatalog(catalog);
    }
    return resolveMainDealershipsFromCatalog(catalog);
  }

  if (assigned.length > 0) {
    return dedupeDealershipCatalog(assigned);
  }

  if (fallbackNames.length > 0) {
    if (catalog.length > 0) {
      const fallbackKeys = new Set(fallbackNames.map(normalizeDealershipKey));
      const fromFallback = catalog.filter((d) => fallbackKeys.has(normalizeDealershipKey(d.name)));
      if (fromFallback.length > 0) {
        return dedupeDealershipCatalog(fromFallback);
      }
    }
    return sortDealershipsByName(
      fallbackNames.map((name) => ({
        name: canonicalDealershipName(name),
        location: vehicleLocationFallbackForDealershipName(name),
        description: null,
        created_at: new Date(),
      })),
    );
  }

  return [];
}

export function readSignedInUserRole(): string | null {
  try {
    const role = localStorage.getItem('role')?.trim();
    return role || null;
  } catch {
    return null;
  }
}

export function isVehicleInventoryBypassRole(role: string | null | undefined): boolean {
  const key = (role ?? '').trim().toLowerCase();
  return VEHICLE_INVENTORY_BYPASS_ROLES.includes(key);
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
      return dedupeDealershipCatalog(byId);
    }
  }

  const apiNames = assignedNames.map(canonicalDealershipName);
  if (apiNames.length > 0 && catalog.length > 0) {
    const exact = catalog.filter((d) => apiNames.includes(d.name));
    if (exact.length > 0) {
      return dedupeDealershipCatalog(exact);
    }

    const keySet = new Set(apiNames.map(normalizeDealershipKey));
    const byNormalized = catalog.filter((d) => keySet.has(normalizeDealershipKey(d.name)));
    if (byNormalized.length > 0) {
      return dedupeDealershipCatalog(byNormalized);
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
    return dedupeDealershipCatalog(resolved);
  }

  if (!hasApiAssignment && fallbackNames.length > 0 && catalog.length > 0) {
    const fallbackKeys = new Set(fallbackNames.map(normalizeDealershipKey));
    const fromFallback = catalog.filter((d) => fallbackKeys.has(normalizeDealershipKey(d.name)));
    if (fromFallback.length > 0) {
      return dedupeDealershipCatalog(fromFallback);
    }
  }

  return [];
}

/** Estado (ubicación corta del vehículo) según nombre de sucursal conocida. */
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
  if (canonical === VEHICLE_DEALERSHIP_VECSA_OAXACA) {
    return 'Oaxaca';
  }
  if (canonical === VEHICLE_DEALERSHIP_VECSA_VERACRUZ) {
    return 'Veracruz';
  }
  return '';
}

/** Etiqueta de ubicación del vehículo: estado, no la dirección postal de la sucursal. */
export function vehicleLocationLabelForDealership(
  dealership: Pick<Dealership, 'name'> & { location?: string | null; state?: string | null },
): string {
  const fromKnownName = vehicleLocationFallbackForDealershipName(dealership.name);
  if (fromKnownName) {
    return fromKnownName;
  }
  const state = dealership.state?.trim();
  if (state) {
    return state;
  }
  return dealership.location?.trim() ?? '';
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
