import { BoutiqueCategory } from '../interfaces/boutique.interfaces';

export interface CategorySelection {
  parentUuid: string;
  subUuid: string;
  sub2Uuid: string;
  leafUuid: string;
}

export function isRootCategory(cat: BoutiqueCategory | null | undefined): boolean {
  if (!cat) {
    return false;
  }
  if (cat.parent?.uuid) {
    return false;
  }
  const pid = (cat as BoutiqueCategory & { parent_id?: number | null }).parent_id;
  return pid == null || pid === 0;
}

export function sortCategoriesByName(list: BoutiqueCategory[]): BoutiqueCategory[] {
  return [...list].sort((a, b) => a.name.localeCompare(b.name, 'es'));
}

export function getChildCategories(categories: BoutiqueCategory[], parentUuid: string | null): BoutiqueCategory[] {
  if (!parentUuid) {
    return sortCategoriesByName(categories.filter((c) => isRootCategory(c)));
  }
  return sortCategoriesByName(categories.filter((c) => c.parent?.uuid === parentUuid));
}

export function categoryHasChildren(categories: BoutiqueCategory[], categoryUuid: string): boolean {
  return categories.some((c) => c.parent?.uuid === categoryUuid);
}

/** Reconstruye la selección en cascada a partir del uuid guardado en el producto. */
export function resolveCategorySelection(
  categoryUuid: string,
  categories: BoutiqueCategory[],
): CategorySelection {
  const empty: CategorySelection = { parentUuid: '', subUuid: '', sub2Uuid: '', leafUuid: '' };
  if (!categoryUuid) {
    return empty;
  }

  const chain: BoutiqueCategory[] = [];
  let current = categories.find((c) => c.uuid === categoryUuid);
  if (!current) {
    return empty;
  }

  while (current) {
    chain.unshift(current);
    const parentUuid: string | undefined = current.parent?.uuid;
    if (!parentUuid) {
      break;
    }
    current = categories.find((c) => c.uuid === parentUuid);
    if (!current) {
      break;
    }
  }

  return {
    parentUuid: chain[0]?.uuid ?? '',
    subUuid: chain[1]?.uuid ?? '',
    sub2Uuid: chain[2]?.uuid ?? '',
    leafUuid: categoryUuid,
  };
}

/**
 * Devuelve el uuid de categoría más específico válido para guardar el producto.
 * Exige seleccionar hasta la hoja cuando existen subniveles.
 */
export function resolveLeafCategoryUuid(
  parentUuid: string,
  subUuid: string,
  sub2Uuid: string,
  categories: BoutiqueCategory[],
): string {
  if (!parentUuid) {
    return '';
  }

  if (sub2Uuid) {
    return sub2Uuid;
  }

  if (subUuid) {
    return categoryHasChildren(categories, subUuid) ? '' : subUuid;
  }

  return categoryHasChildren(categories, parentUuid) ? '' : parentUuid;
}

export function categorySelectionError(
  parentUuid: string,
  subUuid: string,
  sub2Uuid: string,
  categories: BoutiqueCategory[],
): string | null {
  if (!parentUuid) {
    return 'Selecciona la categoría principal.';
  }
  if (categoryHasChildren(categories, parentUuid) && !subUuid) {
    return 'Selecciona una subcategoría.';
  }
  if (subUuid && categoryHasChildren(categories, subUuid) && !sub2Uuid) {
    return 'Selecciona la subcategoría específica.';
  }
  const leaf = resolveLeafCategoryUuid(parentUuid, subUuid, sub2Uuid, categories);
  if (!leaf) {
    return 'La categoría seleccionada no es válida.';
  }
  return null;
}
