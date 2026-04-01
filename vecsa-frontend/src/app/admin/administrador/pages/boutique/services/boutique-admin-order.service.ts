import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  PaginatedData,
  BoutiqueOrder
} from '../../../../../boutique/interfaces/boutique.interfaces';

export interface OrderMetrics {
  total_orders: number;
  pending_orders: number;
  revenue: number;
}

@Injectable({
  providedIn: 'root'
})
export class BoutiqueAdminOrderService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public search(params: {
    status?: string;
    search?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
  }): Observable<ApiResponse<PaginatedData<BoutiqueOrder>>> {
    return this._http.post<ApiResponse<PaginatedData<BoutiqueOrder>>>(
      `${this.url}/api/boutique/admin/orders/search`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public detail(uuid: string): Observable<ApiResponse<BoutiqueOrder>> {
    return this._http.post<ApiResponse<BoutiqueOrder>>(
      `${this.url}/api/boutique/admin/orders/detail`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }

  public updateStatus(params: {
    uuid: string;
    status: string;
  }): Observable<ApiResponse<BoutiqueOrder>> {
    return this._http.post<ApiResponse<BoutiqueOrder>>(
      `${this.url}/api/boutique/admin/orders/update_status`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public generateLabel(uuid: string): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/orders/generate_label`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }

  public metrics(params: {
    date_from?: string;
    date_to?: string;
  }): Observable<ApiResponse<OrderMetrics>> {
    return this._http.post<ApiResponse<OrderMetrics>>(
      `${this.url}/api/boutique/admin/orders/metrics`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public confirmManualPayment(params: {
    order_uuid: string;
    transaction_reference?: string;
  }): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/payments/confirm_manual`,
      params,
      { headers: this.getHeaders() }
    );
  }
}
