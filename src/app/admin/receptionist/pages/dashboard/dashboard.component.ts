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

  @ViewChild('appointmentTypeChart') appointmentTypeChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Citas hoy', value: '—', icon: 'today', color: '#1c69d4', loading: true },
    { label: 'Citas semana', value: '—', icon: 'date_range', color: '#059669', loading: true },
    { label: 'Total citas', value: '—', icon: 'event', color: '#d97706', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Formulario de Recepción', icon: 'assignment', route: '/admin/receptionist/reception-form' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {}

  ngOnInit(): void {
    this.loadMetrics();
  }

  ngAfterViewInit(): void {
    setTimeout(() => this.initCharts(), 300);
  }

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
          const keys = ['today', 'this_week', 'total'];
          keys.forEach((key, i) => {
            this.stats[i].value = s[key] ?? 0;
            this.stats[i].loading = false;
          });
        }
        if (data?.charts) {
          this.chartsData = data.charts;
          this.initCharts();
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
    this.loadAppointmentTypeChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadAppointmentTypeChart(): void {
    if (!this.appointmentTypeChartEl) return;
    const chart = echarts.init(this.appointmentTypeChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.appointments_by_type || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    const colors: Record<string, string> = { valuacion: '#1c69d4', servicio: '#059669', general: '#d97706', otro: '#7c3aed' };
    chart.setOption({
      tooltip: { trigger: 'item' },
      series: [{
        type: 'pie', radius: '65%', label: { fontSize: 11 },
        data: data.map((d: any) => ({ name: d.type, value: d.count, itemStyle: { color: colors[d.type] || '#94a3b8' } })),
      }],
    });
  }

  navigateTo(route: string): void {
    this.router.navigateByUrl(route);
  }
}
