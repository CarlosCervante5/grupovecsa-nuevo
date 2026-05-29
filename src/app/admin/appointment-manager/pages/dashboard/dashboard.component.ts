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

  @ViewChild('appointmentsByMonthChart') appointmentsByMonthChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Citas hoy', value: '—', icon: 'today', color: '#1c69d4', loading: true },
    { label: 'Citas semana', value: '—', icon: 'date_range', color: '#059669', loading: true },
    { label: 'Pendientes asignar', value: '—', icon: 'assignment_late', color: '#f59e0b', loading: true },
    { label: 'Total', value: '—', icon: 'event', color: '#7c3aed', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Asignar Citas', icon: 'assignment', route: '/admin/appointment_manager/assign-valuations' },
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
          ['today', 'this_week', 'unassigned', 'total'].forEach((key, i) => {
            if (this.stats[i]) { this.stats[i].value = s[key] ?? 0; this.stats[i].loading = false; }
          });
        } else { this.stats.forEach(s => { s.value = 0; s.loading = false; }); }
        this.loading = false;
        if (data?.charts) {
          this.chartsData = data.charts;
          setTimeout(() => this.initCharts(), 100);
        }
      },
      error: () => { this.error = true; this.loading = false; this.stats.forEach(s => { s.value = 'Error'; s.loading = false; }); },
    });
  }

  private initCharts(): void { this.loadChart(); window.addEventListener('resize', this.onResize); }
  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadChart(): void {
    if (!this.appointmentsByMonthChartEl) return;
    this.chartInstances.forEach(c => c.dispose()); this.chartInstances = [];
    const chart = echarts.init(this.appointmentsByMonthChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.appointments_by_month || [];
    if (!data.length) { chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }); return; }
    chart.setOption({
      tooltip: { trigger: 'axis' }, grid: { left: 40, right: 16, top: 20, bottom: 30 },
      xAxis: { type: 'category', data: data.map((d: any) => d.month), axisLabel: { fontSize: 11 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'bar', itemStyle: { color: '#1c69d4', borderRadius: [4, 4, 0, 0] } }],
    });
  }

  navigateTo(route: string): void { this.router.navigateByUrl(route); }
}
