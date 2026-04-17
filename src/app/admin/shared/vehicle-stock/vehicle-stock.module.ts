import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { AngularMaterialModule } from '../../../angular-material/angular-material.module';
import { AdminModule } from '../../admin.module';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { VehiclesComponent } from './pages/vehicles/vehicles.component';
import { LoadImagesComponent } from './components/load-images/load-images.component';
import { UpdateImagesComponent } from './components/update-images/update-images.component';
import { UpdateVehicleComponent } from './components/update-vehicle/update-vehicle.component';
import { StoreVehicleComponent } from './components/store-vehicle/store-vehicle.component';
import { AVehicleComponent } from './components/a-vehicle/a-vehicle.component';

@NgModule({
  declarations: [
    VehiclesComponent,
    LoadImagesComponent,
    UpdateImagesComponent,
    UpdateVehicleComponent,
    StoreVehicleComponent,
    AVehicleComponent,
  ],
  imports: [
    CommonModule,
    RouterModule,
    AngularMaterialModule,
    FormsModule,
    ReactiveFormsModule,
    AdminModule,
    DragDropModule,
    SkCubeComponent,
  ],
  exports: [VehiclesComponent],
})
export class VehicleStockModule {}
