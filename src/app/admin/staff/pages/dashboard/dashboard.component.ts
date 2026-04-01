import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AdminDashboardService } from '../../../shared/services/admin-dashboard.service';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  standalone: false,
})
export class DashboardComponent implements OnInit {
  loading = true;
  error = false;

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Clientes', value: '—', icon: 'people', color: '#1c69d4', loading: true },
    { label: 'Recompensas activas', value: '—', icon: 'emoji_events', color: '#059669', loading: true },
    { label: 'Total puntos', value: '—', icon: 'stars', color: '#d97706', loading: true },
  ];

  readonly quickLinks = [
    { label: 'Registro de KM', icon: 'speed', route: '/admin/staff/riders' },
    { label: 'Registro de Compras', icon: 'receipt_long', route: '/admin/staff/sales' },
  ];

  constructor(private router: Router, private dashboardService: AdminDashboardService) {}

  ngOnInit(): void {
    this.loadMetrics();
  }

  private loadMetrics(): void {
    this.dashboardService.getMetrics().subscribe({
      next: (res: any) => {
        const data = res?.data;
        if (data?.stats) {
          const s = data.stats;
          const keys = ['customers', 'active_rewards', 'total_points'];
          keys.forEach((key, i) => {
            this.stats[i].value = s[key] ?? 0;
            this.stats[i].loading = false;
          });
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

  navigateTo(route: string): void {
    this.router.navigateByUrl(route);
  }
}
