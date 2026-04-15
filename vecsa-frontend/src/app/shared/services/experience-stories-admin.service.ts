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

export interface ExperienceStoryPostTypeOption {
  value: string;
  label: string;
}

export interface ExperienceStoriesMetaResponse {
  status: number;
  message: string;
  data: {
    wp_category_options: string[];
    event_agenda_keywords: string[];
    post_types: ExperienceStoryPostTypeOption[];
  };
}

@Injectable({ providedIn: 'root' })
export class ExperienceStoriesAdminService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private headers(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token}`);
  }

  getMeta(): Observable<ExperienceStoriesMetaResponse> {
    return this.http.get<ExperienceStoriesMetaResponse>(
      `${this.baseUrl}/api/experience/admin/stories/meta`,
      { headers: this.headers() }
    );
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

  /**
   * Crear historia con imagen destacada (multipart). No fijar Content-Type (boundary).
   */
  storeWithImage(body: Record<string, unknown>, imageFile: File): Observable<ExperienceStoryMutationResponse> {
    const fd = this.storyFormData(body, { includeImageUrl: !imageFile });
    fd.append('image', imageFile, imageFile.name);
    return this.http.post<ExperienceStoryMutationResponse>(
      `${this.baseUrl}/api/experience/admin/stories/store`,
      fd,
      { headers: this.headersOnlyAuth() }
    );
  }

  update(body: Record<string, unknown>): Observable<ExperienceStoryMutationResponse> {
    return this.http.post<ExperienceStoryMutationResponse>(
      `${this.baseUrl}/api/experience/admin/stories/update`,
      body,
      { headers: this.headers().set('Content-Type', 'application/json') }
    );
  }

  /**
   * Actualizar historia; si se envía imageFile, reemplaza la imagen en el servidor.
   */
  updateWithOptionalImage(body: Record<string, unknown>, imageFile: File | null): Observable<ExperienceStoryMutationResponse> {
    if (!imageFile) {
      return this.update(body);
    }
    const fd = this.storyFormData(body, { includeImageUrl: false });
    fd.append('image', imageFile, imageFile.name);
    return this.http.post<ExperienceStoryMutationResponse>(
      `${this.baseUrl}/api/experience/admin/stories/update`,
      fd,
      { headers: this.headersOnlyAuth() }
    );
  }

  private headersOnlyAuth(): HttpHeaders {
    const token = localStorage.getItem('user_token');
    return new HttpHeaders().set('Authorization', `Bearer ${token ?? ''}`);
  }

  private storyFormData(
    body: Record<string, unknown>,
    opts: { includeImageUrl: boolean }
  ): FormData {
    const fd = new FormData();
    const appendIf = (key: string, v: unknown) => {
      if (v === undefined || v === null) return;
      const s = String(v).trim();
      if (s === '') return;
      fd.append(key, s);
    };

    fd.append('title', String(body['title'] ?? '').trim());
    appendIf('url_name', body['url_name']);
    appendIf('excerpt', body['excerpt']);
    appendIf('body_html', body['body_html']);
    appendIf('status', body['status']);
    appendIf('event_begin_date', body['event_begin_date']);
    appendIf('event_end_date', body['event_end_date']);
    appendIf('wp_category_label', body['wp_category_label']);

    if (opts.includeImageUrl) {
      appendIf('image_url', body['image_url']);
    }

    const uuid = body['uuid'];
    if (uuid != null && String(uuid).trim() !== '') {
      fd.append('uuid', String(uuid).trim());
    }

    return fd;
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
