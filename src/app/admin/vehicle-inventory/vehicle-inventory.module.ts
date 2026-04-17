import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { VehicleStockModule } from '../shared/vehicle-stock/vehicle-stock.module';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';

const routes: Routes = [{ path: '', component: VehiclesComponent }];

@NgModule({
  imports: [VehicleStockModule, RouterModule.forChild(routes)],
})
export class VehicleInventoryModule {}
