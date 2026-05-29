import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { GerenteRoutingModule } from './gerente-routing.module';
import { GerenteDashboardComponent } from './pages/dashboard/gerente-dashboard.component';
import { GerenteLayoutComponent } from './pages/layout/gerente-layout.component';
import { VehicleStockModule } from '../shared/vehicle-stock/vehicle-stock.module';
import { BenchmarkModule } from '../shared/benchmark/benchmark.module';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';

@NgModule({
  declarations: [
    GerenteDashboardComponent,
    GerenteLayoutComponent,
  ],
  imports: [
    CommonModule,
    GerenteRoutingModule,
    FormsModule,
    VehicleStockModule,
    BenchmarkModule,
    SkCubeComponent,
  ]
})
export class GerenteModule { }
