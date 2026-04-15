import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { AuthService } from 'src/app/auth/services/auth.service';

@Injectable({ providedIn: 'root' })
export class StoreManagementGuard {
  constructor(
    private router: Router,
    private auth: AuthService,
  ) {}

  canActivate(): Observable<boolean> {
    return this.auth.refreshPermissionsInStorage().pipe(map(() => this.evaluate()));
  }

  canLoad(): Observable<boolean> {
    return this.canActivate();
  }

  private evaluate(): boolean {
    const perms = this.getPermissions();
    if (perms.includes('access store_management')) return true;
    const role = localStorage.getItem('role') || '';
    if (role === 'developer' || role === 'administrator') return true;
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
