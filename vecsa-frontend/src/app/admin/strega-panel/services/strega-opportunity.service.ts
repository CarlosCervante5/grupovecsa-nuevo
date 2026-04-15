import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

@Injectable({ providedIn: 'root' })
export class StregaOpportunityService {
  private readonly baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private authHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  private toParams(record: Record<string, string | number | boolean | undefined>): HttpParams {
    let p = new HttpParams();
    Object.entries(record).forEach(([k, v]) => {
      if (v !== undefined && v !== '' && v !== null) {
        p = p.set(k, String(v));
      }
    });
    return p;
  }

  /** Rol en sesión: strega-administrator | strega-manager | strega-seller */
  searchLeads(
    role: string,
    params: Record<string, string | number | boolean | undefined>,
  ): Observable<unknown> {
    let path = 'search_seller';
    if (role === 'strega-administrator') {
      path = 'search_administrator';
    } else if (role === 'strega-manager') {
      path = 'search_manager';
    }
    return this.http.get(`${this.baseUrl}/api/strega/leads/${path}`, {
      headers: this.authHeaders(),
      params: this.toParams(params),
    });
  }

  searchAppointments(
    params: Record<string, string | number | boolean | undefined>,
  ): Observable<unknown> {
    return this.http.get(`${this.baseUrl}/api/strega/appointments/search_manager`, {
      headers: this.authHeaders(),
      params: this.toParams(params),
    });
  }
}
