import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { ApiResponse, TrackingInfo } from '../interfaces/boutique.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueShippingService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  public track(order_uuid: string): Observable<ApiResponse<TrackingInfo>> {
    return this._http.post<ApiResponse<TrackingInfo>>(
      `${this.url}/api/boutique/shipping/track`,
      { order_uuid },
      { headers: this.getHeaders() }
    );
  }
}
