import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-admin-layout',
  templateUrl: './admin-layout.component.html',
  styleUrls: ['./admin-layout.component.css'],
  standalone: false,
})
export class AdminLayoutComponent {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  readonly navItems = [
    { label: 'Dashboard', icon: 'dashboard', route: '/admin/administrator' },
    { label: 'Usuarios', icon: 'people', route: '/admin/administrator/users' },
    { label: 'Permisos', icon: 'vpn_key', route: '/admin/administrator/permissions' },
    {
      label: 'Experience (WordPress)',
      icon: 'cloud_download',
      route: '/admin/administrator/experience-wordpress',
    },
  ];

  dynamicItems: { label: string; icon: string; route: string }[] = [];
  panelItems: { label: string; icon: string; route: string }[] = [];

  constructor(private router: Router) {
    try { this.user = JSON.parse(localStorage.getItem('user')!); } catch {}
    try { this.permissions = JSON.parse(localStorage.getItem('permissions') || '[]'); } catch {}
    this.role = localStorage.getItem('role') || '';
    const profile = JSON.parse(localStorage.getItem('profile') || '{}');
    this.name = profile?.name || this.user?.nickname || 'Usuario';

    // Paneles de roles
    const panelLinks: { perm: string; label: string; icon: string; route: string }[] = [
      { perm: 'access gestor', label: 'Gestor', icon: 'manage_accounts', route: '/admin/gestor' },
      { perm: 'access receptionist', label: 'Recepción', icon: 'assignment_ind', route: '/admin/receptionist' },
      { perm: 'access valuator', label: 'Valuador', icon: 'price_check', route: '/admin/valuator' },
      { perm: 'access appointment_manager', label: 'Citas', icon: 'event', route: '/admin/appointment_manager' },
      { perm: 'access staff', label: 'Staff', icon: 'badge', route: '/admin/staff' },
      { perm: 'access bodywork_paint_technician', label: 'Hojalatería', icon: 'build', route: '/admin/bodywork_paint_technician' },
      { perm: 'access spare_parts', label: 'Refacciones', icon: 'settings', route: '/admin/spare_parts' },
    ];

    const fullAccess =
      this.role === 'administrator' || this.role === 'developer';

    for (const link of panelLinks) {
      if (fullAccess || this.permissions.includes(link.perm)) {
        this.panelItems.push({ label: link.label, icon: link.icon, route: link.route });
      }
    }

    // Herramientas
    const toolLinks: { perm: string; label: string; icon: string; route: string }[] = [
      { perm: 'access store_management', label: 'Tienda', icon: 'storefront', route: '/admin/store' },
      { perm: 'access benchmark', label: 'Benchmark ADS', icon: 'analytics', route: '/admin/benchmark' },
      { perm: 'access marketing', label: 'Marketing', icon: 'campaign', route: '/admin/marketing' },
    ];

    for (const link of toolLinks) {
      if (fullAccess || this.permissions.includes(link.perm)) {
        this.dynamicItems.push({ label: link.label, icon: link.icon, route: link.route });
      }
    }

    if (this.role === 'administrator') {
      this.dynamicItems.push({
        label: 'Panel Developer',
        icon: 'code',
        route: '/admin/developer',
      });
    }
  }

  logout(): void { localStorage.clear(); this.router.navigateByUrl('/auth/login'); }
}
