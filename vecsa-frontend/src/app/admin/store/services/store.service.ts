import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import {
  OrderSearchParams, ShipmentSearchParams, CustomerSearchParams,
  PointsSearchParams, PointAdjustment, CouponSearchParams,
  CouponCreate, RedemptionSearchParams
} from '../interfaces/store.interfaces';

@Injectable({ providedIn: 'root' })
export class StoreService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private get headers(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      Authorization: `Bearer ${token}`,
    });
  }

  private post(endpoint: string, body: any = {}): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/store-management/${endpoint}`, body, { headers: this.headers });
  }

  private boutiquePost(path: string, body: object = {}): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/boutique/admin/${path}`, body, { headers: this.headers });
  }

  // Dashboard
  getDashboardMetrics(params?: { date_from?: string; date_to?: string }): Observable<any> {
    return this.post('metrics', params || {});
  }

  // Pedidos
  searchOrders(params: OrderSearchParams): Observable<any> {
    return this.post('orders/search', params);
  }

  getOrderDetail(uuid: string): Observable<any> {
    return this.post('orders/detail', { uuid });
  }

  updateOrderStatus(uuid: string, status: string): Observable<any> {
    return this.post('orders/update_status', { uuid, status });
  }

  generateShippingLabel(uuid: string): Observable<any> {
    return this.post('orders/generate_label', { uuid });
  }

  // Envíos
  searchShipments(params: ShipmentSearchParams): Observable<any> {
    return this.post('shipments/search', params);
  }

  // Clientes
  searchCustomers(params: CustomerSearchParams): Observable<any> {
    return this.post('customers/search', params);
  }

  getCustomerDetail(uuid: string): Observable<any> {
    return this.post('customers/detail', { uuid });
  }

  getCustomerOrders(customerUuid: string): Observable<any> {
    return this.post('customers/orders', { uuid: customerUuid });
  }

  getCustomerPoints(customerUuid: string): Observable<any> {
    return this.post('points/customer_balance', { uuid: customerUuid });
  }

  // Puntos
  searchPoints(params: PointsSearchParams): Observable<any> {
    return this.post('points/search', params);
  }

  adjustPoints(data: PointAdjustment): Observable<any> {
    return this.post('points/adjust', data);
  }

  // Cupones
  searchCoupons(params: CouponSearchParams): Observable<any> {
    return this.post('coupons/search', params);
  }

  createCoupon(data: CouponCreate): Observable<any> {
    return this.post('coupons/store', data);
  }

  updateCoupon(uuid: string, data: Partial<CouponCreate>): Observable<any> {
    return this.post('coupons/update', { uuid, ...data });
  }

  deleteCoupon(uuid: string): Observable<any> {
    return this.post('coupons/delete', { uuid });
  }

  // Redenciones
  searchRedemptions(params: RedemptionSearchParams): Observable<any> {
    return this.post('redemptions/search', params);
  }

  updateRedemptionStatus(uuid: string, status: string): Observable<any> {
    return this.post('redemptions/update_status', { uuid, status });
  }

  /** OpenPay — documentación https://documents.openpay.mx/docs/api/ */
  getOpenpayConfig(): Observable<any> {
    return this.boutiquePost('openpay/config');
  }

  updateOpenpayConfig(payload: Record<string, unknown>): Observable<any> {
    return this.boutiquePost('openpay/update', payload);
  }

  /** Métodos de pago del checkout boutique (OpenPay, transferencia, sucursal). */
  getCheckoutPaymentMethodsConfig(): Observable<any> {
    return this.boutiquePost('checkout_payment_methods/config');
  }

  updateCheckoutPaymentMethods(payload: {
    boutique_checkout_openpay?: boolean;
    boutique_checkout_transferencia: boolean;
    boutique_checkout_sucursal: boolean;
  }): Observable<any> {
    return this.boutiquePost('checkout_payment_methods/update', payload);
  }

  updateTransferBankDetails(payload: Record<string, string>): Observable<any> {
    return this.boutiquePost('transfer_bank_details/update', payload);
  }
}
