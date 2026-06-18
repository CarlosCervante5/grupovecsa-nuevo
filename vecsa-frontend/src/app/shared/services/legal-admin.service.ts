import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { environment } from '@environments/environment';

export interface LegalDocumentPayload {
  slug: string;
  title: string;
  body_html: string;
  meta_description: string | null;
  is_published: boolean;
  public_path?: string;
  updated_at?: string | null;
}

export interface LegalDocumentListItem {
  slug: string;
  title: string;
  public_path: string;
  is_published: boolean;
  has_content: boolean;
  updated_at: string | null;
}

@Injectable({ providedIn: 'root' })
export class LegalAdminService {
  private readonly baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  list(): Observable<LegalDocumentListItem[]> {
    return this.http
      .get<{ data?: { documents?: LegalDocumentListItem[] } }>(
        `${this.baseUrl}/api/admin/legal`,
        { headers: this.authHeaders() },
      )
      .pipe(map((res) => res?.data?.documents ?? []));
  }

  get(slug: string): Observable<LegalDocumentPayload> {
    return this.http
      .get<{ data?: { document?: LegalDocumentPayload } }>(
        `${this.baseUrl}/api/admin/legal/${slug}`,
        { headers: this.authHeaders() },
      )
      .pipe(map((res) => res?.data?.document as LegalDocumentPayload));
  }

  update(slug: string, payload: Partial<LegalDocumentPayload>): Observable<LegalDocumentPayload> {
    return this.http
      .put<{ data?: { document?: LegalDocumentPayload } }>(
        `${this.baseUrl}/api/admin/legal/${slug}`,
        payload,
        { headers: this.authHeaders() },
      )
      .pipe(map((res) => res?.data?.document as LegalDocumentPayload));
  }

  getPublic(slug: string): Observable<LegalDocumentPayload> {
    return this.http
      .get<{ data?: { document?: LegalDocumentPayload } }>(`${this.baseUrl}/api/legal/${slug}`)
      .pipe(map((res) => res?.data?.document as LegalDocumentPayload));
  }

  private authHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token') ?? '';
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }
}
