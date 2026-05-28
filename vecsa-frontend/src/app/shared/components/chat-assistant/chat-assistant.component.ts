import { Component, OnDestroy, OnInit } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpHeaders } from '@angular/common/http';
import { environment } from '@environments/environment';
import { ChatNotificationSoundService } from '@services/chat-notification-sound.service';

const SESSION_KEY = 'vecsa_assistant_session';
const CONVERSATION_KEY = 'vecsa_assistant_conversation';
const DEALERSHIP_KEY = 'vecsa_assistant_dealership_id';
const DEALERSHIP_NAME_KEY = 'vecsa_assistant_dealership_name';
const HUMAN_HANDOFF_KEY = 'vecsa_assistant_human_handoff';
const CHAT_INTENT_KEY = 'vecsa_assistant_chat_topic';

export type ChatIntent = 'autos' | 'motos' | 'boutique' | 'general';

interface ChatIntentOption {
  id: ChatIntent;
  label: string;
  hint: string;
  icon: string;
}

interface ChatDealership {
  id: number;
  name: string;
  location?: string | null;
  state?: string | null;
}

type ChatMessageRole = 'user' | 'assistant' | 'agent';

export interface ChatCatalogCard {
  type: 'vehicle' | 'boutique_product';
  uuid: string;
  title: string;
  subtitle?: string | null;
  price_label?: string | null;
  image_url?: string | null;
  url: string;
}

interface ChatMessage {
  id?: number;
  role: ChatMessageRole;
  text: string;
  catalogCards?: ChatCatalogCard[];
}

interface PollMessageRow {
  id: number;
  role: string;
  content: string;
  catalog_cards?: ChatCatalogCard[];
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
  reply_message_id?: number;
  catalog_cards?: ChatCatalogCard[];
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
  readonly intentOptions: ChatIntentOption[] = [
    {
      id: 'autos',
      label: 'Autos BMW / MINI',
      hint: 'Inventario, precios y asesor de ventas',
      icon: 'directions_car',
    },
    {
      id: 'motos',
      label: 'Motos BMW Motorrad',
      hint: 'Inventario y asesor de motos',
      icon: 'two_wheeler',
    },
    {
      id: 'boutique',
      label: 'Productos / Boutique',
      hint: 'Accesorios, ropa y refacciones',
      icon: 'shopping_bag',
    },
    {
      id: 'general',
      label: 'Otra consulta',
      hint: 'Servicios, citas o información general',
      icon: 'help_outline',
    },
  ];

  open = false;
  unreadCount = 0;
  message = '';
  sending = false;
  loadingDealerships = false;

  showIntentPicker = true;
  showDealershipPicker = false;
  dealershipPickerHint =
    'Elige la sucursal con asesores en línea para tu consulta.';

  selectedIntent: ChatIntent | null = null;
  dealerships: ChatDealership[] = [];
  selectedDealershipId: number | null = null;
  selectedDealershipName: string | null = null;
  messages: ChatMessage[] = [];
  humanHandoff = false;
  readonly catalogPlaceholder = 'assets/images/placeholder-product.svg';

  private url = environment.baseUrl;
  private sessionKey = this.ensureSessionKey();
  private conversationUuid: string | null = this.loadConversationUuid();
  private lastMessageId = 0;
  private pollTimer: ReturnType<typeof setInterval> | null = null;
  private backgroundUnreadTimer: ReturnType<typeof setInterval> | null = null;
  private unreadBaselineReady = false;
  private syncingConversation = false;

