import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';

@Component({
  selector: 'app-gerente-layout',
  templateUrl: './gerente-layout.component.html',
  styleUrls: ['./gerente-layout.component.css'],
  standalone: false,
})
export class GerenteLayoutComponent implements OnInit, OnDestroy {
  user: any = null;
  role = '';
  name = '';
  permissions: string[] = [];

  readonly navItems = [{ label: 'Dashboard', icon: 'dashboard', route: '/admin/gerente' }];

  panelItems: { label: string; icon: string; route: string }[] = [];
  dynamicItems: { label: string; icon: string; route: string }[] = [];

  private permSub?: Subscription;

  constructor(
    private router: Router,
    private auth: AuthService,
  ) {
    this.loadSession();
    this.rebuildNavLists();
  }

  ngOnInit(): void {
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
      { perm: 'access bodywork_paint_technician', label: 'Hojalatería', icon: 'build', route: '/admin/bodywork_paint_technician' },
      { perm: 'access spare_parts', label: 'Refacciones', icon: 'settings', route: '/admin/spare_parts' },
    ];

    for (const link of panelLinks) {
      if (this.permissions.includes(link.perm)) {
        this.panelItems.push({ label: link.label, icon: link.icon, route: link.route });
      }
    }

    const toolLinks: { perm: string; label: string; icon: string; route: string }[] = [
      { perm: 'access store_management', label: 'Tienda', icon: 'storefront', route: '/admin/store' },
      { perm: 'access benchmark', label: 'Benchmark ADS', icon: 'analytics', route: '/admin/benchmark' },
      { perm: 'access marketing', label: 'Marketing', icon: 'campaign', route: '/admin/marketing' },
    ];

    for (const link of toolLinks) {
      if (this.permissions.includes(link.perm)) {
        this.dynamicItems.push({ label: link.label, icon: link.icon, route: link.route });
      }
    }
  }

  logout(): void {
    localStorage.clear();
    this.router.navigateByUrl('/auth/login');
  }
}
