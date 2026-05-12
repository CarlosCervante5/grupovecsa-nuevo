import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { DevCrudService } from '../../developer/services/dev-crud.service';
import { adminRouteSegmentForRole } from 'src/app/admin/utils/admin-route.util';

@Component({
  selector: 'app-benchmark',
  templateUrl: './benchmark.component.html',
  styleUrls: ['./benchmark.component.css'],
  standalone: false,
})
export class BenchmarkComponent implements OnInit {
  competitors: string[] = [];
  history: any[] = [];
  reports: string[] = [];
  scanResults: any = null;
  scanDetail: any[] = [];
  loading = false;
  scanning = false;
  error = '';
  method: 'scraper' | 'api' = 'api';
  newCompetitor = '';
  showCompetitors = false;
  tab: 'scan' | 'history' | 'reports' = 'scan';

  metaTokenConfigured = false;
  metaTokenSource: 'storage' | 'env' | null = null;
  metaTokenInput = '';
  metaTokenSaving = false;
  metaTokenMessage = '';

  /** true si en el backend existe BENCHMARK_REPORT_ADS_URL (proxy al Node reportADS). */
  scraperProxyConfigured = false;

  get totalAds(): number {
    if (!this.scanResults?.summary) return 0;
    return this.scanResults.summary.reduce((s: number, r: any) => s + (r.adsCount || 0), 0);
  }

  constructor(private router: Router, private crud: DevCrudService) {}

  ngOnInit(): void {
    this.loadBenchmarkOptions();
    this.loadCompetitors();
    this.loadMetaTokenStatus();
  }

  loadBenchmarkOptions(): void {
    this.crud.fetch('benchmark/options', 'GET', {}).subscribe({
      next: (res: any) => {
        this.scraperProxyConfigured = !!res?.scraperProxyConfigured;
        if (!this.scraperProxyConfigured && this.method === 'scraper') {
          this.method = 'api';
        }
      },
      error: () => {
        this.scraperProxyConfigured = false;
        if (this.method === 'scraper') {
          this.method = 'api';
        }
      },
    });
  }

  loadMetaTokenStatus(): void {
    this.crud.fetch('benchmark/meta-token', 'GET', {}).subscribe({
      next: (res: any) => {
        this.metaTokenConfigured = !!res?.configured;
        this.metaTokenSource = res?.source ?? null;
        if (this.metaTokenConfigured) {
          this.method = 'api';
        }
      },
      error: () => {
        this.metaTokenConfigured = false;
        this.metaTokenSource = null;
      },
    });
  }

  saveMetaToken(): void {
    const token = this.metaTokenInput.trim();
    if (!token) {
      return;
    }
    this.metaTokenSaving = true;
    this.metaTokenMessage = '';
    this.crud.store('benchmark/meta-token', { token }).subscribe({
      next: (res: any) => {
        this.metaTokenSaving = false;
        this.metaTokenInput = '';
        this.metaTokenConfigured = !!res?.configured;
        this.metaTokenSource = res?.source ?? 'storage';
        this.metaTokenMessage = res?.message || 'Token guardado.';
      },
      error: (err: any) => {
        this.metaTokenSaving = false;
        const e = err?.error;
        const first = e?.errors?.token?.[0];
        this.metaTokenMessage = first || e?.message || e?.error || 'No se pudo guardar el token.';
      },
    });
  }

  clearMetaToken(): void {
    this.metaTokenSaving = true;
    this.metaTokenMessage = '';
    this.crud.deleteAt('benchmark/meta-token').subscribe({
      next: (res: any) => {
        this.metaTokenSaving = false;
        this.metaTokenConfigured = !!res?.configured;
        this.metaTokenSource = res?.source ?? null;
        this.metaTokenMessage = res?.message || 'Token eliminado.';
      },
      error: (err: any) => {
        this.metaTokenSaving = false;
        this.metaTokenMessage = err?.error?.message || err?.error?.error || 'Error al eliminar.';
      },
    });
  }

  loadCompetitors(): void {
    this.loading = true;
    this.error = '';
    this.crud.fetch('benchmark/competitors', 'GET', {}).subscribe({
      next: (res: any) => { this.competitors = Array.isArray(res) ? res : []; this.loading = false; },
      error: (err: any) => {
        this.error = err?.error?.error || 'No se pudo cargar la lista de competidores desde el servidor.';
        this.loading = false;
      },
    });
  }

