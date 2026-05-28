import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { AssistantChatsAdminService } from '@services/assistant-chats-admin.service';
import { ChatNotificationSoundService } from '@services/chat-notification-sound.service';

@Injectable({ providedIn: 'root' })
export class AssistantChatNotificationsService {
  readonly unreadTotal$ = new BehaviorSubject(0);

  private pollTimer: ReturnType<typeof setInterval> | null = null;
  private lastUnreadTotal = 0;
  private unreadBaselineReady = false;

  constructor(
    private api: AssistantChatsAdminService,
    private sound: ChatNotificationSoundService,
  ) {}

  start(): void {
    if (!this.canPoll()) {
      this.unreadTotal$.next(0);
      return;
    }
    this.sound.unlock();
    this.refresh();
    this.stop();
    this.pollTimer = setInterval(() => this.refresh(), 20000);
  }

  stop(): void {
    if (this.pollTimer) {
      clearInterval(this.pollTimer);
      this.pollTimer = null;
    }
  }

  refresh(): void {
    if (!this.canPoll()) {
      this.unreadTotal$.next(0);
      return;
    }
    this.api.unreadSummary().subscribe({
      next: (res) => {
        const total = Number(res?.data?.total_unread ?? 0);
        const safe = Number.isFinite(total) ? total : 0;
        if (this.unreadBaselineReady && safe > this.lastUnreadTotal) {
          this.sound.playNewMessage();
        }
        this.lastUnreadTotal = safe;
        this.unreadBaselineReady = true;
        this.unreadTotal$.next(safe);
      },
      error: () => {},
    });
  }

  private canPoll(): boolean {
    return typeof localStorage !== 'undefined' && !!localStorage.getItem('user_token');
  }
}
