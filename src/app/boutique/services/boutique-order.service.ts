import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import {
  ApiResponse,
  PaginatedData,
  BoutiqueOrder
} from '../interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueOrderService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public search(params: {
    page?: number;
    per_page?: number;
  }): Observable<ApiResponse<PaginatedData<BoutiqueOrder>>> {
    return this._http.post<ApiResponse<PaginatedData<BoutiqueOrder>>>(
      `${this.url}/api/boutique/orders/search`,
      params,
      { headers: this.getHeaders() }
    );
  }

  public detail(uuid: string): Observable<ApiResponse<BoutiqueOrder>> {
    return this._http.post<ApiResponse<BoutiqueOrder>>(
      `${this.url}/api/boutique/orders/detail`,
      { uuid },
      { headers: this.getHeaders() }
    );
  }
}
