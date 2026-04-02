import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class ApiMonitorService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private get headers(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      Authorization: `Bearer ${token}`,
    });
  }

  getLogs(params: any = {}): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/developer/monitor/logs`, params, { headers: this.headers });
  }

  getStats(): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/developer/monitor/stats`, {}, { headers: this.headers });
  }

  getHealth(): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/developer/monitor/health`, {}, { headers: this.headers });
  }
}
