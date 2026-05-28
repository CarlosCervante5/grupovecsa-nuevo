import { Component, OnDestroy } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
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

type ChatMessageRole = 'user' | 'assistant' | 'agent';

interface ChatMessage {
  id?: number;
  role: ChatMessageRole;
  text: string;
}

interface PollMessageRow {
  id: number;
  role: string;
  content: string;
  created_at?: string;
}

interface PollMessagesResponse {
  messages?: PollMessageRow[];
  human_handoff?: boolean;
  conversation_uuid?: string;
}

interface AssistantChatResponse {
  reply?: string;
  conversation_uuid?: string;
  needs_dealership?: boolean;
  dealerships?: ChatDealership[];
  human_handoff?: boolean;
}

@Component({
  selector: 'app-chat-assistant',
  templateUrl: './chat-assistant.component.html',
  styleUrls: ['./chat-assistant.component.css'],
  standalone: false,
})
export class ChatAssistantComponent implements OnDestroy {
  open = false;
  message = '';
  sending = false;
  loadingDealerships = false;
  /** Muestra botones de sucursal (inicio o cuando el API lo pide). */
  showDealershipPicker = true;
  dealershipPickerHint =
    'Te enlazaremos con el equipo de la sucursal que elijas.';
  dealerships: ChatDealership[] = [];
  selectedDealershipId: number | null = null;
  selectedDealershipName: string | null = null;
  messages: ChatMessage[] = [];
  humanHandoff = false;

  private url = environment.baseUrl;
  private sessionKey = this.ensureSessionKey();
  private conversationUuid: string | null = this.loadConversationUuid();
  private lastMessageId = 0;
  private pollTimer: ReturnType<typeof setInterval> | null = null;

  constructor(private http: HttpClient) {
    const stored = this.loadDealershipId();
    if (stored) {
      this.selectedDealershipId = stored;
      this.showDealershipPicker = false;
    }
  }

  ngOnDestroy(): void {
    this.stopPolling();
  }

  toggle(): void {
    this.open = !this.open;
    if (this.open) {
      this.ensureDealershipsLoaded();
      if (this.conversationUuid) {
        this.startPolling();
      }
    } else {
      this.stopPolling();
    }
  }

  messageLabel(role: ChatMessageRole): string {
    if (role === 'user') {
      return '';
    }
    return role === 'agent' ? 'Asesor' : '';
  }

