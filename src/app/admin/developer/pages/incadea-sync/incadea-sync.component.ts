import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule, Router } from '@angular/router';
import { IncadeaSyncService } from '../../services/incadea-sync.service';
import { SyncResult, SyncLog, SyncConfig } from '../../interfaces/incadea-sync.interfaces';

@Component({
  selector: 'app-incadea-sync',
  standalone: true,
  imports: [CommonModule, FormsModule, RouterModule],
  templateUrl: './incadea-sync.component.html',
  styleUrls: ['./incadea-sync.component.css'],
})
export class IncadeaSyncComponent implements OnInit {
  syncing = false;
  lastResult: SyncResult | null = null;
  logs: SyncLog[] = [];
  logsLoading = false;

  // Config
  config: SyncConfig = { excluded_brands: [], excluded_categories: [] };
  configLoading = false;
  configSaving = false;
  newBrand = '';
  newCategory = '';

  // Messages
  syncError = '';
  configSuccess = '';
  configError = '';
  apiProbe: { endpoint?: string; http_status?: number | null; ok?: boolean; hint?: string; error?: string } | null = null;

  constructor(
    private syncService: IncadeaSyncService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.loadLogs();
    this.loadConfig();
  }

  goBack(): void {
    this.router.navigateByUrl('/admin/developer');
  }

  // ── Sync ──

  onSync(): void {
    this.syncing = true;
    this.syncError = '';
    this.lastResult = null;
    this.syncService.triggerSync({
      excluded_brands: this.config.excluded_brands,
      excluded_categories: this.config.excluded_categories,
    }).subscribe({
      next: (res: any) => {
        this.lastResult = res.data;
        this.syncing = false;
        this.loadLogs();
      },
      error: (err: any) => {
        this.syncError = err?.error?.message || 'Error al ejecutar la sincronización';
        this.syncing = false;
      },
    });
  }

  // ── Logs ──

  loadLogs(): void {
    this.logsLoading = true;
    this.syncService.getLogs().subscribe({
      next: (res: any) => {
        this.logs = res.data?.logs || [];
        this.logsLoading = false;
      },
      error: () => { this.logsLoading = false; },
    });
  }

  // ── Config ──

  loadConfig(): void {
    this.configLoading = true;
    this.syncService.getConfig().subscribe({
      next: (res: any) => {
        const cfg = res.data?.config || {};
        this.config = {
          excluded_brands: cfg.excluded_brands || [],
          excluded_categories: cfg.excluded_categories || [],
        };
        this.apiProbe = res.data?.api_probe ?? null;
        this.configLoading = false;
      },
      error: () => { this.configLoading = false; },
    });
  }

  saveConfig(): void {
    this.configSaving = true;
    this.configSuccess = '';
    this.configError = '';
    this.syncService.updateConfig(this.config).subscribe({
      next: () => {
        this.configSuccess = 'Configuración guardada correctamente';
        this.configSaving = false;
        setTimeout(() => { this.configSuccess = ''; }, 3000);
      },
      error: (err: any) => {
        this.configError = err?.error?.message || 'Error al guardar la configuración';
        this.configSaving = false;
      },
    });
  }

  addBrand(): void {
    const val = this.newBrand.trim();
    if (val && this.config.excluded_brands.indexOf(val) === -1) {
      this.config.excluded_brands = [].concat(this.config.excluded_brands as any, [val] as any);
      this.newBrand = '';
    }
  }

  removeBrand(brand: string): void {
    this.config.excluded_brands = this.config.excluded_brands.filter((b: string) => b !== brand);
  }

  addCategory(): void {
    const val = this.newCategory.trim();
    if (val && this.config.excluded_categories.indexOf(val) === -1) {
      this.config.excluded_categories = [].concat(this.config.excluded_categories as any, [val] as any);
      this.newCategory = '';
    }
  }

  removeCategory(cat: string): void {
    this.config.excluded_categories = this.config.excluded_categories.filter((c: string) => c !== cat);
  }

  getStatusClass(status: string): string {
    switch (status) {
      case 'completed': return 'badge-success';
      case 'failed': return 'badge-error';
      case 'running': return 'badge-warning';
      default: return '';
    }
  }

  getStatusLabel(status: string): string {
    switch (status) {
      case 'completed': return 'Completado';
      case 'failed': return 'Fallido';
      case 'running': return 'En progreso';
      default: return status;
    }
  }
}
