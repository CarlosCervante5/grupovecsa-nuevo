import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  PaginatedData,
  BoutiqueInventoryMovement
} from '../../../../../boutique/interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueAdminInventoryService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public update(params: {
    product_uuid: string;
    new_stock: number;
    reason: string;
  }): Observable<ApiResponse<any>> {
    return this._http.post<ApiResponse<any>>(
      `${this.url}/api/boutique/admin/inventory/update`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public movements(params: {
    product_uuid: string;
    page?: number;
    per_page?: number;
  }): Observable<ApiResponse<PaginatedData<BoutiqueInventoryMovement>>> {
    return this._http.post<ApiResponse<PaginatedData<BoutiqueInventoryMovement>>>(
      `${this.url}/api/boutique/admin/inventory/movements`,
      params,
      { headers: this.getHeaders() }
    );
  }
}