  constructor(
    private http: HttpClient,
    private sound: ChatNotificationSoundService,
  ) {
    this.selectedIntent = this.loadIntent();
    this.humanHandoff = this.loadHumanHandoff();
    this.restoreDealershipFromStorage();
    this.applyOnboardingStep();
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
      if (this.showDealershipPicker) {
        this.ensureDealershipsLoaded();
      }
      if (this.conversationUuid) {
        this.refreshConversationState();
        this.startPolling();
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

  intentLabel(): string {
    const opt = this.intentOptions.find((o) => o.id === this.selectedIntent);
    return opt?.label ?? 'Consulta';
  }

  postDealershipWelcome(): string {
    const branch = this.selectedDealershipName ?? 'tu sucursal';
    switch (this.selectedIntent) {
      case 'boutique':
        return `Perfecto, ${branch}. ¿Qué producto y marca buscas? Por ejemplo: maleta BMW o chaleco MINI.`;
      case 'motos':
        return `Listo, ${branch}. ¿Qué moto BMW Motorrad te interesa? Puedes indicar modelo o presupuesto.`;
      case 'autos':
        return `Listo, ${branch}. ¿Qué auto BMW o MINI buscas? Indica modelo, año o presupuesto.`;
      default:
        return `Listo, ${branch}. ¿En qué puedo ayudarte?`;
    }
  }

  selectIntent(intent: ChatIntent): void {
    if (this.selectedIntent !== intent) {
      this.resetConversationSession();
    }
    this.selectedIntent = intent;
    this.saveIntent(intent);
    this.showIntentPicker = false;
    this.showDealershipPicker = true;
    this.dealershipPickerHint =
      'Solo se muestran sucursales con asesores en línea.';
    this.ensureDealershipsLoaded(true);
    setTimeout(() => this.scrollToBottom(), 50);
  }

  selectDealership(d: ChatDealership): void {
    const changed =
      this.selectedDealershipId != null && this.selectedDealershipId !== d.id;
    if (changed) {
      this.resetConversationSession();
    }

    this.selectedDealershipId = d.id;
    this.selectedDealershipName = d.name;
    this.persistDealership(d.id, d.name);
    this.showDealershipPicker = false;
    setTimeout(() => this.scrollToBottom(), 50);
  }

  changeIntent(): void {
    this.resetConversationSession();
    this.selectedIntent = null;
    this.selectedDealershipId = null;
    this.selectedDealershipName = null;
    this.clearStoredIntent();
    this.clearStoredDealership();
    this.showIntentPicker = true;
    this.showDealershipPicker = false;
    setTimeout(() => this.scrollToBottom(), 50);
  }

  changeDealership(): void {
    this.resetConversationSession();
    this.selectedDealershipId = null;
    this.selectedDealershipName = null;
    this.clearStoredDealership();
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

    if (!this.selectedIntent) {
      this.showIntentPicker = true;
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
      page_url: this.pageUrlForIntent(this.selectedIntent),
      chat_topic: this.selectedIntent,
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
      this.startPolling();
    }

    if (res.human_handoff) {
      this.setHumanHandoff(true);
      this.stopBackgroundUnreadCheck();
      this.startBackgroundUnreadCheck();
    }

    if (res.reply?.trim() || (res.catalog_cards?.length ?? 0) > 0) {
      this.appendAssistantMessage(
        res.reply?.trim() ?? '',
        res.reply_message_id,
        res.catalog_cards
      );
    } else if (this.humanHandoff && this.conversationUuid) {
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

    this.appendAssistantMessage('Lo siento, hubo un error. Intenta de nuevo.');
    this.sending = false;
  }

  private applyNeedsDealership(res: AssistantChatResponse): void {
    this.resetConversationSession();
    this.showDealershipPicker = true;
    this.dealershipPickerHint =
      'Selecciona la sucursal con la que deseas contactar.';
    if (res.dealerships?.length) {
      this.dealerships = this.filterDealershipsWithAdvisors(res.dealerships);
    } else {
      this.ensureDealershipsLoaded();
    }
    if (res.reply) {
      this.appendAssistantMessage(res.reply);
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
          this.dealerships = this.filterDealershipsWithAdvisors(
            res.dealerships ?? []
          );
          this.loadingDealerships = false;
        },
        error: () => {
          this.loadingDealerships = false;
        },
      });
  }

  private filterDealershipsWithAdvisors(
    list: ChatDealership[] | undefined
  ): ChatDealership[] {
    return (list ?? []).filter(
      (d): d is ChatDealership => !!d?.id && !!d?.name
    );
  }

  private applyOnboardingStep(): void {
    if (!this.selectedIntent) {
      this.showIntentPicker = true;
      this.showDealershipPicker = false;
      return;
    }
    if (!this.selectedDealershipId) {
      this.showIntentPicker = false;
      this.showDealershipPicker = true;
      return;
    }
    this.showIntentPicker = false;
    this.showDealershipPicker = false;
  }

  private restoreDealershipFromStorage(): void {
    const id = this.loadDealershipId();
    if (id == null) {
      return;
    }
    this.selectedDealershipId = id;
    if (typeof localStorage !== 'undefined') {
      this.selectedDealershipName =
        localStorage.getItem(DEALERSHIP_NAME_KEY) ?? null;
    }
  }

  private resetConversationSession(): void {
    this.stopPolling();
    this.conversationUuid = null;
    this.lastMessageId = 0;
    this.messages = [];
    this.setHumanHandoff(false);
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem(CONVERSATION_KEY);
    }
  }

