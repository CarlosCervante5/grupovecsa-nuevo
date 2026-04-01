import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { GralResponse, HomeTestimonialsResponse } from '@interfaces/admin.interfaces';

@Injectable({
  providedIn: 'root'
})
export class HomeTestimonialService {

  baseUrl = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<HomeTestimonialsResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<HomeTestimonialsResponse>(`${this.baseUrl}/api/home_testimonials/search`, {}, { headers });
  }

  public store(formData: FormData): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_testimonials`, formData, { headers });
  }

  public delete(uuid: string): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_testimonials/delete`, { uuid }, { headers });
  }

  public sortUpdate(image_order: { uuid: string; sort_id: number }[]): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_testimonials/sort_update`, { image_order }, { headers });
  }

  public toggle(uuid: string): Observable<GralResponse> {
    let user_token = localStorage.getItem('user_token');
    let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<GralResponse>(`${this.baseUrl}/api/home_testimonials/toggle`, { uuid }, { headers });
  }
}
