import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class IncadeaSyncService {
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

  triggerSync(filters: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/boutique/admin/incadea/sync`, filters, { headers: this.headers });
  }

  getLogs(): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/boutique/admin/incadea/logs`, {}, { headers: this.headers });
  }

  getConfig(): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/boutique/admin/incadea/config`, {}, { headers: this.headers });
  }

  updateConfig(config: any): Observable<any> {
    return this.http.post(`${this.baseUrl}/api/boutique/admin/incadea/update_config`, config, { headers: this.headers });
  }
}
