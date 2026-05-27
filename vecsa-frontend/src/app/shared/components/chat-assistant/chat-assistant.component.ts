import { Component } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';

const SESSION_KEY = 'vecsa_assistant_session';
const CONVERSATION_KEY = 'vecsa_assistant_conversation';

@Component({
  selector: 'app-chat-assistant',
  templateUrl: './chat-assistant.component.html',
  styleUrls: ['./chat-assistant.component.css'],
  standalone: false,
})
export class ChatAssistantComponent {
  open = false;
  message = '';
  sending = false;
  messages: { role: 'user' | 'assistant'; text: string }[] = [];

  private url = environment.baseUrl;
  private sessionKey = this.ensureSessionKey();
  private conversationUuid: string | null = this.loadConversationUuid();

  constructor(private http: HttpClient) {}

  toggle(): void { this.open = !this.open; }

  send(): void {
    const text = this.message.trim();
    if (!text || this.sending) return;
    this.messages.push({ role: 'user', text });
    this.message = '';
    this.sending = true;

    const headers = this.buildHeaders();
    const body: Record<string, string> = {
      message: text,
      session_key: this.sessionKey,
      page_url: typeof window !== 'undefined' ? window.location.pathname : '',
    };
    if (this.conversationUuid) {
      body['conversation_uuid'] = this.conversationUuid;
    }

    this.http.post<{ reply: string; conversation_uuid?: string }>(
      `${this.url}/api/assistant/chat`,
      body,
      { headers }
    ).subscribe({
      next: (res) => {
        if (res.conversation_uuid) {
          this.conversationUuid = res.conversation_uuid;
          this.saveConversationUuid(res.conversation_uuid);
        }
        this.messages.push({ role: 'assistant', text: res.reply });
        this.sending = false;
        setTimeout(() => this.scrollToBottom(), 50);
      },
      error: () => {
        this.messages.push({ role: 'assistant', text: 'Lo siento, hubo un error. Intenta de nuevo.' });
        this.sending = false;
      },
    });
  }

  private buildHeaders(): HttpHeaders {
    let headers = new HttpHeaders({ 'Content-Type': 'application/json' });
    const token = localStorage.getItem('user_token');
    if (token) {
      headers = headers.set('Authorization', `Bearer ${token}`);
    }
    return headers;
  }

  private ensureSessionKey(): string {
    if (typeof localStorage === 'undefined') {
      return 'guest';
    }
    let key = localStorage.getItem(SESSION_KEY);
    if (!key) {
      key = typeof crypto !== 'undefined' && crypto.randomUUID
        ? crypto.randomUUID()
        : `sess-${Date.now()}-${Math.random().toString(36).slice(2)}`;
      localStorage.setItem(SESSION_KEY, key);
    }
    return key;
  }

  private loadConversationUuid(): string | null {
    if (typeof localStorage === 'undefined') {
      return null;
    }
    return localStorage.getItem(CONVERSATION_KEY);
  }

  private saveConversationUuid(uuid: string): void {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(CONVERSATION_KEY, uuid);
    }
  }

  private scrollToBottom(): void {
    const el = document.querySelector('.chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
  }
}
