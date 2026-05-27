import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import {
  adminBenchmarkUrl,
  adminDashboardUrl,
  adminVehicleInventoryUrl,
} from 'src/app/admin/utils/admin-route.util';

@Component({
  selector: 'app-marketing-layout',
  templateUrl: './marketing-layout.component.html',
  styleUrls: ['./marketing-layout.component.css'],
  standalone: false,
})
export class MarketingLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  readonly navItems = [
    { label: 'Dashboard', icon: 'dashboard', route: '/admin/marketing' },
    { label: 'Home Slides', icon: 'image', route: '/admin/marketing/home-slides' },
    { label: 'Testimonios', icon: 'format_quote', route: '/admin/marketing/home-testimonials' },
    { label: 'Banners Boutique', icon: 'view_carousel', route: '/admin/marketing/boutique-banners' },
    { label: 'Experience — Historias', icon: 'auto_stories', route: '/admin/marketing/experience-stories' },
    { label: 'Chats asistente', icon: 'forum', route: '/admin/marketing/assistant-chats' },
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
      this.dynamicItems.push({
        label: 'Benchmark ADS',
        icon: 'monitoring',
        route: adminBenchmarkUrl(this.role) ?? '/admin/benchmark',
      });
    }
    if (
      this.permissions.includes('access vehicle_inventory') ||
      this.permissions.includes('access marketing')
    ) {
      this.dynamicItems.push({
        label: 'Inventario de vehículos',
        icon: 'inventory_2',
        route: adminVehicleInventoryUrl(this.role) ?? '/admin/vehicle-inventory',
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
