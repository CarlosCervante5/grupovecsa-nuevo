import { Component, OnInit } from '@angular/core';
import { StregaOpportunityService } from '../../services/strega-opportunity.service';

@Component({
  selector: 'app-strega-leads',
  templateUrl: './strega-leads.component.html',
  styleUrls: ['./strega-leads.component.css'],
  standalone: false,
})
export class StregaLeadsComponent implements OnInit {
  loading = true;
  error = false;
  keyword = '';
  page = 1;
  lastPage = 1;
  total = 0;
  rows: Record<string, unknown>[] = [];
  displayedColumns = ['customer', 'type', 'dealership', 'status', 'created'];

  constructor(private strega: StregaOpportunityService) {}

  ngOnInit(): void {
    this.load();
  }

  search(): void {
    this.page = 1;
    this.load();
  }

  goPage(p: number): void {
    if (p < 1 || p > this.lastPage) {
      return;
    }
    this.page = p;
    this.load();
  }

  customerName(row: Record<string, unknown>): string {
    const c = row['customer'] as Record<string, string> | undefined;
    if (!c) {
      return '—';
    }
    const n = [c['name'], c['last_name']].filter(Boolean).join(' ').trim();
    return n || '—';
  }

  private load(): void {
    this.loading = true;
    this.error = false;
    const role = (localStorage.getItem('role') || '').trim();
    const params: Record<string, string | number> = {
      paginate: 15,
      page: this.page,
    };
    if (this.keyword.trim()) {
      params.keyword = this.keyword.trim();
    }
    this.strega.searchLeads(role, params).subscribe({
      next: (res: any) => {
        const d = res?.data;
        this.rows = Array.isArray(d?.data) ? d.data : [];
        this.lastPage = d?.last_page ?? 1;
        this.total = d?.total ?? this.rows.length;
        this.loading = false;
      },
      error: () => {
        this.error = true;
        this.loading = false;
        this.rows = [];
      },
    });
  }
}
