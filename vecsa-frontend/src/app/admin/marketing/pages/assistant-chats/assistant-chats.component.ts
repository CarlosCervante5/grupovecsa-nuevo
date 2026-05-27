import { Component } from '@angular/core';
import { PageEvent } from '@angular/material/paginator';
import { reload } from '@helpers/session.helper';
import { Router } from '@angular/router';
import {
  AssistantChatDetail,
  AssistantChatListRow,
  AssistantChatsAdminService,
} from '@services/assistant-chats-admin.service';

@Component({
  selector: 'app-assistant-chats',
  templateUrl: './assistant-chats.component.html',
  styleUrls: ['./assistant-chats.component.css'],
  standalone: false,
})
export class AssistantChatsComponent {
  rows: AssistantChatListRow[] = [];
  loading = true;
  detailLoading = false;
  selected: AssistantChatDetail | null = null;

  searchTerm = '';
  pageIndex = 0;
  pageSize = 20;
  total = 0;

  constructor(
    private api: AssistantChatsAdminService,
    private router: Router
  ) {
    this.loadList();
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
    this.api.detail(row.uuid).subscribe({
      next: (res) => {
        this.selected = res?.data?.conversation ?? null;
        this.detailLoading = false;
      },
      error: (err) => {
        this.detailLoading = false;
        reload(err, this.router);
      },
    });
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
}
