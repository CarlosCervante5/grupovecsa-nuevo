import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { GralResponse, HomeSlidesResponse } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class HomeSlideService {

  baseUrl = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<HomeSlidesResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<HomeSlidesResponse>(`${this.baseUrl}/api/home_slides/search`, {}, { headers });
  }

  public store(formData: FormData): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_slides`, formData, { headers });
  }

  public update(formData: FormData): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_slides/update`, formData, { headers });
  }

  public delete(uuid: string): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_slides/delete`, { uuid }, { headers });
  }

  public sortUpdate(image_order: { uuid: string; sort_id: number }[]): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_slides/sort_update`, { image_order }, { headers });
  }

  public toggle(uuid: string): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_slides/toggle`, { uuid }, { headers });
  }
}
