import { Component, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminBenchmarkUrl, adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

@Component({
  selector: 'app-strega-layout',
  templateUrl: './strega-layout.component.html',
  styleUrls: ['./strega-layout.component.css'],
  standalone: false,
})
export class StregaLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];
  panelTitle = 'Strega';
  panelIcon = 'hub';
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
    this.rebuildNav();
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
    return m ? m[1] : '/admin';
  }

  get isStregaManager(): boolean {
    return (localStorage.getItem('role') || '').trim() === 'strega-manager';
  }

  private rebuildNav(): void {
    const b = this.basePath;
    const items: { label: string; icon: string; route: string }[] = [
      { label: 'Oportunidades', icon: 'list_alt', route: b },
    ];
    if (this.isStregaManager) {
      items.push({ label: 'Citas en espera', icon: 'event_note', route: `${b}/citas` });
    }
    this.navItems = items;
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
