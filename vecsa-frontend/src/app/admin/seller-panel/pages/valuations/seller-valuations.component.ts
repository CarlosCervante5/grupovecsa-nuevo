import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { environment } from '@environments/environment';

@Component({
  selector: 'app-seller-valuations',
  templateUrl: './seller-valuations.component.html',
  styleUrls: ['./seller-valuations.component.css'],
  standalone: false,
})
export class SellerValuationsComponent implements OnInit {
  loading = true;
  error = false;
  keyword = '';
  page = 1;
  lastPage = 1;
  total = 0;
  rows: Record<string, unknown>[] = [];
  displayedColumns = ['customer', 'vehicle', 'year', 'uuid'];

  constructor(private http: HttpClient) {}

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
    const appt = row['appointment'] as Record<string, unknown> | undefined;
    const c = appt?.['customer'] as Record<string, string> | undefined;
    if (!c) {
      return '—';
    }
    const n = [c['name'], c['last_name']].filter(Boolean).join(' ').trim();
    return n || '—';
  }

  vehicleLabel(row: Record<string, unknown>): string {
    const appt = row['appointment'] as Record<string, unknown> | undefined;
    const v = appt?.['vehicle'] as Record<string, string> | undefined;
    if (!v) {
      return '—';
    }
    return [v['brand_name'], v['model_name']].filter(Boolean).join(' ') || '—';
  }

  vehicleYear(row: Record<string, unknown>): string {
    const appt = row['appointment'] as Record<string, unknown> | undefined;
    const v = appt?.['vehicle'] as Record<string, string> | undefined;
    return v?.['year'] ? String(v['year']) : '—';
  }

  private load(): void {
    this.loading = true;
    this.error = false;
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    let params = new HttpParams().set('page', String(this.page));
    if (this.keyword.trim()) {
      params = params.set('keyword', this.keyword.trim());
    }
    this.http
      .get<Record<string, unknown>>(`${environment.baseUrl}/api/valuations/search`, { headers, params })
      .subscribe({
        next: (res) => {
          const d = res?.['data'] as Record<string, unknown> | undefined;
          this.rows = Array.isArray(d?.['data']) ? (d!['data'] as Record<string, unknown>[]) : [];
          this.lastPage = (d?.['last_page'] as number) ?? 1;
          this.total = (d?.['total'] as number) ?? this.rows.length;
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
