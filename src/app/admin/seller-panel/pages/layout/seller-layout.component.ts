import { Component, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminBenchmarkUrl, adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

@Component({
  selector: 'app-seller-layout',
  templateUrl: './seller-layout.component.html',
  styleUrls: ['./seller-layout.component.css'],
  standalone: false,
})
export class SellerLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];
  panelTitle = 'Vendedor';
  panelIcon = 'sell';
  navItems: { label: string; icon: string; route: string }[] = [];
  dynamicItems: { label: string; icon: string; route: string }[] = [];
  private permSub?: Subscription;

  constructor(
    private router: Router,
    private route: ActivatedRoute,
    private auth: AuthService,
  ) {
    this.loadSession();
    this.rebuildDynamicItems();
  }

  ngOnInit(): void {
    const merged: Record<string, unknown> = {};
    for (const snap of this.route.snapshot.pathFromRoot) {
      Object.assign(merged, snap.data);
    }
    if (merged['panelTitle']) {
      this.panelTitle = merged['panelTitle'] as string;
    }
    if (merged['panelIcon']) {
      this.panelIcon = merged['panelIcon'] as string;
    }
    this.navItems = [{ label: 'Dashboard y citas', icon: 'dashboard', route: this.basePath }];
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

  get basePath(): string {
    const url = this.router.url.split('?')[0];
    const m = url.match(/^(\/admin\/[^/]+)/);
    return m ? m[1] : '/admin/seller';
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
    if (this.permissions.includes('access valuator')) {
      this.dynamicItems.push({ label: 'Panel valuador', icon: 'open_in_new', route: '/admin/valuator' });
    }
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
    this.auth.signOut(this.router);
  }
}
