import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { OrdersResponse, OrderDetailResponse } from '../interfaces/orders.interface';

@Injectable({
  providedIn: 'root'
})
export class OrdersService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<OrdersResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<OrdersResponse>(`${this.url}/api/boutique/orders/search`, {}, { headers });
  }

  public detail(uuid: string): Observable<OrderDetailResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<OrderDetailResponse>(`${this.url}/api/boutique/orders/detail`, { uuid }, { headers });
  }
}
