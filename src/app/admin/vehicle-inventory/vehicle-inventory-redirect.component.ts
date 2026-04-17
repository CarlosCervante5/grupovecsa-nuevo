import { Component, OnInit, inject } from '@angular/core';
import { Router } from '@angular/router';
import {
  adminDashboardUrl,
  adminVehicleInventoryUrl,
} from 'src/app/admin/utils/admin-route.util';

/**
 * `/admin/vehicle-inventory` redirige al hijo con layout (`/admin/.../vehicle-inventory` o marketing/vehicles).
 */
@Component({
  selector: 'app-vehicle-inventory-redirect',
  standalone: true,
  template: '',
})
export class VehicleInventoryRedirectComponent implements OnInit {
  private readonly router = inject(Router);

  ngOnInit(): void {
    const role = localStorage.getItem('role');
    const target = adminVehicleInventoryUrl(role);
    if (target) {
      void this.router.navigateByUrl(target, { replaceUrl: true });
      return;
    }
    void this.router.navigateByUrl(adminDashboardUrl(role) || '/', { replaceUrl: true });
  }
}
