import { Injectable } from '@angular/core';
import { Router, CanMatch, Route, UrlSegment } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

/**
 * Inventario de vehículos:
 * - `access vehicle_inventory` o `access marketing`
 * - permisos Spatie de vehículos (p. ej. gestor con `list all vehicles` sin módulos `access_*`)
 * - rol `marketing`: en muchos entornos el rol existe pero el rol no tiene filas `access marketing` en `/me`
 */
@Injectable({ providedIn: 'root' })
export class VehicleInventoryGuard implements CanMatch {
  private static readonly permissionAllowList = [
    'access vehicle_inventory',
    'access marketing',
    'list all vehicles',
    'create vehicles',
    'update vehicles',
    'delete vehicles',
  ];

  private static readonly roleAllowList = [
    'developer',
    'administrator',
    'gerente',
    'marketing',
  ];

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
    if (VehicleInventoryGuard.permissionAllowList.some((p) => perms.includes(p))) {
      return true;
    }
    const role = (localStorage.getItem('role') || '').trim().toLowerCase();
    if (VehicleInventoryGuard.roleAllowList.includes(role)) {
      return true;
    }
    const home = role ? adminDashboardUrl(role) : '/';
    this.router.navigateByUrl(home);
    return false;
  }
}
