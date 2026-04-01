import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { AppointmentsResponse } from '../interfaces/appointments.interface';

@Injectable({
  providedIn: 'root'
})
export class AppointmentsService {

  private url: string = environment.baseUrl;

  constructor(private _http: HttpClient) {}

  public search(): Observable<AppointmentsResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<AppointmentsResponse>(
      `${this.url}/api/appointment/search`,
      { type: '', keyword: '', paginate: 100 },
      { headers }
    );
  }

  public searchByType(type: string): Observable<AppointmentsResponse> {
    const user_token = localStorage.getItem('user_token');
    const headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);
    return this._http.post<AppointmentsResponse>(
      `${this.url}/api/appointment/search`,
      { type, keyword: '', paginate: 100 },
      { headers }
    );
  }
}
