import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { VehicleStockModule } from '../shared/vehicle-stock/vehicle-stock.module';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';
import { VehicleInventoryShellComponent } from './pages/vehicle-inventory-shell/vehicle-inventory-shell.component';

const routes: Routes = [
  {
    path: '',
    component: VehicleInventoryShellComponent,
    children: [{ path: '', component: VehiclesComponent }],
  },
];

@NgModule({
  declarations: [VehicleInventoryShellComponent],
  imports: [CommonModule, VehicleStockModule, RouterModule.forChild(routes)],
})
export class VehicleInventoryModule {}
