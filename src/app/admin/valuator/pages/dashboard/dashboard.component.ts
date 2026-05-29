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

  @ViewChild('valuationStatusChart') valuationStatusChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Pendientes', value: '—', icon: 'pending', color: '#f59e0b', loading: true },
    { label: 'En progreso', value: '—', icon: 'autorenew', color: '#1c69d4', loading: true },
    { label: 'Completadas', value: '—', icon: 'check_circle', color: '#059669', loading: true },
    { label: 'Total', value: '—', icon: 'price_check', color: '#7c3aed', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Citas de Valuación', icon: 'event_note', route: '/admin/valuator/appointment' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {}

  ngOnInit(): void { this.loadMetrics(); }

  ngAfterViewInit(): void { setTimeout(() => this.initCharts(), 300); }

  ngOnDestroy(): void {
    this.chartInstances.forEach(c => c.dispose());
    window.removeEventListener('resize', this.onResize);
  }

  private loadMetrics(): void {
    this.dashboardService.getMetrics().subscribe({
      next: (res: any) => {
        const data = res?.data;
        if (data?.stats) {
          const s = data.stats;
          const keys = ['pending', 'in_progress', 'completed', 'total'];
          keys.forEach((key, i) => {
            this.stats[i].value = s[key] ?? 0;
            this.stats[i].loading = false;
          });
        }
        if (data?.charts) { this.chartsData = data.charts; this.initCharts(); }
        this.loading = false;
      },
      error: () => {
        this.error = true; this.loading = false;
        this.stats.forEach(s => { s.value = 'Error'; s.loading = false; });
      },
    });
  }

  private initCharts(): void {
    this.loadValuationStatusChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadValuationStatusChart(): void {
    if (!this.valuationStatusChartEl) return;
    const chart = echarts.init(this.valuationStatusChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.valuations_by_status || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    const colors: Record<string, string> = { pending: '#f59e0b', in_progress: '#1c69d4', completed: '#059669', cancelled: '#ef4444' };
    chart.setOption({
      tooltip: { trigger: 'item' },
      series: [{
        type: 'pie', radius: '65%', label: { fontSize: 11 },
        data: data.map((d: any) => ({ name: d.status, value: d.count, itemStyle: { color: colors[d.status] || '#94a3b8' } })),
      }],
    });
  }

  navigateTo(route: string): void { this.router.navigateByUrl(route); }
}
