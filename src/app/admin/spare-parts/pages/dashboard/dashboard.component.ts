import { Component, OnInit, ElementRef, ViewChild, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { AdminDashboardService } from '../../../shared/services/admin-dashboard.service';
import * as echarts from 'echarts';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  standalone: false,
})
export class DashboardComponent implements OnInit, OnDestroy {
  loading = true;
  error = false;

  @ViewChild('partsStatusChart') partsStatusChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Pendientes', value: '—', icon: 'pending', color: '#f59e0b', loading: true },
    { label: 'En revisión', value: '—', icon: 'search', color: '#1c69d4', loading: true },
    { label: 'Completadas', value: '—', icon: 'check_circle', color: '#059669', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Refacciones', icon: 'settings', route: '/admin/spare_parts/administration' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {}

  ngOnInit(): void { this.loadMetrics(); }

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
          const keys = ['pending', 'pending_review', 'completed'];
          keys.forEach((key, i) => {
            this.stats[i].value = s[key] ?? 0;
            this.stats[i].loading = false;
          });
        }
        this.loading = false;
        if (data?.charts) {
          this.chartsData = data.charts;
          setTimeout(() => this.initCharts(), 100);
        }
      },
      error: () => {
        this.error = true; this.loading = false;
        this.stats.forEach(s => { s.value = 'Error'; s.loading = false; });
      },
    });
  }

  private initCharts(): void {
    this.loadPartsStatusChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadPartsStatusChart(): void {
    if (!this.partsStatusChartEl) return;
    const chart = echarts.init(this.partsStatusChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.parts_by_status || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    const colors: Record<string, string> = { pending: '#f59e0b', pending_review: '#1c69d4', completed: '#059669', parts_done: '#059669', cancelled: '#ef4444' };
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
