import { Injectable } from '@angular/core';
import { Router } from '@angular/router';

@Injectable({ providedIn: 'root' })
export class StoreManagementGuard {
  constructor(private router: Router) {}

  canActivate(): boolean {
    const perms = this.getPermissions();
    if (perms.includes('access store_management')) return true;
    const role = localStorage.getItem('role') || '';
    if (role === 'developer') return true;
    this.router.navigateByUrl('/');
    return false;
  }

  canLoad(): boolean {
    return this.canActivate();
  }

  private getPermissions(): string[] {
    try {
      return JSON.parse(localStorage.getItem('permissions') || '[]');
    } catch { return []; }
  }
}
