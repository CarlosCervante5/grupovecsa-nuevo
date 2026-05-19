import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import {
  adminBenchmarkUrl,
  adminBodyworkPanelBaseFromRouterUrl,
  adminDashboardUrl,
} from 'src/app/admin/utils/admin-route.util';

@Component({
  selector: 'app-bodywork-layout',
  templateUrl: './bodywork-layout.component.html',
  styleUrls: ['./bodywork-layout.component.css'],
  standalone: false,
})
export class BodyworkLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  get panelBase(): string {
    return adminBodyworkPanelBaseFromRouterUrl(this.router.url);
  }

  get navItems(): { label: string; icon: string; route: string }[] {
    const b = this.panelBase;
    return [
      { label: 'Dashboard', icon: 'dashboard', route: b },
      { label: 'Hojalatería y Pintura', icon: 'build', route: `${b}/bodywork-paint` },
    ];
  }

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
      this.dynamicItems.push({
        label: 'Benchmark ADS',
        icon: 'monitoring',
        route: adminBenchmarkUrl(this.role) ?? '/admin/benchmark',
      });
    }
  }

  get panelHomeUrl(): string {
    return adminDashboardUrl(this.role);
  }

  logout(): void {
    localStorage.clear();
    this.router.navigateByUrl('/auth/iniciar-sesion');
  }
}