  selectDealership(d: ChatDealership): void {
    this.selectedDealershipId = d.id;
    this.selectedDealershipName = d.name;
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(DEALERSHIP_KEY, String(d.id));
    }
    this.showDealershipPicker = false;
    if (this.messages.length === 0) {
      this.messages = [
        {
          role: 'assistant',
          text: `Has elegido ${d.name}. ¿En qué puedo ayudarte hoy?`,
        },
      ];
    }
    setTimeout(() => this.scrollToBottom(), 50);
  }

  changeDealership(): void {
    this.showDealershipPicker = true;
    this.dealershipPickerHint =
      'Elige otra sucursal para continuar la conversación.';
    this.ensureDealershipsLoaded(true);
    setTimeout(() => this.scrollToBottom(), 50);
  }

  send(): void {
    const text = this.message.trim();
    if (!text || this.sending) {
      return;
    }

    if (!this.selectedDealershipId) {
      this.showDealershipPicker = true;
      this.dealershipPickerHint =
        'Selecciona una sucursal para que podamos atenderte.';
      this.ensureDealershipsLoaded();
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
      .post<AssistantChatResponse>(`${this.url}/api/assistant/chat`, body, {
        headers,
      })
      .subscribe({
        next: (res) => this.handleChatResponse(res),
        error: (err) => this.handleChatError(err),
      });
  }

  private handleChatResponse(res: AssistantChatResponse): void {
    if (res.needs_dealership) {
      this.applyNeedsDealership(res);
      this.sending = false;
      return;
    }

    if (res.conversation_uuid) {
      this.conversationUuid = res.conversation_uuid;
      this.saveConversationUuid(res.conversation_uuid);
    }
    if (res.human_handoff) {
      this.humanHandoff = true;
      this.startPolling();
    }
    if (res.reply) {
      this.messages.push({ role: 'assistant', text: res.reply });
    }
    this.sending = false;
    setTimeout(() => this.scrollToBottom(), 50);
  }

  private handleChatError(err: HttpErrorResponse): void {
    const body = err.error as AssistantChatResponse | undefined;
    if (err.status === 422 && body?.needs_dealership) {
      this.applyNeedsDealership(body);
      this.sending = false;
      return;
    }

    this.messages.push({
      role: 'assistant',
      text: 'Lo siento, hubo un error. Intenta de nuevo.',
    });
    this.sending = false;
  }

  private applyNeedsDealership(res: AssistantChatResponse): void {
    this.clearStoredDealership();
    this.showDealershipPicker = true;
    this.dealershipPickerHint =
      'Selecciona la sucursal con la que deseas contactar.';
    if (res.dealerships?.length) {
      this.dealerships = res.dealerships.filter(
        (d): d is ChatDealership => !!d?.id && !!d?.name
      );
    } else {
      this.ensureDealershipsLoaded();
    }
    if (res.reply) {
      this.messages.push({ role: 'assistant', text: res.reply });
    }
    setTimeout(() => this.scrollToBottom(), 50);
  }

  private ensureDealershipsLoaded(force = false): void {
    if (this.loadingDealerships || (!force && this.dealerships.length > 0)) {
      return;
    }
    this.loadingDealerships = true;
    this.http
      .get<{ dealerships: ChatDealership[] }>(
        `${this.url}/api/assistant/dealerships`
      )
      .subscribe({
        next: (res) => {
          this.dealerships = (res.dealerships ?? []).filter(
            (d): d is ChatDealership => !!d?.id && !!d?.name
          );
          this.loadingDealerships = false;
        },
        error: () => {
          this.loadingDealerships = false;
        },
      });
  }

  private clearStoredDealership(): void {
    this.selectedDealershipId = null;
    this.selectedDealershipName = null;
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem(DEALERSHIP_KEY);
    }
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

  private startPolling(): void {
    this.stopPolling();
    if (!this.conversationUuid) {
      return;
    }
    this.seedPollCursor(() => {
      this.pollTimer = setInterval(() => this.pollMessages(), 5000);
      this.pollMessages();
    });
  }

  private seedPollCursor(done: () => void): void {
    if (this.lastMessageId > 0) {
      done();
      return;
    }
    const body = {
      conversation_uuid: this.conversationUuid!,
      session_key: this.sessionKey,
      after_id: 0,
    };
    this.http
      .post<PollMessagesResponse>(`${this.url}/api/assistant/messages`, body, {
        headers: this.buildHeaders(),
      })
      .subscribe({
        next: (res) => {
          for (const m of res.messages ?? []) {
            this.lastMessageId = Math.max(this.lastMessageId, m.id);
          }
          done();
        },
        error: () => done(),
      });
  }

  private stopPolling(): void {
    if (this.pollTimer) {
      clearInterval(this.pollTimer);
      this.pollTimer = null;
    }
  }

  private pollMessages(): void {
    if (!this.conversationUuid || !this.open) {
      return;
    }
    const body: Record<string, string | number> = {
      conversation_uuid: this.conversationUuid,
      session_key: this.sessionKey,
      after_id: this.lastMessageId,
    };
    this.http
      .post<PollMessagesResponse>(`${this.url}/api/assistant/messages`, body, {
        headers: this.buildHeaders(),
      })
      .subscribe({
        next: (res) => {
          if (res.human_handoff) {
            this.humanHandoff = true;
          }
          const incoming = res.messages ?? [];
          let added = false;
          for (const m of incoming) {
            if (m.id <= this.lastMessageId) {
              continue;
            }
            if (this.messages.some((x) => x.id === m.id)) {
              this.lastMessageId = Math.max(this.lastMessageId, m.id);
              continue;
            }
            this.lastMessageId = Math.max(this.lastMessageId, m.id);
            if (m.role === 'user') {
              continue;
            }
            this.messages.push({
              id: m.id,
              role: m.role === 'agent' ? 'agent' : 'assistant',
              text: m.content,
            });
            added = true;
          }
          if (added) {
            setTimeout(() => this.scrollToBottom(), 50);
          }
        },
      });
  }

  private scrollToBottom(): void {
    const el = document.querySelector('.chat-messages');
    if (el) {
      el.scrollTop = el.scrollHeight;
    }
  }
}
