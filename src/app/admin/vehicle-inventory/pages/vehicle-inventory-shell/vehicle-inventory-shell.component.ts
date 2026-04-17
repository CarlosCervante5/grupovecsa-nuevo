import { Component, OnDestroy, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

/**
 * Marco visual del inventario: la ruta vive fuera de marketing/gestor/administrator,
 * sin esto el usuario pierde el panel lateral y parece un “rebote” o pantalla huérfana.
 */
@Component({
  selector: 'app-vehicle-inventory-shell',
  templateUrl: './vehicle-inventory-shell.component.html',
  styleUrls: ['../../../gestor/pages/layout/gestor-layout.component.css'],
  standalone: false,
})
export class VehicleInventoryShellComponent implements OnInit, OnDestroy {
  user: unknown = null;
  role = '';
  name = '';
  private permSub?: Subscription;

  constructor(
    private router: Router,
    private auth: AuthService,
  ) {
    this.loadSession();
  }

  ngOnInit(): void {
    this.permSub = this.auth.permissionsRevision$.subscribe(() => {
      try {
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
      } catch {
        this.user = null;
      }
      this.role = localStorage.getItem('role') || '';
      try {
        const profile = JSON.parse(localStorage.getItem('profile') || '{}');
        const u = this.user as { nickname?: string } | null;
        this.name = profile?.name || u?.nickname || 'Usuario';
      } catch {
        this.name = 'Usuario';
      }
    });
  }

  ngOnDestroy(): void {
    this.permSub?.unsubscribe();
  }

  private loadSession(): void {
    try {
      this.user = JSON.parse(localStorage.getItem('user') || 'null');
    } catch {
      this.user = null;
    }
    this.role = localStorage.getItem('role') || '';
    try {
      const profile = JSON.parse(localStorage.getItem('profile') || '{}');
      const u = this.user as { nickname?: string } | null;
      this.name = profile?.name || u?.nickname || 'Usuario';
    } catch {
      this.name = 'Usuario';
    }
  }

  get panelHomeUrl(): string {
    return adminDashboardUrl(this.role);
  }

  logout(): void {
    localStorage.clear();
    void this.router.navigateByUrl('/auth/login');
  }
}
