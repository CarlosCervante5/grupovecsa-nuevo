import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { QuotationsResponse } from '../interfaces/quotations.interface';

@Injectable({
  providedIn: 'root'
})
export class QuotationsService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<QuotationsResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.get<QuotationsResponse>(`${this.url}/api/valuations/search`, { headers });
  }
}
