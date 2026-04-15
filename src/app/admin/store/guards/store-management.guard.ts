import { Injectable } from '@angular/core';
import { Router, CanMatch, Route, UrlSegment } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';

/**
 * Solo canMatch en la ruta (evita canLoad + canActivate = dos GET /me en paralelo y condiciones de carrera).
 */
@Injectable({ providedIn: 'root' })
export class StoreManagementGuard implements CanMatch {
  private static readonly bypassRoles = ['developer', 'administrator', 'gerente', 'gestor'];

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
    if (perms.includes('access store_management')) {
      return true;
    }
    const role = (localStorage.getItem('role') || '').trim().toLowerCase();
    if (StoreManagementGuard.bypassRoles.includes(role)) {
      return true;
    }
    const home = role ? adminDashboardUrl(role) : '/';
    this.router.navigateByUrl(home);
    return false;
  }
}
