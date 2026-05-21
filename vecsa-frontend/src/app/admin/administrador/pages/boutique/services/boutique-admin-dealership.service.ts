import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';
import { BoutiqueDealershipSummary } from '../../../../../boutique/interfaces/boutique.interfaces';

@Injectable({ providedIn: 'root' })
export class BoutiqueAdminDealershipService {
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

  list(): Observable<{ data?: { dealerships?: BoutiqueDealershipSummary[] } }> {
    return this.http.post(
      `${this.baseUrl}/api/boutique/admin/dealerships/list`,
      {},
      { headers: this.headers }
    );
  }

  updateWhatsapp(id: number, whatsapp_phone: string): Observable<unknown> {
    return this.http.post(
      `${this.baseUrl}/api/boutique/admin/dealerships/update_whatsapp`,
      { id, whatsapp_phone },
      { headers: this.headers }
    );
  }
}
