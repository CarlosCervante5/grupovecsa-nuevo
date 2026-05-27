import { Component } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';

const SESSION_KEY = 'vecsa_assistant_session';
const CONVERSATION_KEY = 'vecsa_assistant_conversation';
const DEALERSHIP_KEY = 'vecsa_assistant_dealership_id';

interface ChatDealership {
  id: number;
  name: string;
  location?: string | null;
  state?: string | null;
}

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
  loadingDealerships = false;
  phase: 'dealership' | 'chat' = 'dealership';
  dealerships: ChatDealership[] = [];
  selectedDealershipId: number | null = null;
  messages: { role: 'user' | 'assistant'; text: string }[] = [];

  private url = environment.baseUrl;
  private sessionKey = this.ensureSessionKey();
  private conversationUuid: string | null = this.loadConversationUuid();

  constructor(private http: HttpClient) {
    const stored = this.loadDealershipId();
    if (stored) {
      this.selectedDealershipId = stored;
      this.phase = 'chat';
    }
  }

  toggle(): void {
    this.open = !this.open;
    if (this.open && this.dealerships.length === 0) {
      this.loadDealerships();
    }
  }

  selectDealership(d: ChatDealership): void {
    this.selectedDealershipId = d.id;
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(DEALERSHIP_KEY, String(d.id));
    }
    this.phase = 'chat';
    this.messages = [
      {
        role: 'assistant',
        text: `Has elegido ${d.name}. ¿En qué puedo ayudarte hoy?`,
      },
    ];
    setTimeout(() => this.scrollToBottom(), 50);
  }

  send(): void {
    const text = this.message.trim();
    if (!text || this.sending) {
      return;
    }
    if (!this.selectedDealershipId) {
      this.phase = 'dealership';
      return;
    }

    this.messages.push({ role: 'user', text });
    this.message = '';
    this.sending = true;

    const headers = this.buildHeaders();
    const body: Record<string, string | number> = {
      message: text,
      session_key: this.sessionKey,
      page_url: typeof window !== 'undefined' ? window.location.pathname : '',
      dealership_id: this.selectedDealershipId,
    };
    if (this.conversationUuid) {
      body['conversation_uuid'] = this.conversationUuid;
    }

    this.http
      .post<{
        reply: string;
        conversation_uuid?: string;
        needs_dealership?: boolean;
        dealerships?: ChatDealership[];
      }>(`${this.url}/api/assistant/chat`, body, { headers })
      .subscribe({
        next: (res) => {
          if (res.needs_dealership) {
            this.phase = 'dealership';
            if (res.dealerships?.length) {
              this.dealerships = res.dealerships;
            }
            this.messages.push({
              role: 'assistant',
              text: res.reply || 'Elige la sucursal con la que deseas contactar:',
            });
            this.sending = false;
            return;
          }
          if (res.conversation_uuid) {
            this.conversationUuid = res.conversation_uuid;
            this.saveConversationUuid(res.conversation_uuid);
          }
          this.messages.push({ role: 'assistant', text: res.reply });
          this.sending = false;
          setTimeout(() => this.scrollToBottom(), 50);
        },
        error: () => {
          this.messages.push({
            role: 'assistant',
            text: 'Lo siento, hubo un error. Intenta de nuevo.',
          });
          this.sending = false;
        },
      });
  }

  private loadDealerships(): void {
    this.loadingDealerships = true;
    this.http
      .get<{ dealerships: ChatDealership[] }>(`${this.url}/api/assistant/dealerships`)
      .subscribe({
        next: (res) => {
          this.dealerships = res.dealerships ?? [];
          this.loadingDealerships = false;
        },
        error: () => {
          this.loadingDealerships = false;
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
      key =
        typeof crypto !== 'undefined' && crypto.randomUUID
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

  private loadDealershipId(): number | null {
    if (typeof localStorage === 'undefined') {
      return null;
    }
    const raw = localStorage.getItem(DEALERSHIP_KEY);
    if (!raw) {
      return null;
    }
    const id = parseInt(raw, 10);
    return Number.isNaN(id) ? null : id;
  }

  private saveConversationUuid(uuid: string): void {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(CONVERSATION_KEY, uuid);
    }
  }

  private scrollToBottom(): void {
    const el = document.querySelector('.chat-messages');
    if (el) {
      el.scrollTop = el.scrollHeight;
    }
  }
}
