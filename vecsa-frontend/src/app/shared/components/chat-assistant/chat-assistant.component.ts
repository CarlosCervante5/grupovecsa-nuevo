import { Component, OnDestroy, OnInit } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { ChatNotificationSoundService } from '@services/chat-notification-sound.service';

const SESSION_KEY = 'vecsa_assistant_session';
const CONVERSATION_KEY = 'vecsa_assistant_conversation';
const DEALERSHIP_KEY = 'vecsa_assistant_dealership_id';
const HUMAN_HANDOFF_KEY = 'vecsa_assistant_human_handoff';

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
  unread_count?: number;
}

interface VisitorUnreadSummary {
  unread_count?: number;
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
export class ChatAssistantComponent implements OnInit, OnDestroy {
  open = false;
  unreadCount = 0;
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
  private backgroundUnreadTimer: ReturnType<typeof setInterval> | null = null;
  private unreadBaselineReady = false;

  constructor(
    private http: HttpClient,
    private sound: ChatNotificationSoundService,
  ) {
    const stored = this.loadDealershipId();
    if (stored) {
      this.selectedDealershipId = stored;
      this.showDealershipPicker = false;
    }
    this.humanHandoff = this.loadHumanHandoff();
  }

  ngOnInit(): void {
    this.startBackgroundUnreadCheck();
  }

  ngOnDestroy(): void {
    this.stopPolling();
    this.stopBackgroundUnreadCheck();
  }

  toggle(): void {
    this.sound.unlock();
    this.open = !this.open;
    if (this.open) {
      this.unreadCount = 0;
      this.stopBackgroundUnreadCheck();
      this.ensureDealershipsLoaded();
      if (this.conversationUuid) {
        if (this.humanHandoff) {
          this.startPolling();
        }
        this.markVisitorRead();
      }
    } else {
      this.stopPolling();
      this.startBackgroundUnreadCheck();
    }
  }

  messageLabel(role: ChatMessageRole): string {
    if (role === 'user') {
      return '';
    }
    return role === 'agent' ? 'Asesor' : '';
  }

  isBoutiqueSection(): boolean {
    if (typeof window === 'undefined') {
      return false;
    }
    return /\/boutique(?:\/|$)/i.test(window.location.pathname);
  }

  boutiqueWelcomeText(): string {
    return '¡Hola! Estás en la Boutique VECSA. ¿Qué producto y marca estás buscando?';
  }

  selectDealership(d: ChatDealership): void {
    this.selectedDealershipId = d.id;
    this.selectedDealershipName = d.name;
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(DEALERSHIP_KEY, String(d.id));
    }
    this.showDealershipPicker = false;
    if (this.messages.length === 0) {
      const boutiqueHint = this.isBoutiqueSection()
        ? `Has elegido ${d.name}. ¿Qué producto y marca estás buscando en nuestra Boutique? Por ejemplo: maleta BMW o chaleco MINI.`
        : `Has elegido ${d.name}. ¿En qué puedo ayudarte hoy?`;
      this.messages = [
        {
          role: 'assistant',
          text: boutiqueHint,
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
      if (!this.open) {
        this.startBackgroundUnreadCheck();
      }
    }
    if (res.human_handoff) {
      this.setHumanHandoff(true);
      this.startPolling();
      this.stopBackgroundUnreadCheck();
      this.startBackgroundUnreadCheck();
    }
    if (res.reply?.trim()) {
      this.messages.push({ role: 'assistant', text: res.reply });
      if (this.conversationUuid) {
        this.syncPollCursor();
      }
    } else if (this.humanHandoff && this.conversationUuid) {
      this.syncPollCursor();
      setTimeout(() => this.pollMessages(), 150);
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

  private loadHumanHandoff(): boolean {
    if (typeof localStorage === 'undefined') {
      return false;
    }
    return localStorage.getItem(HUMAN_HANDOFF_KEY) === '1';
  }

  private setHumanHandoff(active: boolean): void {
    this.humanHandoff = active;
    if (typeof localStorage !== 'undefined') {
      if (active) {
        localStorage.setItem(HUMAN_HANDOFF_KEY, '1');
      } else {
        localStorage.removeItem(HUMAN_HANDOFF_KEY);
      }
    }
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

  /** Actualiza lastMessageId con lo ya persistido (evita duplicar burbujas del bot). */
  private syncPollCursor(): void {
    if (!this.conversationUuid) {
      return;
    }
    const body = {
      conversation_uuid: this.conversationUuid,
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
          if (res.human_handoff) {
            this.setHumanHandoff(true);
          }
        },
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
          if (res.human_handoff) {
            this.setHumanHandoff(true);
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
            this.setHumanHandoff(true);
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
            if (m.role === 'user') {
              this.lastMessageId = Math.max(this.lastMessageId, m.id);
              continue;
            }
            const last = this.messages[this.messages.length - 1];
            if (
              last &&
              last.id == null &&
              last.role !== 'user' &&
              last.text === m.content
            ) {
              last.id = m.id;
              this.lastMessageId = Math.max(this.lastMessageId, m.id);
              continue;
            }
            this.lastMessageId = Math.max(this.lastMessageId, m.id);
            this.messages.push({
              id: m.id,
              role: m.role === 'agent' ? 'agent' : 'assistant',
              text: m.content,
            });
            added = true;
          }
          if (added) {
            this.sound.playNewMessage();
            setTimeout(() => this.scrollToBottom(), 50);
          }
          if (typeof res.unread_count === 'number') {
            this.unreadCount = res.unread_count;
          }
          if (this.open) {
            this.markVisitorRead(this.lastMessageId);
          }
        },
      });
  }

  private startBackgroundUnreadCheck(): void {
    this.stopBackgroundUnreadCheck();
    if (!this.conversationUuid) {
      return;
    }
    this.refreshUnreadCount();
    this.backgroundUnreadTimer = setInterval(
      () => this.refreshUnreadCount(),
      12000
    );
  }

  private stopBackgroundUnreadCheck(): void {
    if (this.backgroundUnreadTimer) {
      clearInterval(this.backgroundUnreadTimer);
      this.backgroundUnreadTimer = null;
    }
  }

  private refreshUnreadCount(): void {
    if (this.open || !this.conversationUuid) {
      return;
    }
    this.http
      .post<VisitorUnreadSummary>(`${this.url}/api/assistant/unread-summary`, {
        conversation_uuid: this.conversationUuid,
        session_key: this.sessionKey,
      })
      .subscribe({
        next: (res) => {
          const n = Number(res?.unread_count ?? 0);
          const safe = Number.isFinite(n) ? n : 0;
          if (this.unreadBaselineReady && safe > this.unreadCount) {
            this.sound.playNewMessage();
          }
          this.unreadCount = safe;
          this.unreadBaselineReady = true;
        },
      });
  }

  private markVisitorRead(messageId?: number): void {
    if (!this.conversationUuid) {
      return;
    }
    const body: Record<string, string | number | boolean> = {
      conversation_uuid: this.conversationUuid,
      session_key: this.sessionKey,
      mark_read: true,
    };
    if (messageId && messageId > 0) {
      body['last_read_message_id'] = messageId;
    }
    this.http
      .post<PollMessagesResponse>(`${this.url}/api/assistant/messages`, body, {
        headers: this.buildHeaders(),
      })
      .subscribe({
        next: (res) => {
          this.unreadCount = 0;
          if (typeof res.unread_count === 'number') {
            this.unreadCount = res.unread_count;
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
