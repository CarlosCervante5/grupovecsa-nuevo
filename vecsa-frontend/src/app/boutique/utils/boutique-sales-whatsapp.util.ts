/** Fallback si ninguna sucursal del pedido tiene WhatsApp configurado. */
export const BOUTIQUE_SALES_WHATSAPP_FALLBACK = '527712843793';

export interface BoutiqueDealershipContact {
  id?: number;
  name?: string;
  location?: string;
  whatsapp_phone?: string | null;
}

export function normalizeWhatsAppPhone(raw: string): string {
  const digits = String(raw || '').replace(/\D/g, '');
  if (digits.length === 10) {
    return `52${digits}`;
  }
  return digits;
}

/**
 * Teléfono para wa.me: sucursal de recolección, una sola sucursal en el carrito, o fallback.
 */
export function resolveBoutiqueSalesWhatsAppPhone(options: {
  pickupDealership?: BoutiqueDealershipContact | null;
  cartItems?: Array<{ product?: { dealership?: BoutiqueDealershipContact | null } }>;
  fallback?: string;
}): string {
  const pickup = options.pickupDealership?.whatsapp_phone?.trim();
  if (pickup) {
    return normalizeWhatsAppPhone(pickup);
  }

  const phones = new Set<string>();
  for (const item of options.cartItems ?? []) {
    const w = item.product?.dealership?.whatsapp_phone?.trim();
    if (w) {
      phones.add(normalizeWhatsAppPhone(w));
    }
  }
  if (phones.size === 1) {
    return [...phones][0];
  }

  const fb = options.fallback?.trim() || BOUTIQUE_SALES_WHATSAPP_FALLBACK;
  return normalizeWhatsAppPhone(fb);
}

export function boutiqueSalesWhatsAppUrl(message: string, phone?: string): string {
  const normalized = phone ? normalizeWhatsAppPhone(phone) : normalizeWhatsAppPhone(BOUTIQUE_SALES_WHATSAPP_FALLBACK);
  return `https://api.whatsapp.com/send?phone=${normalized}&text=${encodeURIComponent(message)}`;
}

export function formatMxn(amount: number): string {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    maximumFractionDigits: 2,
  }).format(amount);
}
