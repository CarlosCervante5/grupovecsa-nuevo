import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-marketing-layout',
  templateUrl: './marketing-layout.component.html',
  styleUrls: ['./marketing-layout.component.css'],
  standalone: false,
})
export class MarketingLayoutComponent {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  readonly navItems = [
    { label: 'Dashboard', icon: 'dashboard', route: '/admin/marketing' },
    { label: 'Vehículos', icon: 'directions_car', route: '/admin/marketing/vehicles' },
    { label: 'Home Slides', icon: 'image', route: '/admin/marketing/home-slides' },
    { label: 'Testimonios', icon: 'format_quote', route: '/admin/marketing/home-testimonials' },
    { label: 'Banners Boutique', icon: 'view_carousel', route: '/admin/marketing/boutique-banners' },
  ];

  dynamicItems: { label: string; icon: string; route: string }[] = [];

  constructor(private router: Router) {
    try { this.user = JSON.parse(localStorage.getItem('user')!); } catch {}
    try { this.permissions = JSON.parse(localStorage.getItem('permissions') || '[]'); } catch {}
    this.role = localStorage.getItem('role') || '';
    const profile = JSON.parse(localStorage.getItem('profile') || '{}');
    this.name = profile?.name || this.user?.nickname || 'Usuario';

    if (this.permissions.includes('access store_management')) {
      this.dynamicItems.push({ label: 'Tienda', icon: 'storefront', route: '/admin/store' });
    }
    if (this.permissions.includes('access benchmark')) {
      this.dynamicItems.push({ label: 'Benchmark ADS', icon: 'monitoring', route: '/admin/benchmark' });
    }
  }

  logout(): void { localStorage.clear(); this.router.navigateByUrl('/auth/login'); }
}
