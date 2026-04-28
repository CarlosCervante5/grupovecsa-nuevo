import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { GerenteDashboardService } from '../../services/gerente-dashboard.service';
import { adminBenchmarkUrl } from 'src/app/admin/utils/admin-route.util';
import * as echarts from 'echarts';

@Component({
  selector: 'app-gerente-dashboard',
  templateUrl: './gerente-dashboard.component.html',
  styleUrls: ['./gerente-dashboard.component.css'],
  standalone: false,
})
export class GerenteDashboardComponent implements OnInit, AfterViewInit, OnDestroy {
  loading = true;
  error = false;

  @ViewChild('ordersBarChart') ordersBarChartEl!: ElementRef;
  @ViewChild('ordersDoughnutChart') ordersDoughnutChartEl!: ElementRef;
  @ViewChild('topProductsChart') topProductsChartEl!: ElementRef;
  @ViewChild('valuationsDealershipChart') valuationsDealershipChartEl!: ElementRef;
  @ViewChild('appointmentsDealershipChart') appointmentsDealershipChartEl!: ElementRef;
  @ViewChild('appointmentsLineChart') appointmentsLineChartEl!: ElementRef;

  private chartInstances: echarts.ECharts[] = [];
  private chartsData: any = {};

  // Row 1 - General
  generalStats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Vehículos', value: '—', icon: 'directions_car', color: '#1c69d4', loading: true },
    { label: 'Productos', value: '—', icon: 'inventory_2', color: '#7c3aed', loading: true },
    { label: 'Pedidos', value: '—', icon: 'shopping_cart', color: '#059669', loading: true },
    { label: 'Usuarios', value: '—', icon: 'people', color: '#0891b2', loading: true },
    { label: 'Clientes', value: '—', icon: 'person', color: '#d97706', loading: true },
  ];

  // Row 2 - General continued
  generalStats2: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Sucursales', value: '—', icon: 'business', color: '#dc2626', loading: true },
    { label: 'Valuaciones', value: '—', icon: 'price_check', color: '#4f46e5', loading: true },
    { label: 'Citas', value: '—', icon: 'event', color: '#0d9488', loading: true },
  ];

  // Row 3 - Boutique
  boutiqueStats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Total Ventas', value: '—', icon: 'attach_money', color: '#059669', loading: true },
    { label: 'Pedidos Pendientes', value: '—', icon: 'pending_actions', color: '#d97706', loading: true },
  ];

  // Row 4 - Activity
  activityStats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Citas Hoy', value: '—', icon: 'today', color: '#1c69d4', loading: true },
    { label: 'Citas Semana', value: '—', icon: 'date_range', color: '#7c3aed', loading: true },
    { label: 'Valuaciones Pendientes', value: '—', icon: 'hourglass_empty', color: '#d97706', loading: true },
    { label: 'Valuaciones en Progreso', value: '—', icon: 'autorenew', color: '#059669', loading: true },
  ];

  // Row 5 - Benchmark
  benchmarkStats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Competidores', value: '—', icon: 'groups', color: '#dc2626', loading: true },
    { label: 'Escaneos', value: '—', icon: 'radar', color: '#4f46e5', loading: true },
  ];

  quickLinks: { label: string; icon: string; route: string }[] = [
    { label: 'Gestor', icon: 'manage_accounts', route: '/admin/gestor' },
    { label: 'Recepción', icon: 'assignment_ind', route: '/admin/receptionist' },
    { label: 'Valuador', icon: 'price_check', route: '/admin/valuator' },
    { label: 'Citas', icon: 'event', route: '/admin/appointment_manager' },
    { label: 'Staff', icon: 'badge', route: '/admin/staff' },
    { label: 'Refacciones', icon: 'settings', route: '/admin/spare_parts' },
    { label: 'Vendedor', icon: 'sell', route: '/admin/seller' },
    { label: 'Strega vendedor', icon: 'storefront', route: '/admin/strega-seller' },
    {
      label: 'Benchmark ADS',
      icon: 'analytics',
      route: adminBenchmarkUrl(localStorage.getItem('role')) ?? '/admin/benchmark',
    },
  ];

  constructor(private router: Router, private dashboardService: GerenteDashboardService) {}

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

  retryLoad(): void {
    this.error = false;
    this.loading = true;
    this.setAllLoading(true);
    this.loadMetrics();
  }

  private setAllLoading(val: boolean): void {
    var allStats = [].concat(this.generalStats as any, this.generalStats2 as any, this.boutiqueStats as any, this.activityStats as any, this.benchmarkStats as any);
    allStats.forEach((s: any) => { s.loading = val; if (val) s.value = '—'; });
  }

  private loadMetrics(): void {
    this.dashboardService.getMetrics().subscribe({
      next: (res: any) => {
        const data = res?.data;
        if (data?.stats) {
          const s = data.stats;
          // Row 1 - General
          this.generalStats[0].value = s.vehicles ?? 0;
          this.generalStats[1].value = s.products ?? 0;
          this.generalStats[2].value = s.orders ?? 0;
          this.generalStats[3].value = s.users ?? 0;
          this.generalStats[4].value = s.customers ?? 0;
          this.generalStats.forEach(st => st.loading = false);

          // Row 2 - General continued
          this.generalStats2[0].value = s.dealerships ?? 0;
          this.generalStats2[1].value = s.valuations ?? 0;
          this.generalStats2[2].value = s.appointments ?? 0;
          this.generalStats2.forEach(st => st.loading = false);

          // Row 3 - Boutique
          const totalSales = s.total_sales ?? 0;
          this.boutiqueStats[0].value = '$' + Number(totalSales).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          this.boutiqueStats[1].value = s.pending_orders ?? 0;
          this.boutiqueStats.forEach(st => st.loading = false);

          // Row 4 - Activity
          this.activityStats[0].value = s.appointments_today ?? 0;
          this.activityStats[1].value = s.appointments_week ?? 0;
          this.activityStats[2].value = s.valuations_pending ?? 0;
          this.activityStats[3].value = s.valuations_in_progress ?? 0;
          this.activityStats.forEach(st => st.loading = false);

          // Row 5 - Benchmark
          this.benchmarkStats[0].value = s.benchmark_competitors ?? 0;
          this.benchmarkStats[1].value = s.benchmark_scans ?? 0;
          this.benchmarkStats.forEach(st => st.loading = false);
        } else {
          this.setAllLoading(false);
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
        var allStats = [].concat(this.generalStats as any, this.generalStats2 as any, this.boutiqueStats as any, this.activityStats as any, this.benchmarkStats as any);
        allStats.forEach((s: any) => { s.value = 'Error'; s.loading = false; });
      },
    });
  }

  private initCharts(): void {
    this.chartInstances.forEach(c => c.dispose());
    this.chartInstances = [];
    this.loadOrdersBarChart();
    this.loadOrdersDoughnutChart();
    this.loadTopProductsChart();
    this.loadValuationsDealershipChart();
    this.loadAppointmentsDealershipChart();
    this.loadAppointmentsLineChart();
    window.addEventListener('resize', this.onResize);
  }

  private onResize = () => this.chartInstances.forEach(c => c.resize());

  private initChart(el: ElementRef | undefined): echarts.ECharts | null {
    if (!el) return null;
    const chart = echarts.init(el.nativeElement);
    this.chartInstances.push(chart);
    return chart;
  }

  private showNoData(chart: echarts.ECharts): void {
    chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } });
  }

  private loadOrdersBarChart(): void {
    const chart = this.initChart(this.ordersBarChartEl);
    if (!chart) return;
    const data = this.chartsData?.orders_by_month || [];
    if (!data.length) { this.showNoData(chart); return; }
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 16, top: 20, bottom: 30 },
      xAxis: { type: 'category', data: data.map((d: any) => d.month), axisLabel: { fontSize: 11 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'bar', itemStyle: { color: '#1c69d4', borderRadius: [4, 4, 0, 0] } }],
    });
  }

  private loadOrdersDoughnutChart(): void {
    const chart = this.initChart(this.ordersDoughnutChartEl);
    if (!chart) return;
    const data = this.chartsData?.orders_by_status || [];
    if (!data.length) { this.showNoData(chart); return; }
    chart.setOption({
      tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
      legend: { bottom: 0, left: 'center', textStyle: { fontSize: 11 } },
      series: [{
        type: 'pie', radius: ['40%', '70%'], center: ['50%', '45%'],
        label: { show: false },
        data: data.map((d: any) => ({ name: d.status, value: d.count })),
      }],
    });
  }

  private loadTopProductsChart(): void {
    const chart = this.initChart(this.topProductsChartEl);
    if (!chart) return;
    const data = this.chartsData?.top_products || [];
    if (!data.length) { this.showNoData(chart); return; }
    const reversed = data.slice().reverse();
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 120, right: 24, top: 10, bottom: 20 },
      xAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      yAxis: { type: 'category', data: reversed.map((d: any) => d.name), axisLabel: { fontSize: 11, width: 100, overflow: 'truncate' } },
      series: [{ data: reversed.map((d: any) => d.quantity), type: 'bar', itemStyle: { color: '#7c3aed', borderRadius: [0, 4, 4, 0] } }],
    });
  }

  private loadValuationsDealershipChart(): void {
    const chart = this.initChart(this.valuationsDealershipChartEl);
    if (!chart) return;
    const data = this.chartsData?.valuations_by_dealership || [];
    if (!data.length) { this.showNoData(chart); return; }
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 16, top: 20, bottom: 60 },
      xAxis: { type: 'category', data: data.map((d: any) => d.name), axisLabel: { fontSize: 10, rotate: 30 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'bar', itemStyle: { color: '#4f46e5', borderRadius: [4, 4, 0, 0] } }],
    });
  }

  private loadAppointmentsDealershipChart(): void {
    const chart = this.initChart(this.appointmentsDealershipChartEl);
    if (!chart) return;
    const data = this.chartsData?.appointments_by_dealership || [];
    if (!data.length) { this.showNoData(chart); return; }
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 16, top: 20, bottom: 60 },
      xAxis: { type: 'category', data: data.map((d: any) => d.name), axisLabel: { fontSize: 10, rotate: 30 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'bar', itemStyle: { color: '#0d9488', borderRadius: [4, 4, 0, 0] } }],
    });
  }

  private loadAppointmentsLineChart(): void {
    const chart = this.initChart(this.appointmentsLineChartEl);
    if (!chart) return;
    const data = this.chartsData?.appointments_by_month || [];
    if (!data.length) { this.showNoData(chart); return; }
    chart.setOption({
      tooltip: { trigger: 'axis' },
      grid: { left: 40, right: 16, top: 20, bottom: 30 },
      xAxis: { type: 'category', data: data.map((d: any) => d.month), axisLabel: { fontSize: 11 } },
      yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
      series: [{ data: data.map((d: any) => d.count), type: 'line', smooth: true, itemStyle: { color: '#059669' }, areaStyle: { color: 'rgba(5,150,105,0.1)' } }],
    });
  }

  navigateTo(route: string): void {
    this.router.navigateByUrl(route);
  }
}
