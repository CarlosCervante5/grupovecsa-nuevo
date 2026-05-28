import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';

export interface AssistantChatListRow {
  uuid: string;
  preview: string | null;
  visitor_name: string;
  visitor_email: string | null;
  page_url: string | null;
  dealership_id: number | null;
  dealership_name: string | null;
  assigned_user_id: number | null;
  assigned_user_name: string | null;
  messages_count: number;
  last_message_at: string | null;
  created_at: string;
  is_registered: boolean;
  is_human_handoff?: boolean;
  human_handoff_at?: string | null;
}

export interface AssistantChatMessage {
  id: number;
  role: 'user' | 'assistant' | 'agent';
  content: string;
  created_at: string;
}

export interface AssistantChatDetail extends AssistantChatListRow {
  session_key: string;
  ip_address: string | null;
  messages: AssistantChatMessage[];
}

@Injectable({ providedIn: 'root' })
export class AssistantChatsAdminService {
  private baseUrl = environment.baseUrl;

  constructor(private http: HttpClient) {}

  private headers(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders({
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    });
  }

  search(params: {
    page?: number;
    per_page?: number;
    search?: string;
    date_from?: string;
    date_to?: string;
  } = {}): Observable<any> {
    return this.http.post(
      `${this.baseUrl}/api/assistant/admin/conversations/search`,
      params,
      { headers: this.headers() }
    );
  }

  detail(uuid: string): Observable<any> {
    return this.http.post(
      `${this.baseUrl}/api/assistant/admin/conversations/detail`,
      { uuid },
      { headers: this.headers() }
    );
  }

  takeOver(uuid: string): Observable<any> {
    return this.http.post(
      `${this.baseUrl}/api/assistant/admin/conversations/take-over`,
      { uuid },
      { headers: this.headers() }
    );
  }

  reply(uuid: string, message: string): Observable<any> {
    return this.http.post(
      `${this.baseUrl}/api/assistant/admin/conversations/reply`,
      { uuid, message },
      { headers: this.headers() }
    );
  }

  release(uuid: string): Observable<any> {
    return this.http.post(
      `${this.baseUrl}/api/assistant/admin/conversations/release`,
      { uuid },
      { headers: this.headers() }
    );
  }
}
