import { Injectable } from '@angular/core';
import { Router, CanMatch, Route, UrlSegment } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

/**
 * Inventario de vehículos: permiso dedicado `access vehicle_inventory`
 * o compatibilidad con `access marketing` (misma pantalla que antes en /admin/marketing/vehicles).
 */
@Injectable({ providedIn: 'root' })
export class VehicleInventoryGuard implements CanMatch {
  constructor(
    private router: Router,
    private auth: AuthService,
  ) {}

  canMatch(_route: Route, _segments: UrlSegment[]): Observable<boolean> {
    return this.auth.refreshPermissionsForGuard().pipe(
      map((perms) => this.evaluate(perms)),
    );
  }

  private evaluate(perms: string[]): boolean {
    if (!localStorage.getItem('user_token')) {
      this.router.navigateByUrl('/auth/login');
      return false;
    }
    if (perms.includes('access vehicle_inventory') || perms.includes('access marketing')) {
      return true;
    }
    const role = (localStorage.getItem('role') || '').trim().toLowerCase();
    if (role === 'developer' || role === 'administrator' || role === 'gerente') {
      return true;
    }
    const home = role ? adminDashboardUrl(role) : '/';
    this.router.navigateByUrl(home);
    return false;
  }
}
