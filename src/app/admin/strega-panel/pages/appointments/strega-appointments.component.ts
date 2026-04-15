import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';
import { StregaOpportunityService } from '../../services/strega-opportunity.service';

@Component({
  selector: 'app-strega-appointments',
  templateUrl: './strega-appointments.component.html',
  styleUrls: ['./strega-appointments.component.css'],
  standalone: false,
})
export class StregaAppointmentsComponent implements OnInit {
  loading = true;
  error = false;
  keyword = '';
  page = 1;
  lastPage = 1;
  total = 0;
  rows: Record<string, unknown>[] = [];
  displayedColumns = ['customer', 'status', 'followups', 'opportunity'];

  constructor(
    private strega: StregaOpportunityService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    if ((localStorage.getItem('role') || '').trim() !== 'strega-manager') {
      void this.router.navigateByUrl(adminDashboardUrl(localStorage.getItem('role')));
      return;
    }
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

  opportunityHint(row: Record<string, unknown>): string {
    const opp = row['opportunity'] as Record<string, unknown> | undefined;
    if (!opp) {
      return '—';
    }
    const type = opp['type'];
    const camp = (opp['campaign'] as Record<string, string> | undefined)?.['name'];
    return [type, camp].filter(Boolean).join(' · ') || '—';
  }

  private load(): void {
    this.loading = true;
    this.error = false;
    const params: Record<string, string | number> = {
      paginate: 15,
      page: this.page,
    };
    if (this.keyword.trim()) {
      params.keyword = this.keyword.trim();
    }
    this.strega.searchAppointments(params).subscribe({
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
