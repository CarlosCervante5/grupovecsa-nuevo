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
export class DashboardAdminComponent implements OnInit, OnDestroy {
  loading = true;
  error = false;

  @ViewChild('ordersBarChart') ordersBarChartEl!: ElementRef;
  @ViewChild('ordersStatusChart') ordersStatusChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Vehículos', value: '—', icon: 'directions_car', color: '#1c69d4', loading: true },
    { label: 'Productos', value: '—', icon: 'inventory_2', color: '#7c3aed', loading: true },
    { label: 'Pedidos', value: '—', icon: 'receipt_long', color: '#059669', loading: true },
    { label: 'Usuarios', value: '—', icon: 'people', color: '#d97706', loading: true },
    { label: 'Clientes', value: '—', icon: 'person', color: '#dc2626', loading: true },
    { label: 'Sucursales', value: '—', icon: 'store', color: '#0891b2', loading: true },
    { label: 'Valuaciones', value: '—', icon: 'price_check', color: '#4f46e5', loading: true },
    { label: 'Citas', value: '—', icon: 'event', color: '#be185d', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Usuarios', icon: 'people', route: '/admin/administrator/users' },
    { label: 'Permisos', icon: 'vpn_key', route: '/admin/administrator/permissions' },
    { label: 'Boutique', icon: 'storefront', route: '/admin/administrator/boutique' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {}

  ngOnInit(): void {
    this.loadMetrics();
  }

  ngOnDestroy(): void {
    this.chartInstances.forEach(c => c.dispose());
  }

  private loadMetrics(): void {
    this.dashboardService.getMetrics().subscribe({
      next: (res: any) => {
        const data = res?.data;
        if (data?.stats) {
          const s = data.stats;
          const keys = ['vehicles', 'products', 'orders', 'users', 'customers', 'dealerships', 'valuations', 'appointments'];
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
        this.error = true;
        this.loading = false;
        this.stats.forEach(s => { s.value = 'Error'; s.loading = false; });
      },
    });
  }

  private initCharts(): void {
    this.loadOrdersBarChart();
    this.loadOrdersStatusChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private loadOrdersBarChart(): void {
    if (!this.ordersBarChartEl) return;
    const chart = echarts.init(this.ordersBarChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.orders_by_month || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 16, top: 20, bottom: 30 },
      xAxis: { type: 'category', data: data.map((d: any) => d.month), axisLabel: { fontSize: 11 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'bar', itemStyle: { color: '#1c69d4', borderRadius: [4, 4, 0, 0] } }],
    });
  }

  private loadOrdersStatusChart(): void {
    if (!this.ordersStatusChartEl) return;
    const chart = echarts.init(this.ordersStatusChartEl.nativeElement);
    this.chartInstances.push(chart);
    const data = this.chartsData?.orders_by_status || [];
    if (!data.length) {
      chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
      return;
    }
    const colors: Record<string, string> = { pending: '#f59e0b', paid: '#059669', shipped: '#1c69d4', delivered: '#10b981', cancelled: '#ef4444', refunded: '#6b7280' };
    chart.setOption({
      tooltip: { trigger: 'item' },
      series: [{
        type: 'pie', radius: '65%', label: { fontSize: 11 },
        data: data.map((d: any) => ({ name: d.status, value: d.count, itemStyle: { color: colors[d.status] || '#94a3b8' } })),
      }],
    });
  }

  navigateTo(route: string): void {
    this.router.navigateByUrl(route);
  }
}
