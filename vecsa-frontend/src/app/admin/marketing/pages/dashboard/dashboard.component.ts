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

  @ViewChild('vehiclesBrandChart') vehiclesBrandChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Campañas activas', value: '—', icon: 'campaign', color: '#1c69d4', loading: true },
    { label: 'Promociones', value: '—', icon: 'local_offer', color: '#7c3aed', loading: true },
    { label: 'Eventos', value: '—', icon: 'event', color: '#059669', loading: true },
    { label: 'Vehículos publicados', value: '—', icon: 'directions_car', color: '#d97706', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Vehículos', icon: 'directions_car', route: '/admin/marketing/vehicles' },
    { label: 'Home Slides', icon: 'image', route: '/admin/marketing/home-slides' },
    { label: 'Testimonios', icon: 'format_quote', route: '/admin/marketing/home-testimonials' },
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
          const keys = ['campaigns', 'promotions', 'events', 'vehicles'];
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
    this.loadVehiclesBrandChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadVehiclesBrandChart(): void {
    if (!this.vehiclesBrandChartEl) return;
    const chart = echarts.init(this.vehiclesBrandChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.vehicles_by_brand || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    const colors = ['#1c69d4', '#7c3aed', '#059669', '#d97706', '#dc2626', '#0891b2', '#4f46e5', '#be185d', '#f59e0b', '#10b981'];
    chart.setOption({
      tooltip: { trigger: 'item' },
      series: [{
        type: 'pie', radius: '65%', label: { fontSize: 11 },
        data: data.map((d: any, i: number) => ({ name: d.name, value: d.count, itemStyle: { color: colors[i % colors.length] } })),
      }],
    });
  }

  navigateTo(route: string): void {
    this.router.navigateByUrl(route);
  }
}
