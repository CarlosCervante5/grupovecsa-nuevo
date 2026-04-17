import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';
import { expandLegacyGestorPermissions, GESTOR_FEATURE_PERMISSIONS } from 'src/app/admin/utils/gestor-feature-permissions';

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

  navItems: { label: string; icon: string; route: string }[] = [];

  dynamicItems: { label: string; icon: string; route: string }[] = [];

  private permSub?: Subscription;

  constructor(
    private router: Router,
    private auth: AuthService,
  ) {
    this.loadSession();
    this.rebuildSidebar();
  }

  ngOnInit(): void {
    this.permSub = this.auth.permissionsRevision$.subscribe(() => {
      try {
        this.permissions = JSON.parse(localStorage.getItem('permissions') || '[]');
      } catch {
        this.permissions = [];
      }
      try {
        const profile = JSON.parse(localStorage.getItem('profile') || '{}');
        this.name = profile?.name || this.user?.nickname || 'Usuario';
      } catch {
        this.name = this.user?.nickname || 'Usuario';
      }
      this.rebuildSidebar();
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

  private rebuildSidebar(): void {
    const base = adminDashboardUrl(this.role);
    this.navItems = [{ label: 'Dashboard', icon: 'dashboard', route: base }];

    const effective = expandLegacyGestorPermissions(this.permissions, this.role);

    const main: { perm: string; label: string; icon: string; path: string }[] = [
      { perm: GESTOR_FEATURE_PERMISSIONS.promotions, label: 'Promociones', icon: 'campaign', path: 'promotions' },
      { perm: GESTOR_FEATURE_PERMISSIONS.scheduledEvents, label: 'Experience', icon: 'auto_stories', path: 'scheduled-events' },
      { perm: GESTOR_FEATURE_PERMISSIONS.rewards, label: 'Recompensas', icon: 'emoji_events', path: 'rewards' },
    ];
    for (const item of main) {
      if (effective.includes(item.perm)) {
        this.navItems.push({ label: item.label, icon: item.icon, route: `${base}/${item.path}` });
      }
    }

    this.dynamicItems = [];
    if (this.permissions.includes('access store_management')) {
      this.dynamicItems.push({ label: 'Tienda', icon: 'storefront', route: '/admin/store' });
    }
    if (this.permissions.includes('access benchmark')) {
      this.dynamicItems.push({ label: 'Benchmark ADS', icon: 'monitoring', route: '/admin/benchmark' });
    }
  }

  get panelBaseUrl(): string {
    return adminDashboardUrl(this.role);
  }

  /** Etiqueta lateral según URL (/admin/manager vs /admin/gestor). */
  get panelBrandLabel(): string {
    const parts = this.router.url.split('?')[0].split('/').filter(Boolean);
    const seg = parts[0] === 'admin' && parts[1] ? parts[1] : '';
    if (!seg) {
      return 'Panel';
    }
    if (seg === 'manager') {
      return 'Manager';
    }
    if (seg === 'gestor') {
      return 'Gestor';
    }
    return seg.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
  }

  get panelHomeUrl(): string {
    return adminDashboardUrl(this.role);
  }

  logout(): void {
    localStorage.clear();
    this.router.navigateByUrl('/auth/login');
  }
}
