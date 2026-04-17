import { Injectable } from '@angular/core';
import { Router, CanMatch, Route, UrlSegment } from '@angular/router';
import { Observable, of } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
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

  canMatch(_route: Route, segments: UrlSegment[]): Observable<boolean> {
    const path = segments.map((s) => s.path).join('/');
    VehicleInventoryGuard.log('canMatch inicio', {
      path,
      urlActual: this.router.url,
      roleLs: localStorage.getItem('role'),
    });
    return this.auth.refreshPermissionsForGuard().pipe(
      tap((perms) =>
        VehicleInventoryGuard.log('tras GET /me (refreshPermissionsForGuard)', {
          permisosCount: perms.length,
          permisosPrimeros: perms.slice(0, 30),
          roleLs: localStorage.getItem('role'),
        }),
      ),
      map((perms) => this.evaluate(perms, 'me')),
      tap((ok) => VehicleInventoryGuard.log('canMatch fin (ruta /me OK)', { permitido: ok })),
      catchError((err) => {
        VehicleInventoryGuard.log('refreshPermissionsForGuard ERROR → reintento con localStorage', {
          mensaje: err instanceof Error ? err.message : String(err),
        });
        let cached: string[] = [];
        try {
          cached = JSON.parse(localStorage.getItem('permissions') || '[]');
        } catch {
          cached = [];
        }
        return of(this.evaluate(cached, 'cache-ls'));
      }),
    );
  }

  private static log(msg: string, data?: Record<string, unknown>): void {
    if (data !== undefined) {
      console.log(`[VehicleInventoryGuard] ${msg}`, data);
    } else {
      console.log(`[VehicleInventoryGuard] ${msg}`);
    }
  }

  private evaluate(perms: string[], fuente: string): boolean {
    const token = localStorage.getItem('user_token');
    const roleRaw = localStorage.getItem('role');
    const role = (roleRaw || '').trim().toLowerCase();

    if (!token) {
      VehicleInventoryGuard.log('DENEGADO: sin user_token → navegar /auth/login', { fuente });
      this.router.navigateByUrl('/auth/login');
      return false;
    }

    const matchedPerm = VehicleInventoryGuard.permissionAllowList.find((p) => perms.includes(p));
    if (matchedPerm) {
      VehicleInventoryGuard.log('PERMITIDO: permiso en lista', {
        fuente,
        matchedPerm,
        role,
        permisosCount: perms.length,
      });
      return true;
    }

    if (VehicleInventoryGuard.roleAllowList.includes(role)) {
      VehicleInventoryGuard.log('PERMITIDO: rol en lista', {
        fuente,
        role,
        permisosCount: perms.length,
      });
      return true;
    }

    const home = role ? adminDashboardUrl(role) : '/';
    VehicleInventoryGuard.log('DENEGADO: redirigiendo a home del rol', {
      fuente,
      roleRaw,
      roleNormalizado: role,
      home,
      permisosCount: perms.length,
      permisosLista: perms,
      permisosEsperadosAlgunos: VehicleInventoryGuard.permissionAllowList,
      rolesEsperadosAlgunos: VehicleInventoryGuard.roleAllowList,
    });
    this.router.navigateByUrl(home);
    return false;
  }
}
