import { Component, OnInit, inject } from '@angular/core';
import { Router } from '@angular/router';
import {
  adminBenchmarkUrl,
  adminDashboardUrl,
} from 'src/app/admin/utils/admin-route.util';

/** `/admin/benchmark` redirige a la ruta hija con layout del panel actual. */
@Component({
  selector: 'app-benchmark-redirect',
  standalone: true,
  template: '',
})
export class BenchmarkRedirectComponent implements OnInit {
  private readonly router = inject(Router);

  ngOnInit(): void {
    const role = localStorage.getItem('role');
    const target = adminBenchmarkUrl(role);
    if (target) {
      void this.router.navigateByUrl(target, { replaceUrl: true });
      return;
    }
    void this.router.navigateByUrl(adminDashboardUrl(role) || '/', { replaceUrl: true });
  }
}
