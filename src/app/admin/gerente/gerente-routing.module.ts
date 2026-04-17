import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GerenteDashboardComponent } from './pages/dashboard/gerente-dashboard.component';
import { GerenteLayoutComponent } from './pages/layout/gerente-layout.component';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';
import { VehicleInventoryGuard } from '../vehicle-inventory/guards/vehicle-inventory.guard';

const routes: Routes = [
  {
    path: '',
    component: GerenteLayoutComponent,
    children: [
      { path: '', component: GerenteDashboardComponent },
      {
        path: 'vehicle-inventory',
        component: VehiclesComponent,
        canActivate: [VehicleInventoryGuard],
      },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class GerenteRoutingModule { }
