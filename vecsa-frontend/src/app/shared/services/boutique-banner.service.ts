import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { GralResponse, BoutiqueBannersResponse } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueBannerService {

  baseUrl = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<BoutiqueBannersResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<BoutiqueBannersResponse>(`${this.baseUrl}/api/boutique/admin/banners/search`, {}, { headers });
  }

  public publicList(): Observable<BoutiqueBannersResponse> {
    return this._http.post<BoutiqueBannersResponse>(`${this.baseUrl}/api/boutique/banners`, {});
  }

  public store(formData: FormData): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/boutique/admin/banners/store`, formData, { headers });
  }

  public update(formData: FormData): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/boutique/admin/banners/update`, formData, { headers });
  }

  public delete(uuid: string): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/boutique/admin/banners/delete`, { uuid }, { headers });
  }

  public sortUpdate(image_order: { uuid: string; sort_id: number }[]): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/boutique/admin/banners/sort_update`, { image_order }, { headers });
  }

  public toggle(uuid: string): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/boutique/admin/banners/toggle`, { uuid }, { headers });
  }
}
