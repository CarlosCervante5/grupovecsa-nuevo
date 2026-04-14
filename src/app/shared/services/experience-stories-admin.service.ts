import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

export interface ExperienceStoryRow {
  uuid: string;
  title: string;
  excerpt: string | null;
  body_html: string | null;
  image_path: string | null;
  url_name: string;
  status: string;
  category: string;
  wp_import_id: number | null;
  wp_category_label?: string | null;
  wp_tags?: string[] | null;
  event_begin_date?: string | null;
  event_end_date?: string | null;
  created_at: string;
}

export interface ExperienceStoriesSearchResponse {
  status: number;
  message: string;
  data: {
    posts: {
      data: ExperienceStoryRow[];
      total: number;
      last_page: number;
      current_page: number;
      per_page?: number;
    };
  };
}

export interface ExperienceStoryMutationResponse {
  status: number;
  message: string;
  data: { post: ExperienceStoryRow };
}

export interface ExperienceImportResponse {
  status: number;
  message: string;
  data: { imported: number; skipped: number; errors: string[] };
}

@Injectable({ providedIn: 'root' })
export class ExperienceStoriesAdminService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private headers(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  search(page = 1, perPage = 20): Observable<ExperienceStoriesSearchResponse> {
    const params = new HttpParams().set('page', page).set('per_page', perPage);
    return this.http.post<ExperienceStoriesSearchResponse>(
      `${this.baseUrl}/api/experience/admin/stories/search`,
      {},
      { headers: this.headers(), params }
    );
  }

  store(body: Record<string, unknown>): Observable<ExperienceStoryMutationResponse> {
    return this.http.post<ExperienceStoryMutationResponse>(
      `${this.baseUrl}/api/experience/admin/stories/store`,
      body,
      { headers: this.headers().set('Content-Type', 'application/json'), withCredentials: false }
    );
  }

  update(body: Record<string, unknown>): Observable<ExperienceStoryMutationResponse> {
    return this.http.post<ExperienceStoryMutationResponse>(
      `${this.baseUrl}/api/experience/admin/stories/update`,
      body,
      { headers: this.headers().set('Content-Type', 'application/json') }
    );
  }

  delete(uuid: string): Observable<{ status: number; message: string }> {
    return this.http.post<{ status: number; message: string }>(
      `${this.baseUrl}/api/experience/admin/stories/delete`,
      { uuid },
      { headers: this.headers().set('Content-Type', 'application/json') }
    );
  }

  importWordpress(baseUrl?: string, limit?: number): Observable<ExperienceImportResponse> {
    const body: Record<string, unknown> = {};
    if (baseUrl) body.base_url = baseUrl;
    if (limit != null) body.limit = limit;
    return this.http.post<ExperienceImportResponse>(
      `${this.baseUrl}/api/experience/admin/stories/import_wordpress`,
      body,
      { headers: this.headers().set('Content-Type', 'application/json') }
    );
  }
}
