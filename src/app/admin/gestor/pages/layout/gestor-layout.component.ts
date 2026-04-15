import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

@Component({
  selector: 'app-gestor-layout',
  templateUrl: './gestor-layout.component.html',
  styleUrls: ['./gestor-layout.component.css'],
  standalone: false,
})
export class GestorLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  readonly navItems = [
    { label: 'Dashboard', icon: 'dashboard', route: '/admin/gestor' },
    { label: 'Promociones', icon: 'campaign', route: '/admin/gestor/promotions' },
    { label: 'Eventos', icon: 'event', route: '/admin/gestor/scheduled-events' },
    { label: 'Recompensas', icon: 'emoji_events', route: '/admin/gestor/rewards' },
  ];

  dynamicItems: { label: string; icon: string; route: string }[] = [];

  private permSub?: Subscription;

  constructor(
    private router: Router,
    private auth: AuthService,
  ) {
    this.loadSession();
    this.rebuildDynamicItems();
  }

  ngOnInit(): void {
    this.permSub = this.auth.permissionsRevision$.subscribe(() => {
      try {
        this.permissions = JSON.parse(localStorage.getItem('permissions') || '[]');
      } catch {
        this.permissions = [];
      }
      this.rebuildDynamicItems();
    });
  }

  ngOnDestroy(): void {
    this.permSub?.unsubscribe();
  }

  private loadSession(): void {
    try {
      this.user = JSON.parse(localStorage.getItem('user')!);
    } catch {}
    try {
      this.permissions = JSON.parse(localStorage.getItem('permissions') || '[]');
    } catch {}
    this.role = localStorage.getItem('role') || '';
    const profile = JSON.parse(localStorage.getItem('profile') || '{}');
    this.name = profile?.name || this.user?.nickname || 'Usuario';
  }

  private rebuildDynamicItems(): void {
    this.dynamicItems = [];
    if (this.permissions.includes('access store_management')) {
      this.dynamicItems.push({ label: 'Tienda', icon: 'storefront', route: '/admin/store' });
    }
    if (this.permissions.includes('access benchmark')) {
      this.dynamicItems.push({ label: 'Benchmark ADS', icon: 'monitoring', route: '/admin/benchmark' });
    }
  }

  get panelHomeUrl(): string {
    return adminDashboardUrl(this.role);
  }

  logout(): void {
    localStorage.clear();
    this.router.navigateByUrl('/auth/login');
  }
}
