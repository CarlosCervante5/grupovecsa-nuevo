import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { AdminDashboardService } from '../../../shared/services/admin-dashboard.service';
import * as echarts from 'echarts';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  standalone: false,
})
export class DashboardComponent implements OnInit, AfterViewInit, OnDestroy {
  loading = true;
  error = false;

  @ViewChild('eventsBarChart') eventsBarChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Promociones', value: '—', icon: 'local_offer', color: '#1c69d4', loading: true },
    { label: 'Eventos', value: '—', icon: 'event', color: '#059669', loading: true },
    { label: 'Recompensas', value: '—', icon: 'emoji_events', color: '#d97706', loading: true },
  ];

  quickLinks: { label: string; icon: string; route: string }[] = [
    { label: 'Promociones', icon: 'campaign', route: '/admin/gestor/promotions' },
    { label: 'Eventos', icon: 'event', route: '/admin/gestor/scheduled-events' },
    { label: 'Recompensas', icon: 'emoji_events', route: '/admin/gestor/rewards' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {
    // Add dynamic links based on permissions
    let permissions: string[] = [];
    try { permissions = JSON.parse(localStorage.getItem('permissions') || '[]'); } catch {}
    if (permissions.includes('access store_management')) {
      this.quickLinks.push({ label: 'Tienda', icon: 'storefront', route: '/admin/store' });
    }
    if (permissions.includes('access benchmark')) {
      this.quickLinks.push({ label: 'Benchmark ADS', icon: 'monitoring', route: '/admin/benchmark' });
    }
  }

  ngOnInit(): void {
    this.loadMetrics();
  }

  ngAfterViewInit(): void {
    setTimeout(() => this.initCharts(), 400);
  }

  ngOnDestroy(): void {
    window.removeEventListener('resize', this.onResize);
    this.chartInstances.forEach(c => c.dispose());
  }

  private loadMetrics(): void {
    this.dashboardService.getMetrics().subscribe({
      next: (res: any) => {
        const data = res?.data;
        if (data?.stats) {
          const s = data.stats;
          const keys = ['promotions', 'events', 'active_rewards'];
          keys.forEach((key, i) => {
            if (this.stats[i]) {
              this.stats[i].value = s[key] ?? 0;
              this.stats[i].loading = false;
            }
          });
        } else {
          this.stats.forEach(s => { s.value = 0; s.loading = false; });
        }
        if (data?.charts) {
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
}
