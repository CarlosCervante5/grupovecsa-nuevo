/** Respuesta de POST /api/boutique/checkout/openpay_public_config */
export interface BoutiqueOpenPayPublicConfig {
  merchant_id: string;
  public_key: string;
  sandbox: boolean;
  /** true si hay merchant_id y public_key configurados para el modo actual (sandbox/producción). */
  available: boolean;
}

/** Enlaces efectivos del checkbox legal del checkout (rutas internas o URLs absolutas). */
export interface BoutiqueCheckoutLegalPagesPayload {
  terms_url: string;
  privacy_url: string;
  returns_url: string;
}

/** Respuesta de POST /api/boutique/checkout/payment_methods_public */
export interface BoutiquePaymentMethodsPublicPayload {
  methods: {
    stripe: boolean;
    openpay: boolean;
    transferencia: boolean;
    sucursal: boolean;
  };
  openpay: BoutiqueOpenPayPublicConfig;
  transfer_bank?: BoutiqueTransferBankDetails;
  legal_pages?: BoutiqueCheckoutLegalPagesPayload;
}

export interface BoutiqueTransferBankDetails {
  bank_name: string;
  account_holder: string;
  clabe: string;
  account_number: string;
  instructions: string;
  configured: boolean;
}

export interface BoutiqueCategory {
  uuid: string;
  name: string;
  description: string | null;
  active: boolean;
  created_at: string;
  /** Categoría padre cuando es subcategoría (API admin). */
  parent?: { uuid: string; name: string } | null;
  children?: BoutiqueCategory[];
}

export interface BoutiqueProductImage {
  uuid: string;
  image_path: string;
  cloudinary_public_id: string | null;
  sort_id: number;
  status: 'pending' | 'uploaded' | 'failed';
}

export interface BoutiqueColorOption {
  name: string;
  hex: string;
}

export interface BoutiqueProductVariant {
  uuid: string;
  color: string | null;
  color_hex: string | null;
  size: string | null;
  stock: number;
  sku: string | null;
}

/** Sucursal donde está el inventario / venta del producto. */
export interface BoutiqueDealershipSummary {
  id: number;
  name: string;
  location: string;
  state?: string | null;
  whatsapp_phone?: string | null;
}

export interface BoutiqueProduct {
  uuid: string;
  /** Sucursal (admin); null en productos legacy o globales */
  dealership_id?: number | null;
  dealership?: BoutiqueDealershipSummary | null;
  category: BoutiqueCategory;
  name: string;
  description: string | null;
  price: number;
  sku: string;
  stock: number;
  active: boolean;
  images: BoutiqueProductImage[];
  variants?: BoutiqueProductVariant[];
  created_at: string;
}

export interface BoutiqueCartItem {
  uuid: string;
  product: BoutiqueProduct;
  quantity: number;
  subtotal: number;
}

export interface BoutiqueCart {
  uuid: string;
  items: BoutiqueCartItem[];
  total: number;
}

export interface BoutiqueOrderItem {
  uuid: string;
  product_name: string;
  product_sku: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
}

export interface BoutiquePayment {
  uuid: string;
  method: 'stripe' | 'transferencia' | 'sucursal' | 'openpay';
  amount: number;
  status: 'pendiente' | 'completado' | 'fallido' | 'reembolsado';
  stripe_payment_intent_id: string | null;
  transaction_reference: string | null;
  confirmed_at: string | null;
}

export interface BoutiqueShipment {
  uuid: string;
  delivery_method: 'envio_domicilio' | 'recoleccion_sucursal';
  carrier_name: string | null;
  tracking_number: string | null;
  envia_label_url: string | null;
  status: 'pendiente' | 'en_preparacion' | 'enviado' | 'entregado';
  estimated_delivery: string | null;
  dealership: any | null;
}

export interface BoutiqueOrderUser {
  uuid: string;
  name: string;
  email: string;
}

export interface BoutiqueOrder {
  uuid: string;
  order_number: string;
  status: 'pendiente' | 'pagado' | 'en_preparacion' | 'enviado' | 'entregado' | 'cancelado';
  subtotal: number;
  shipping_cost: number;
  total: number;
  delivery_method: 'envio_domicilio' | 'recoleccion_sucursal';
  shipping_name: string | null;
  shipping_address: string | null;
  shipping_city: string | null;
  shipping_state: string | null;
  shipping_zip: string | null;
  shipping_phone: string | null;
  notes: string | null;
  user?: BoutiqueOrderUser | null;
  items: BoutiqueOrderItem[];
  order_items?: BoutiqueOrderItem[];
  payment: BoutiquePayment | null;
  shipment: BoutiqueShipment | null;
  created_at: string;
}

export interface BoutiqueInventoryMovement {
  uuid: string;
  product: BoutiqueProduct;
  previous_stock: number;
  new_stock: number;
  quantity_change: number;
  reason: string;
  reference_type: string | null;
  reference_uuid: string | null;
  created_at: string;
}

// API Response wrappers
export interface ApiResponse<T> {
  status: number;
  message: string;
  data: T;
}

export interface PaginatedData<T> {
  current_page: number;
  data: T[];
  last_page: number;
  per_page: number;
  total: number;
}

export interface ShippingQuote {
  carrier: string;
  service: string;
  price: number;
  estimated_days: number;
}

export interface PaymentIntentResponse {
  client_secret: string;
  order_uuid: string;
}

export interface TrackingInfo {
  tracking_number: string;
  carrier: string;
  status: string;
  events: TrackingEvent[];
}

export interface TrackingEvent {
  date: string;
  description: string;
  location: string | null;
}
