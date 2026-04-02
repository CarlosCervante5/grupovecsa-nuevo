import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { ApiMonitorService } from '../../services/api-monitor.service';

@Component({
  selector: 'app-api-monitor',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './api-monitor.component.html',
  styleUrls: ['./api-monitor.component.css'],
})
export class ApiMonitorComponent implements OnInit, OnDestroy {
  // Health
  services: any[] = [];
  healthLoading = true;
  healthError = '';

  // Stats
  stats: any = {};
  statsLoading = true;

  // Logs
  logs: any[] = [];
  logsLoading = true;
  logSearch = '';
  currentPage = 1;
  lastPage = 1;
  totalLogs = 0;

  private refreshInterval: any;

  constructor(
    private monitorService: ApiMonitorService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.loadHealth();
    this.loadStats();
    this.loadLogs();
    // Auto-refresh every 30s
    this.refreshInterval = setInterval(() => {
      this.loadHealth();
      this.loadStats();
    }, 30000);
  }

  ngOnDestroy(): void {
    if (this.refreshInterval) clearInterval(this.refreshInterval);
  }

  goBack(): void {
    this.router.navigateByUrl('/admin/developer');
  }

  // ── Health ──

  loadHealth(): void {
    this.healthLoading = true;
    this.healthError = '';
    this.monitorService.getHealth().subscribe({
      next: (res: any) => {
        this.services = res.data?.services || [];
        this.healthLoading = false;
      },
      error: (err: any) => {
        this.healthError = err?.error?.message || 'Error al verificar servicios';
        this.healthLoading = false;
      },
    });
  }

  // ── Stats ──

  loadStats(): void {
    this.statsLoading = true;
    this.monitorService.getStats().subscribe({
      next: (res: any) => {
        this.stats = res.data || {};
        this.statsLoading = false;
      },
      error: () => { this.statsLoading = false; },
    });
  }

  // ── Logs ──

  loadLogs(): void {
    this.logsLoading = true;
    this.monitorService.getLogs({ search: this.logSearch, per_page: 30, page: this.currentPage }).subscribe({
      next: (res: any) => {
        const paginated = res.data?.logs || {};
        this.logs = paginated.data || [];
        this.currentPage = paginated.current_page || 1;
        this.lastPage = paginated.last_page || 1;
        this.totalLogs = paginated.total || 0;
        this.logsLoading = false;
      },
      error: () => { this.logsLoading = false; },
    });
  }

  onSearch(): void {
    this.currentPage = 1;
    this.loadLogs();
  }

  prevPage(): void {
    if (this.currentPage > 1) { this.currentPage--; this.loadLogs(); }
  }

  nextPage(): void {
    if (this.currentPage < this.lastPage) { this.currentPage++; this.loadLogs(); }
  }

  getStatusColor(status: string): string {
    return status === 'ok' ? '#16a34a' : '#ef4444';
  }

  getStatusBg(status: string): string {
    return status === 'ok' ? '#dcfce7' : '#fef2f2';
  }

  getMethodColor(method: string): string {
    switch (method) {
      case 'GET': return '#16a34a';
      case 'POST': return '#1c69d4';
      case 'PUT': return '#d97706';
      case 'DELETE': return '#ef4444';
      default: return '#64748b';
    }
  }

  formatBytes(bytes: number): string {
    if (!bytes || bytes === 0) return '0 B';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }

  get healthyCount(): number {
    return this.services.filter((s: any) => s.status === 'ok').length;
  }
}