  private clearStoredDealership(): void {
    this.selectedDealershipId = null;
    this.selectedDealershipName = null;
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem(DEALERSHIP_KEY);
      localStorage.removeItem(DEALERSHIP_NAME_KEY);
    }
  }

  private persistDealership(id: number, name: string): void {
    if (typeof localStorage === 'undefined') {
      return;
    }
    localStorage.setItem(DEALERSHIP_KEY, String(id));
    localStorage.setItem(DEALERSHIP_NAME_KEY, name);
  }

  private pageUrlForIntent(intent: ChatIntent): string {
    switch (intent) {
      case 'boutique':
        return '/boutique';
      case 'motos':
        return '/motorrad';
      case 'autos':
        return '/compra-tu-auto';
      default:
        return '/';
    }
  }

  cardImageUrl(card: ChatCatalogCard): string {
    const url = card.image_url?.trim();
    return url || this.catalogPlaceholder;
  }

  onCatalogImageError(event: Event): void {
    const img = event.target as HTMLImageElement | null;
    if (img && img.src !== this.catalogPlaceholder) {
      img.src = this.catalogPlaceholder;
    }
  }

  private appendAssistantMessage(
    text: string,
    messageId?: number,
    catalogCards?: ChatCatalogCard[]
  ): void {
    const trimmed = text.trim();
    const cards = catalogCards?.length ? catalogCards : undefined;
    if (!trimmed && !cards?.length) {
      return;
    }
    if (messageId != null) {
      const existing = this.messages.find((m) => m.id === messageId);
      if (existing) {
        return;
      }
    }
    const last = this.messages[this.messages.length - 1];
    if (
      last &&
      last.role !== 'user' &&
      last.text === trimmed &&
      (messageId == null || last.id === messageId)
    ) {
      if (messageId != null && last.id == null) {
        last.id = messageId;
      }
      if (cards?.length && !last.catalogCards?.length) {
        last.catalogCards = cards;
      }
      return;
    }
    this.messages.push({
      id: messageId,
      role: 'assistant',
      text: trimmed,
      catalogCards: cards,
    });
    if (messageId != null) {
      this.lastMessageId = Math.max(this.lastMessageId, messageId);
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

  private loadIntent(): ChatIntent | null {
    if (typeof localStorage === 'undefined') {
      return null;
    }
    const raw = localStorage.getItem(CHAT_INTENT_KEY);
    if (
      raw === 'autos' ||
      raw === 'motos' ||
      raw === 'boutique' ||
      raw === 'general'
    ) {
      return raw;
    }
    return null;
  }

  private saveIntent(intent: ChatIntent): void {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(CHAT_INTENT_KEY, intent);
    }
  }

  private clearStoredIntent(): void {
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem(CHAT_INTENT_KEY);
    }
  }

  private saveConversationUuid(uuid: string): void {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(CONVERSATION_KEY, uuid);
    }
  }

  private refreshConversationState(): void {
    if (!this.conversationUuid || this.syncingConversation) {
      return;
    }
    this.syncingConversation = true;
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
          this.applyServerMessages(res.messages ?? [], true);
          this.setHumanHandoff(!!res.human_handoff);
          this.syncingConversation = false;
        },
        error: () => {
          this.syncingConversation = false;
        },
      });
  }

  private startPolling(): void {
    this.stopPolling();
    if (!this.conversationUuid) {
      return;
    }
    this.pollTimer = setInterval(() => this.pollMessages(), 5000);
    this.pollMessages();
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
          this.setHumanHandoff(!!res.human_handoff);
          const added = this.applyServerMessages(res.messages ?? [], false);
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

  private applyServerMessages(
    rows: PollMessageRow[],
    replace: boolean
  ): boolean {
    if (replace) {
      this.messages = rows.map((m) => this.rowToChatMessage(m));
      this.lastMessageId = rows.reduce((max, m) => Math.max(max, m.id), 0);
      return rows.length > 0;
    }

    let added = false;
    for (const m of rows) {
      if (m.id <= this.lastMessageId) {
        continue;
      }
      if (m.role === 'user') {
        this.lastMessageId = Math.max(this.lastMessageId, m.id);
        continue;
      }
      if (this.messages.some((x) => x.id === m.id)) {
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
        last.role = m.role === 'agent' ? 'agent' : 'assistant';
        if (m.catalog_cards?.length && !last.catalogCards?.length) {
          last.catalogCards = m.catalog_cards;
        }
        this.lastMessageId = Math.max(this.lastMessageId, m.id);
        continue;
      }
      this.messages.push(this.rowToChatMessage(m));
      this.lastMessageId = Math.max(this.lastMessageId, m.id);
      added = true;
    }
    return added;
  }

  private rowToChatMessage(m: PollMessageRow): ChatMessage {
    return {
      id: m.id,
      role: m.role === 'agent' ? 'agent' : m.role === 'user' ? 'user' : 'assistant',
      text: m.content,
      catalogCards: m.catalog_cards?.length ? m.catalog_cards : undefined,
    };
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
