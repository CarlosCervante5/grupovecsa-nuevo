import { Injectable } from '@angular/core';
import { Router, CanActivate, CanMatch, Route, UrlSegment } from '@angular/router';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

/**
 * Inventario de vehículos:
 * - `access vehicle_inventory` o `access marketing`
 * - permisos Spatie de vehículos (list/create/update/delete)
 * - roles que usan el panel y el inventario en la práctica: marketing, gestor, manager (+ admin/dev/gerente)
 * Si GET /me falla, se reevalúa con permisos ya guardados en localStorage.
 */
@Injectable({ providedIn: 'root' })
export class VehicleInventoryGuard implements CanMatch, CanActivate {
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
    'admin',
    'gerente',
    'marketing',
    'gestor',
    'manager',
  ];

  constructor(
    private router: Router,
    private auth: AuthService,
  ) {}

  canMatch(_route: Route, _segments: UrlSegment[]): Observable<boolean> {
    return this.check$();
  }

  canActivate(): Observable<boolean> {
    return this.check$();
  }

  private check$(): Observable<boolean> {
    return this.auth.refreshPermissionsForGuard().pipe(
      map((perms) => this.evaluate(perms)),
      catchError(() => {
        let cached: string[] = [];
        try {
          cached = JSON.parse(localStorage.getItem('permissions') || '[]');
        } catch {
          cached = [];
        }
        return of(this.evaluate(cached));
      }),
    );
  }

  private evaluate(perms: string[]): boolean {
    if (!localStorage.getItem('user_token')) {
      this.router.navigateByUrl('/auth/iniciar-sesion');
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
