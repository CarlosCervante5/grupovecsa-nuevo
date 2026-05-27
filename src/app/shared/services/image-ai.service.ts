import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '@environments/environment';

export type ImageAiActionId = 'remove_background' | 'enhance' | 'studio_white';

export type ImageAiTargetType = 'preview_only' | 'vehicle_image' | 'boutique_product_image';

export interface ImageAiAction {
  id: ImageAiActionId;
  label: string;
  description: string;
}

export interface ImageAiConfig {
  provider?: string;
  enabled: boolean;
  configured: boolean;
  model_resolved?: string;
  default_model_hint?: string;
  actions: ImageAiAction[];
}

export interface ImageAiProcessPayload {
  action: ImageAiActionId;
  source_url: string;
  target_type: ImageAiTargetType;
  target_uuid?: string;
  replace_original?: boolean;
}

export interface ImageAiProcessResult {
  preview_url?: string;
  image_url?: string;
  action: ImageAiActionId;
  saved: boolean;
}

interface ApiWrap<T> {
  status: number;
  message: string;
  data: T;
}

@Injectable({ providedIn: 'root' })
export class ImageAiService {
  private readonly baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private headers(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders({
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      Authorization: `Bearer ${token}`,
    });
  }

  getConfig(): Observable<ApiWrap<ImageAiConfig>> {
    return this.http.post<ApiWrap<ImageAiConfig>>(`${this.baseUrl}/api/image_ai/config`, {}, { headers: this.headers() });
  }

  process(payload: ImageAiProcessPayload): Observable<ApiWrap<ImageAiProcessResult>> {
    return this.http.post<ApiWrap<ImageAiProcessResult>>(
      `${this.baseUrl}/api/image_ai/process`,
      payload,
      { headers: this.headers() },
    );
  }
}
