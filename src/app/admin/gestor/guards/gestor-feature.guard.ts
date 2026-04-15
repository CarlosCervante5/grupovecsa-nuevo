import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Router, RouterStateSnapshot, UrlTree } from '@angular/router';
import { Observable, of } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';
import { expandLegacyGestorPermissions } from 'src/app/admin/utils/gestor-feature-permissions';

/**
 * Protege rutas hijas del módulo gestor según route.data.requiredPermission.
 */
@Injectable({ providedIn: 'root' })
export class GestorFeatureGuard {
  constructor(
    private router: Router,
    private auth: AuthService,
  ) {}

  canActivate(route: ActivatedRouteSnapshot, _state: RouterStateSnapshot): Observable<boolean | UrlTree> {
    const required = route.data?.['requiredPermission'] as string | undefined;
    if (!required) {
      return of(true);
    }
    return this.auth.refreshPermissionsForGuard().pipe(
      map((perms) => {
        const role = localStorage.getItem('role');
        const effective = expandLegacyGestorPermissions(perms, role);
        if (effective.includes(required)) {
          return true;
        }
        const r = (role || '').toLowerCase();
        if (r === 'developer' || r === 'administrator') {
          return true;
        }
        return this.router.parseUrl(adminDashboardUrl(role));
      }),
    );
  }
}
