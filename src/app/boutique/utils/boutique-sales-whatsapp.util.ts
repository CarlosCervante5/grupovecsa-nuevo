/** WhatsApp ventas boutique (sin pasarela en línea). */
export const BOUTIQUE_SALES_WHATSAPP_PHONE = '527712843793';

export function boutiqueSalesWhatsAppUrl(message: string): string {
  const phone = BOUTIQUE_SALES_WHATSAPP_PHONE.replace(/\D/g, '');
  return `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`;
}

export function formatMxn(amount: number): string {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    maximumFractionDigits: 2,
  }).format(amount);
}
