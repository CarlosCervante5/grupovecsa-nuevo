import { Component, OnDestroy } from '@angular/core';
import { PageEvent } from '@angular/material/paginator';
import { reload } from '@helpers/session.helper';
import { Router } from '@angular/router';
import {
  AssistantChatDetail,
  AssistantChatListRow,
  AssistantChatMessage,
  AssistantChatsAdminService,
} from '@services/assistant-chats-admin.service';

@Component({
  selector: 'app-assistant-chats',
  templateUrl: './assistant-chats.component.html',
  styleUrls: ['./assistant-chats.component.css'],
  standalone: false,
})
export class AssistantChatsComponent implements OnDestroy {
  rows: AssistantChatListRow[] = [];
  loading = true;
  detailLoading = false;
  actionLoading = false;
  selected: AssistantChatDetail | null = null;
  agentReply = '';

  searchTerm = '';
  pageIndex = 0;
  pageSize = 20;
  total = 0;

  private refreshTimer: ReturnType<typeof setInterval> | null = null;

  constructor(
    private api: AssistantChatsAdminService,
    private router: Router
  ) {
    this.loadList();
  }

  ngOnDestroy(): void {
    this.stopAutoRefresh();
  }

  loadList(): void {
    this.loading = true;
    const body: Record<string, string | number> = {
      page: this.pageIndex + 1,
      per_page: this.pageSize,
    };
    if (this.searchTerm.trim()) {
      body['search'] = this.searchTerm.trim();
    }

    this.api.search(body).subscribe({
      next: (res) => {
        const p = res?.data?.conversations;
        this.rows = p?.data ?? [];
        this.total = p?.total ?? 0;
        if (p?.per_page != null) {
          this.pageSize = p.per_page;
        }
        this.loading = false;
      },
      error: (err) => {
        this.loading = false;
        reload(err, this.router);
      },
    });
  }

  onSearch(): void {
    this.pageIndex = 0;
    this.loadList();
  }

  onPage(event: PageEvent): void {
    this.pageIndex = event.pageIndex;
    this.pageSize = event.pageSize;
    this.loadList();
  }

  openRow(row: AssistantChatListRow): void {
    this.detailLoading = true;
    this.selected = null;
    this.agentReply = '';
    this.stopAutoRefresh();
    this.api.detail(row.uuid).subscribe({
      next: (res) => {
        this.selected = res?.data?.conversation ?? null;
        this.detailLoading = false;
        this.scheduleAutoRefresh();
        setTimeout(() => this.scrollThread(), 50);
      },
      error: (err) => {
        this.detailLoading = false;
        reload(err, this.router);
      },
    });
  }

  takeOver(): void {
    if (!this.selected || this.actionLoading) {
      return;
    }
    this.actionLoading = true;
    this.api.takeOver(this.selected.uuid).subscribe({
      next: (res) => {
        this.selected = res?.data?.conversation ?? this.selected;
        this.actionLoading = false;
        this.syncRowFromSelected();
        this.scheduleAutoRefresh();
        setTimeout(() => this.scrollThread(), 50);
      },
      error: (err) => {
        this.actionLoading = false;
        reload(err, this.router);
      },
    });
  }

  sendAgentReply(): void {
    const text = this.agentReply.trim();
    if (!this.selected || !text || this.actionLoading) {
      return;
    }
    this.actionLoading = true;
    this.api.reply(this.selected.uuid, text).subscribe({
      next: (res) => {
        this.selected = res?.data?.conversation ?? this.selected;
        this.agentReply = '';
        this.actionLoading = false;
        this.syncRowFromSelected();
        setTimeout(() => this.scrollThread(), 50);
      },
      error: (err) => {
        this.actionLoading = false;
        reload(err, this.router);
      },
    });
  }

  releaseToBot(): void {
    if (!this.selected || this.actionLoading) {
      return;
    }
    this.actionLoading = true;
    this.api.release(this.selected.uuid).subscribe({
      next: (res) => {
        this.selected = res?.data?.conversation ?? this.selected;
        this.actionLoading = false;
        this.syncRowFromSelected();
        this.stopAutoRefresh();
        setTimeout(() => this.scrollThread(), 50);
      },
      error: (err) => {
        this.actionLoading = false;
        reload(err, this.router);
      },
    });
  }

  messageRoleLabel(msg: AssistantChatMessage): string {
    if (msg.role === 'user') {
      return 'Cliente';
    }
    if (msg.role === 'agent') {
      return 'Asesor';
    }
    return 'Asistente';
  }

  messageCssClass(msg: AssistantChatMessage): string {
    if (msg.role === 'user') {
      return 'thread-msg-user';
    }
    if (msg.role === 'agent') {
      return 'thread-msg-agent';
    }
    return 'thread-msg-assistant';
  }

  formatDate(val: string | null): string {
    if (!val) {
      return '—';
    }
    const d = new Date(val.replace(' ', 'T'));
    if (Number.isNaN(d.getTime())) {
      return val;
    }
    return d.toLocaleString('es-MX', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  shortUrl(url: string | null): string {
    if (!url) {
      return '';
    }
    try {
      const u = new URL(url, window.location.origin);
      return u.pathname.length > 48 ? u.pathname.slice(0, 45) + '…' : u.pathname;
    } catch {
      return url.length > 48 ? url.slice(0, 45) + '…' : url;
    }
  }

  private scheduleAutoRefresh(): void {
    this.stopAutoRefresh();
    if (!this.selected?.is_human_handoff) {
      return;
    }
    this.refreshTimer = setInterval(() => this.refreshSelected(), 8000);
  }

  private stopAutoRefresh(): void {
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer);
      this.refreshTimer = null;
    }
  }

  private refreshSelected(): void {
    if (!this.selected || this.detailLoading || this.actionLoading) {
      return;
    }
    this.api.detail(this.selected.uuid).subscribe({
      next: (res) => {
        const next = res?.data?.conversation as AssistantChatDetail | undefined;
        if (!next || next.uuid !== this.selected?.uuid) {
          return;
        }
        const prevCount = this.selected.messages?.length ?? 0;
        this.selected = next;
        this.syncRowFromSelected();
        if ((next.messages?.length ?? 0) > prevCount) {
          setTimeout(() => this.scrollThread(), 50);
        }
      },
    });
  }

  private syncRowFromSelected(): void {
    if (!this.selected) {
      return;
    }
    const idx = this.rows.findIndex((r) => r.uuid === this.selected?.uuid);
    if (idx >= 0) {
      this.rows[idx] = {
        ...this.rows[idx],
        preview: this.selected.preview,
        assigned_user_name: this.selected.assigned_user_name,
        messages_count: this.selected.messages_count,
        last_message_at: this.selected.last_message_at,
        is_human_handoff: this.selected.is_human_handoff,
      };
    }
  }

  private scrollThread(): void {
    const el = document.querySelector('.thread-scroll');
    if (el) {
      el.scrollTop = el.scrollHeight;
    }
  }
}