  startScan(): void {
    this.scanning = true;
    this.error = '';
    this.scanResults = null;
    this.scanDetail = [];
    this.crud.store('benchmark/scan', { method: this.method }, { timeoutMs: 600_000 }).subscribe({
      next: (res: any) => {
        this.scanResults = res;
        this.scanning = false;
        this.crud.fetch('benchmark/history', 'GET', {}).subscribe({
          next: (hist: any) => {
            const list = Array.isArray(hist) ? hist : [];
            if (list.length > 0) {
              this.crud.fetch('benchmark/history/' + list[0].file, 'GET', {}).subscribe({
                next: (detail: any) => { this.scanDetail = Array.isArray(detail) ? detail : []; },
              });
            }
          },
        });
      },
      error: (err: any) => {
        if (err?.name === 'TimeoutError') {
          this.error =
            'Tiempo de espera agotado (10 minutos). Si escanea muchos competidores, puede agotarse el límite del navegador o del servidor; pruebe con menos filas o revise BENCHMARK_META_REQUEST_DELAY_US en el backend.';
        } else {
          const e = err?.error;
          this.error =
            (typeof e === 'string' ? e : null) ||
            e?.error ||
            e?.message ||
            (Array.isArray(e?.errors) ? e.errors.join(' ') : '') ||
            'Error al escanear';
        }
        this.scanning = false;
      },
    });
  }

  loadHistory(): void {
    this.tab = 'history';
    this.loading = true;
    this.crud.fetch('benchmark/history', 'GET', {}).subscribe({
      next: (res: any) => { this.history = Array.isArray(res) ? res : []; this.loading = false; },
      error: () => { this.history = []; this.loading = false; },
    });
  }

  loadReports(): void {
    this.tab = 'reports';
    this.loading = true;
    this.crud.fetch('benchmark/reports', 'GET', {}).subscribe({
      next: (res: any) => { this.reports = Array.isArray(res) ? res : []; this.loading = false; },
      error: () => { this.reports = []; this.loading = false; },
    });
  }

  addCompetitor(): void {
    const name = this.newCompetitor.trim();
    if (!name) return;
    this.crud.store('benchmark/competitors', { name }).subscribe({
      next: (res: any) => {
        this.competitors = res?.competitors || [...this.competitors, name];
        this.newCompetitor = '';
      },
      error: (err: any) => { this.error = err?.error?.error || 'Error al agregar'; },
    });
  }

  removeCompetitor(name: string): void {
    this.crud.deleteById('benchmark/competitors', encodeURIComponent(name)).subscribe({
      next: (res: any) => { this.competitors = res?.competitors || this.competitors.filter(c => c !== name); },
      error: () => {},
    });
  }

  viewHistoryDetail(file: string): void {
    this.loading = true;
    this.crud.fetch('benchmark/history/' + file, 'GET', {}).subscribe({
      next: (detail: any) => {
        this.scanDetail = Array.isArray(detail) ? detail : [];
        this.tab = 'scan';
        this.loading = false;
      },
      error: () => { this.loading = false; },
    });
  }

  /** Anuncios para tarjetas: soporta JSON guardado solo con `data` (Meta API) o con `ads` (scraper). */
  displayAds(result: any): any[] {
    if (Array.isArray(result?.ads) && result.ads.length > 0) {
      return result.ads;
    }
    const data = result?.data;
    if (!Array.isArray(data) || data.length === 0) {
      return [];
    }
    return data.map((row: any) => ({
      text: Array.isArray(row?.ad_creative_bodies)
        ? row.ad_creative_bodies.join('\n')
        : String(row?.ad_creative_bodies ?? ''),
      images:
        row?.ad_snapshot_url && typeof row.ad_snapshot_url === 'string' ? [row.ad_snapshot_url] : [],
      imageCount: row?.ad_snapshot_url ? 1 : 0,
      videoCount: 0,
    }));
  }

  goBack(): void {
    const role = localStorage.getItem('role') || '';
    this.router.navigate(['/admin', adminRouteSegmentForRole(role)]);
  }
}
