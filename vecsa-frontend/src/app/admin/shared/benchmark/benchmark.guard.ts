import { Injectable } from '@angular/core';
import { Router, CanActivate, CanLoad, CanMatch, Route, UrlSegment } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';

@Injectable({ providedIn: 'root' })
export class BenchmarkGuard implements CanActivate, CanLoad, CanMatch {
  constructor(
    private router: Router,
    private auth: AuthService,
  ) {}

  canActivate(): Observable<boolean> {
    return this.check$();
  }

  canLoad(): Observable<boolean> {
    return this.check$();
  }

  canMatch(_route: Route, _segments: UrlSegment[]): Observable<boolean> {
    return this.check$();
  }

  private check$(): Observable<boolean> {
    return this.auth.refreshPermissionsInStorage().pipe(map(() => this.evaluate()));
  }

  private evaluate(): boolean {
    const perms = this.getPermissions();
    if (perms.includes('access benchmark')) return true;
    const role = (localStorage.getItem('role') || '').trim().toLowerCase();
    if (role === 'developer' || role === 'administrator' || role === 'admin') return true;
    this.router.navigateByUrl('/');
    return false;
  }

  private getPermissions(): string[] {
    try {
      return JSON.parse(localStorage.getItem('permissions') || '[]');
    } catch {
      return [];
    }
  }
}
