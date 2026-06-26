import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { CarCareBannersResponse, GralResponse } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class CarCareBannerService {

  baseUrl = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<CarCareBannersResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<CarCareBannersResponse>(`${this.baseUrl}/api/carcare/admin/banners/search`, {}, { headers });
  }

  public publicList(): Observable<CarCareBannersResponse> {
    return this._http.post<CarCareBannersResponse>(`${this.baseUrl}/api/carcare/banners`, {});
  }

  public store(formData: FormData): Observable<GralResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/carcare/admin/banners/store`, formData, { headers });
  }

  public update(formData: FormData): Observable<GralResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/carcare/admin/banners/update`, formData, { headers });
  }

  public delete(uuid: string): Observable<GralResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/carcare/admin/banners/delete`, { uuid }, { headers });
  }

  public sortUpdate(image_order: { uuid: string; sort_id: number }[]): Observable<GralResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/carcare/admin/banners/sort_update`, { image_order }, { headers });
  }

  public toggle(uuid: string): Observable<GralResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/carcare/admin/banners/toggle`, { uuid }, { headers });
  }
}
