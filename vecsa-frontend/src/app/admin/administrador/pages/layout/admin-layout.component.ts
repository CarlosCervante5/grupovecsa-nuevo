import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import {
  adminBenchmarkUrl,
  adminDashboardUrl,
  adminVehicleInventoryUrl,
} from 'src/app/admin/utils/admin-route.util';
import { AssistantChatNotificationsService } from '@services/assistant-chat-notifications.service';

@Component({
  selector: 'app-admin-layout',
  templateUrl: './admin-layout.component.html',
  styleUrls: ['./admin-layout.component.css'],
  standalone: false,
})
export class AdminLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  readonly navItems = [
    { label: 'Dashboard', icon: 'dashboard', route: '/admin/administrator' },
    { label: 'Sucursales', icon: 'store', route: '/admin/administrator/sucursales' },
    { label: 'Usuarios', icon: 'people', route: '/admin/administrator/users' },
    { label: 'Permisos', icon: 'vpn_key', route: '/admin/administrator/permissions' },
    { label: 'Legales', icon: 'policy', route: '/admin/administrator/legales' },
    { label: 'Manuales', icon: 'menu_book', route: '/admin/administrator/manuales' },
    {
      label: 'Experience (WordPress)',
      icon: 'cloud_download',
      route: '/admin/administrator/experience-wordpress',
    },
    {
      label: 'Chats asistente',
      icon: 'forum',
      route: '/admin/administrator/assistant-chats',
      notifyKey: 'assistant' as const,
    },
  ];

  dynamicItems: { label: string; icon: string; route: string }[] = [];
  panelItems: { label: string; icon: string; route: string }[] = [];
  assistantUnread = 0;

  private permSub?: Subscription;
  private unreadSub?: Subscription;

  constructor(
    private router: Router,
    private auth: AuthService,
    private assistantNotifications: AssistantChatNotificationsService,
  ) {
    this.loadSession();
    this.rebuildNavLists();
  }

  ngOnInit(): void {
    this.assistantNotifications.start();
    this.unreadSub = this.assistantNotifications.unreadTotal$.subscribe((n) => {
      this.assistantUnread = n;
    });
    this.permSub = this.auth.permissionsRevision$.subscribe(() => {
      try {
        this.permissions = JSON.parse(localStorage.getItem('permissions') || '[]');
      } catch {
        this.permissions = [];
      }
      this.rebuildNavLists();
    });
  }

  ngOnDestroy(): void {
    this.permSub?.unsubscribe();
    this.unreadSub?.unsubscribe();
    this.assistantNotifications.stop();
  }

  navBadgeCount(item: { notifyKey?: string }): number {
    return item.notifyKey === 'assistant' ? this.assistantUnread : 0;
  }

  formatBadgeCount(count: number): string {
    return count > 99 ? '99+' : String(count);
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

  private rebuildNavLists(): void {
    this.panelItems = [];
    this.dynamicItems = [];

    const panelLinks: { perm: string; label: string; icon: string; route: string }[] = [
      { perm: 'access gestor', label: 'Gestor', icon: 'manage_accounts', route: '/admin/gestor' },
      { perm: 'access receptionist', label: 'Recepción', icon: 'assignment_ind', route: '/admin/receptionist' },
      { perm: 'access valuator', label: 'Valuador', icon: 'price_check', route: '/admin/valuator' },
      { perm: 'access appointment_manager', label: 'Citas', icon: 'event', route: '/admin/appointment_manager' },
      { perm: 'access staff', label: 'Staff', icon: 'badge', route: '/admin/staff' },
      { perm: 'access spare_parts', label: 'Refacciones', icon: 'settings', route: '/admin/spare_parts' },
      { perm: 'access seller', label: 'Vendedor', icon: 'sell', route: '/admin/seller' },
    ];

    const roleNorm = (this.role || '').trim().toLowerCase();
    /** `admin` es alias legacy del rol Spatie `administrator` (ver admin-route.util). */
    const fullAccess =
      roleNorm === 'administrator' || roleNorm === 'admin' || roleNorm === 'developer';

    for (const link of panelLinks) {
      if (fullAccess || this.permissions.includes(link.perm)) {
        this.panelItems.push({ label: link.label, icon: link.icon, route: link.route });
      }
    }

    const toolLinks: {
      perm: string;
      label: string;
      icon: string;
      route: string;
      extraPerms?: string[];
    }[] = [
      { perm: 'access store_management', label: 'Tienda', icon: 'storefront', route: '/admin/store' },
      {
        perm: 'access benchmark',
        label: 'Benchmark ADS',
        icon: 'analytics',
        route: adminBenchmarkUrl(this.role) ?? '/admin/benchmark',
      },
      { perm: 'access marketing', label: 'Marketing', icon: 'campaign', route: '/admin/marketing' },
      {
        perm: 'access vehicle_inventory',
        extraPerms: ['access marketing'],
        label: 'Inventario de vehículos',
        icon: 'inventory_2',
        route: adminVehicleInventoryUrl(this.role) ?? '/admin/vehicle-inventory',
      },
    ];

    for (const link of toolLinks) {
      const allow =
        fullAccess ||
        this.permissions.includes(link.perm) ||
        (link.extraPerms?.some((p) => this.permissions.includes(p)) ?? false);
      if (allow) {
        this.dynamicItems.push({ label: link.label, icon: link.icon, route: link.route });
      }
    }

    if (roleNorm === 'administrator' || roleNorm === 'admin') {
      this.dynamicItems.push({
        label: 'Panel Developer',
        icon: 'code',
        route: '/admin/developer',
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
