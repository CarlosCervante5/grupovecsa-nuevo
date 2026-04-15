import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { adminDashboardUrl } from './utils/admin-route.util';

/**
 * Resuelve la URL `/admin` (sin segmento) según rol en sesión.
 * Sin esto, el comodín del módulo admin enviaba a /404.
 */
@Component({
  selector: 'app-admin-entry-redirect',
  standalone: true,
  template: '',
})
export class AdminEntryRedirectComponent implements OnInit {
  constructor(private router: Router) {}

  ngOnInit(): void {
    if (!localStorage.getItem('user_token')) {
      void this.router.navigateByUrl('/auth/iniciar-sesion', { replaceUrl: true });
      return;
    }
    const role = (localStorage.getItem('role') || '').trim().toLowerCase();
    if (role === 'client') {
      void this.router.navigateByUrl('/auth/mi-cuenta', { replaceUrl: true });
      return;
    }
    const target = adminDashboardUrl(localStorage.getItem('role'));
    if (!target.startsWith('/admin/')) {
      void this.router.navigateByUrl('/auth/iniciar-sesion', { replaceUrl: true });
      return;
    }
    void this.router.navigateByUrl(target, { replaceUrl: true });
  }
}
