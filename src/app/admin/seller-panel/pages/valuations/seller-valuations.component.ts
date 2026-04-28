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
  inventoryLoading = true;
  error = false;
  inventoryError = false;
  keyword = '';
  inventoryKeyword = '';
  page = 1;
  lastPage = 1;
  total = 0;
  rows: Record<string, unknown>[] = [];
  displayedColumns = ['customer', 'vehicle', 'year', 'uuid'];
  inventoryPage = 1;
  inventoryLastPage = 1;
  inventoryTotal = 0;
  inventoryRows: Record<string, unknown>[] = [];
  inventoryColumns = ['vehicle', 'year', 'price', 'share'];
  appointmentMetrics = {
    total: 0,
    pending: 0,
    inProgress: 0,
    completed: 0,
  };

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.load();
    this.loadInventory();
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

  searchInventory(): void {
    this.inventoryPage = 1;
    this.loadInventory();
  }

  goInventoryPage(p: number): void {
    if (p < 1 || p > this.inventoryLastPage) {
      return;
    }
    this.inventoryPage = p;
    this.loadInventory();
  }

  get sellerUuid(): string {
    try {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      return (user?.uuid || '').toString();
    } catch {
      return '';
    }
  }

  get basePublicUrl(): string {
    if (typeof window !== 'undefined' && window.location?.origin) {
      return window.location.origin;
    }
    return '';
  }

  get referralLink(): string {
    const uuid = this.sellerUuid;
    if (!uuid) {
      return `${this.basePublicUrl}/compra-tu-auto`;
    }
    return `${this.basePublicUrl}/compra-tu-auto?seller=${encodeURIComponent(uuid)}`;
  }

  vehicleReferralLink(row: Record<string, unknown>): string {
    const uuid = (row['uuid'] || '').toString();
    if (!uuid) {
      return this.referralLink;
    }
    const seller = this.sellerUuid;
    if (!seller) {
      return `${this.basePublicUrl}/compra-tu-auto/detail/${encodeURIComponent(uuid)}`;
    }
    return `${this.basePublicUrl}/compra-tu-auto/detail/${encodeURIComponent(uuid)}?seller=${encodeURIComponent(seller)}`;
  }

  async copyToClipboard(value: string): Promise<void> {
    if (!value) {
      return;
    }
    try {
      await navigator.clipboard.writeText(value);
    } catch {
      // noop: no bloquear flujo si el navegador no permite clipboard
    }
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

  inventoryVehicleLabel(row: Record<string, unknown>): string {
    const brand = (row['brand'] as Record<string, unknown> | undefined)?.['name'];
    const line = (row['line'] as Record<string, unknown> | undefined)?.['name'];
    const model = (row['model'] as Record<string, unknown> | undefined)?.['name'];
    return [brand, line, model].filter(Boolean).join(' ') || '—';
  }

  inventoryYear(row: Record<string, unknown>): string {
    const model = row['model'] as Record<string, unknown> | undefined;
    const year = model?.['year'];
    return year ? String(year) : '—';
  }

  inventoryPrice(row: Record<string, unknown>): string {
    const raw = Number(row['sale_price'] ?? row['list_price'] ?? 0);
    if (!Number.isFinite(raw) || raw <= 0) {
      return '—';
    }
    return raw.toLocaleString('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 0 });
  }

  private computeAppointmentMetrics(rows: Record<string, unknown>[]): void {
    const summary = { total: rows.length, pending: 0, inProgress: 0, completed: 0 };
    rows.forEach((row) => {
      const status = String(row['status'] || '').toLowerCase();
      if (status === 'to_appraise' || status === 'pending') {
        summary.pending += 1;
      } else if (status === 'on_progress' || status === 'in_progress' || status === 'checklist_ready') {
        summary.inProgress += 1;
      } else if (status === 'valuated' || status === 'completed') {
        summary.completed += 1;
      }
    });
    this.appointmentMetrics = summary;
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
          this.computeAppointmentMetrics(this.rows);
          this.loading = false;
        },
        error: () => {
          this.error = true;
          this.loading = false;
          this.rows = [];
          this.computeAppointmentMetrics([]);
        },
      });
  }

  private loadInventory(): void {
    this.inventoryLoading = true;
    this.inventoryError = false;
    let params = new HttpParams()
      .set('page', String(this.inventoryPage))
      .set('paginate', '10')
      .set('status', 'active')
      .set('has_images', 'true');
    if (this.inventoryKeyword.trim()) {
      params = params.set('keyword', this.inventoryKeyword.trim());
    }
    this.http
      .get<Record<string, unknown>>(`${environment.baseUrl}/api/vehicles/search`, { params })
      .subscribe({
        next: (res) => {
          const d = res?.['data'] as Record<string, unknown> | undefined;
          this.inventoryRows = Array.isArray(d?.['data']) ? (d!['data'] as Record<string, unknown>[]) : [];
          this.inventoryLastPage = (d?.['last_page'] as number) ?? 1;
          this.inventoryTotal = (d?.['total'] as number) ?? this.inventoryRows.length;
          this.inventoryLoading = false;
        },
        error: () => {
          this.inventoryError = true;
          this.inventoryLoading = false;
          this.inventoryRows = [];
        },
      });
  }
}
