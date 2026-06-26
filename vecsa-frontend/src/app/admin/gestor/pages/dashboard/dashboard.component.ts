import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { AdminDashboardService } from '../../../shared/services/admin-dashboard.service';
import * as echarts from 'echarts';
import {
  adminBenchmarkUrl,
  adminDashboardUrl,
  adminGestorPanelBaseFromRouterUrl,
} from 'src/app/admin/utils/admin-route.util';
import { expandLegacyGestorPermissions, GESTOR_FEATURE_PERMISSIONS } from 'src/app/admin/utils/gestor-feature-permissions';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  standalone: false,
})
export class DashboardComponent implements OnInit, AfterViewInit, OnDestroy {
  loading = true;
  error = false;

  /** Muestra gráfica de eventos solo si el usuario puede gestionar eventos. */
  showEventsChart = false;

  @ViewChild('eventsBarChart') eventsBarChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: {
    metricKey: string;
    label: string;
    value: string | number;
    icon: string;
    color: string;
    loading: boolean;
  }[] = [];

  quickLinks: { label: string; icon: string; route: string }[] = [];

  private readonly statDefs: {
    metricKey: string;
    label: string;
    icon: string;
    color: string;
    perm: string;
  }[] = [
    { metricKey: 'promotions', label: 'Promociones', icon: 'local_offer', color: '#1c69d4', perm: GESTOR_FEATURE_PERMISSIONS.promotions },
    { metricKey: 'events', label: 'Agenda (eventos)', icon: 'event', color: '#059669', perm: GESTOR_FEATURE_PERMISSIONS.scheduledEvents },
    { metricKey: 'active_rewards', label: 'Recompensas', icon: 'emoji_events', color: '#d97706', perm: GESTOR_FEATURE_PERMISSIONS.rewards },
  ];

  private readonly quickDefs: { perm: string; label: string; icon: string; path: string }[] = [
    { perm: GESTOR_FEATURE_PERMISSIONS.promotions, label: 'Promociones', icon: 'campaign', path: 'promotions' },
    { perm: GESTOR_FEATURE_PERMISSIONS.scheduledEvents, label: 'Experience', icon: 'auto_stories', path: 'scheduled-events' },
    { perm: GESTOR_FEATURE_PERMISSIONS.rewards, label: 'Recompensas', icon: 'emoji_events', path: 'rewards' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {}

  ngOnInit(): void {
    this.buildFromPermissions();
    this.loadMetrics();
  }

  private buildFromPermissions(): void {
    let raw: string[] = [];
    try {
      raw = JSON.parse(localStorage.getItem('permissions') || '[]');
    } catch {
      raw = [];
    }
    const role = localStorage.getItem('role');
    const permissions = expandLegacyGestorPermissions(raw, role);
    const base = adminGestorPanelBaseFromRouterUrl(this.router.url) || adminDashboardUrl(role);

    this.showEventsChart = this.canSee(permissions, GESTOR_FEATURE_PERMISSIONS.scheduledEvents);

    this.stats = this.statDefs
      .filter((d) => this.canSee(permissions, d.perm))
      .map((d) => ({
        metricKey: d.metricKey,
        label: d.label,
        value: '—',
        icon: d.icon,
        color: d.color,
        loading: true,
      }));

    this.quickLinks = this.quickDefs
      .filter((d) => this.canSee(permissions, d.perm))
      .map((d) => ({ label: d.label, icon: d.icon, route: `${base}/${d.path}` }));

    if (raw.includes('access store_management')) {
      this.quickLinks.push({ label: 'Tienda', icon: 'storefront', route: '/admin/store' });
    }
    if (raw.includes('access benchmark')) {
      this.quickLinks.push({
        label: 'Benchmark ADS',
        icon: 'monitoring',
        route: adminBenchmarkUrl(role) ?? '/admin/benchmark',
      });
    }
  }

  /** @param effective permisos tras expandLegacyGestorPermissions (gestor sin permisos granulares = acceso completo al módulo). */
  private canSee(effective: string[], perm: string): boolean {
    const r = (localStorage.getItem('role') || '').toLowerCase();
    if (r === 'developer' || r === 'administrator') {
      return true;
    }
    return effective.includes(perm);
  }

  ngAfterViewInit(): void {
    setTimeout(() => this.initCharts(), 400);
  }

  ngOnDestroy(): void {
    window.removeEventListener('resize', this.onResize);
    this.chartInstances.forEach(c => c.dispose());
  }

  private loadMetrics(): void {
    const panel = this.resolveGestorPanel();
    this.dashboardService.getMetrics(panel).subscribe({
      next: (res: any) => {
        const data = res?.data;
        if (data?.stats) {
          const s = data.stats;
          this.stats.forEach((row) => {
            row.value = s[row.metricKey] ?? 0;
            row.loading = false;
          });
        } else {
          this.stats.forEach((s) => { s.value = 0; s.loading = false; });
        }
        if (data?.charts && this.showEventsChart) {
          this.chartsData = data.charts;
          setTimeout(() => this.initCharts(), 100);
        }
        this.loading = false;
      },
      error: () => {
        this.error = true;
        this.loading = false;
        this.stats.forEach(s => { s.value = 'Error'; s.loading = false; });
      },
    });
  }

  private initCharts(): void {
    if (!this.showEventsChart) {
      return;
    }
    this.loadEventsBarChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadEventsBarChart(): void {
    if (!this.eventsBarChartEl) return;
    // Dispose existing chart if any
    this.chartInstances.forEach(c => c.dispose());
    this.chartInstances = [];
    const chart = echarts.init(this.eventsBarChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.events_by_month || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 16, top: 20, bottom: 30 },
      xAxis: { type: 'category', data: data.map((d: any) => d.month), axisLabel: { fontSize: 11 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'bar', itemStyle: { color: '#059669', borderRadius: [4, 4, 0, 0] } }],
    });
  }

  navigateTo(route: string): void {
    this.router.navigateByUrl(route);
  }

  /** Indica al backend qué métricas devolver (gestor/manager), también si entra administrator. */
  private resolveGestorPanel(): 'gestor' | 'manager' | undefined {
    const base = adminGestorPanelBaseFromRouterUrl(this.router.url);
    if (base.endsWith('/manager')) {
      return 'manager';
    }
    if (base.endsWith('/gestor')) {
      return 'gestor';
    }
    return undefined;
  }
}
