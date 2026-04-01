export interface Order {
  uuid: string;
  order_number: string;
  status: string;
  subtotal: string;
  shipping_cost: string;
  total: string;
  delivery_method: string;
  created_at: string;
  order_items_count: number;
}

export interface OrderDetail {
  uuid: string;
  order_number: string;
  status: string;
  subtotal: string;
  shipping_cost: string;
  total: string;
  delivery_method: string;
  shipping_name: string;
  shipping_address: string;
  shipping_city: string;
  shipping_state: string;
  shipping_zip: string;
  shipping_phone: string;
  notes: string;
  created_at: string;
  order_items: OrderItem[];
  payment: Payment | null;
  shipment: Shipment | null;
}

export interface OrderItem {
  uuid: string;
  product_name: string;
  quantity: number;
  unit_price: string;
  total: string;
}

export interface Payment {
  uuid: string;
  method: string;
  status: string;
  amount: string;
}

export interface Shipment {
  uuid: string;
  carrier: string;
  tracking_number: string;
  status: string;
}

export interface OrdersResponse {
  status: number;
  message: string;
  data: { orders: Order[] };
}

export interface OrderDetailResponse {
  status: number;
  message: string;
  data: { order: OrderDetail };
}
