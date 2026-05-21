import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  BoutiqueOrder,
  ShippingQuote,
  PaymentIntentResponse,
  BoutiqueOpenPayPublicConfig,
  BoutiquePaymentMethodsPublicPayload,
} from '../interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueCheckoutService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public shippingQuote(params: {
    shipping_address: string;
    shipping_city: string;
    shipping_state: string;
    shipping_zip: string;
  }): Observable<ApiResponse<ShippingQuote[]>> {
    // Use public endpoint when not logged in
    const isLoggedIn = !!localStorage.getItem('user_token');
    const endpoint = isLoggedIn
      ? `${this.url}/api/boutique/checkout/shipping_quote`
      : `${this.url}/api/boutique/checkout/shipping_quote_public`;
    const options = isLoggedIn ? { headers: this.getHeaders() } : {};
    return this._http.post<ApiResponse<ShippingQuote[]>>(endpoint, params, options);
  }

  public createOrder(params: {
    delivery_method: string;
    payment_method: string;
    shipping_name?: string;
    shipping_address?: string;
    shipping_city?: string;
    shipping_state?: string;
    shipping_zip?: string;
    shipping_phone?: string;
    dealership_uuid?: string;
    notes?: string;
  }): Observable<ApiResponse<BoutiqueOrder>> {
    return this._http.post<ApiResponse<BoutiqueOrder>>(
      `${this.url}/api/boutique/checkout/create_order`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public createGuestOrder(params: {
    guest_name: string;
    guest_email: string;
    delivery_method: string;
    payment_method: string;
    items: Array<{ product_uuid: string; quantity: number; variant_uuid?: string }>;
    shipping_name?: string;
    shipping_address?: string;
    shipping_city?: string;
    shipping_state?: string;
    shipping_zip?: string;
    shipping_phone?: string;
    dealership_uuid?: string;
    shipping_option?: any;
    notes?: string;
  }): Observable<ApiResponse<BoutiqueOrder>> {
    return this._http.post<ApiResponse<BoutiqueOrder>>(
      `${this.url}/api/boutique/checkout/create_guest_order`,
      params
    );
  }

  public createPaymentIntent(order_uuid: string): Observable<ApiResponse<PaymentIntentResponse>> {
    return this._http.post<ApiResponse<PaymentIntentResponse>>(
      `${this.url}/api/boutique/checkout/payment_intent`,
      { order_uuid },
      { headers: this.getHeaders() }
    );
  }

  /** Sin auth: datos públicos para mostrar OpenPay en checkout cuando la tienda está configurada. */
  public getOpenPayPublicConfig(): Observable<ApiResponse<BoutiqueOpenPayPublicConfig>> {
    return this._http.post<ApiResponse<BoutiqueOpenPayPublicConfig>>(
      `${this.url}/api/boutique/checkout/openpay_public_config`,
      {}
    );
  }

  /** Sin auth: métodos habilitados (Stripe/OpenPay por llaves; transferencia/sucursal por admin). */
  public getPaymentMethodsPublic(): Observable<ApiResponse<BoutiquePaymentMethodsPublicPayload>> {
    return this._http.post<ApiResponse<BoutiquePaymentMethodsPublicPayload>>(
      `${this.url}/api/boutique/checkout/payment_methods_public`,
      {}
    );
  }

  /** Sin auth: confirma cargo OpenPay tras tokenizar en el navegador (invitados y sesión iniciada). */
  public confirmOpenPayCharge(params: {
    order_uuid: string;
    source_id: string;
    device_session_id: string;
  }): Observable<ApiResponse<{ order: BoutiqueOrder }>> {
    return this._http.post<ApiResponse<{ order: BoutiqueOrder }>>(
      `${this.url}/api/boutique/checkout/openpay_confirm_charge`,
      params
    );
  }

  /**
   * Cobra con OpenPay y crea el pedido solo si el pago se confirma (sin crear pedido antes).
   */
  public placeOpenPayOrder(
    checkout: Record<string, unknown> & {
      source_id: string;
      device_session_id: string;
    },
  ): Observable<
    ApiResponse<{
      order?: BoutiqueOrder;
      requires_3ds?: boolean;
      redirect_url?: string;
      order_number?: string;
    }>
  > {
    const token = localStorage.getItem('user_token');
    const options = token ? { headers: this.getHeaders() } : {};

    return this._http.post<
      ApiResponse<{
        order?: BoutiqueOrder;
        requires_3ds?: boolean;
        redirect_url?: string;
        order_number?: string;
      }>
    >(`${this.url}/api/boutique/checkout/openpay_place_order`, checkout, options);
  }
}
