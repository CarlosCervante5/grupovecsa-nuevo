import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

// Modules
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { ComprarAutosRoutingModule } from './comprar-autos-routing.module';

// Components
import { CompraTuAutoComponent } from './pages/compra-tu-auto/compra-tu-auto.component';
import { DetailComponent } from './pages/detail/detail.component';
import { VehicleComponent } from './components/vehicle/vehicle.component';
import { AskInformationComponent } from './components/ask-information/ask-information.component';
import { ConditionsComponent } from './pages/conditions/conditions.component';
import { VehiclePanoramaViewerComponent } from './components/vehicle-panorama-viewer/vehicle-panorama-viewer.component';
import { ComprarAutosLayoutComponent } from './layout/comprar-autos-layout.component';

import { MatDialogModule } from '@angular/material/dialog';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';
import { VehicleDealershipStatePipe } from 'src/app/shared/pipes/vehicle-dealership-state.pipe';


@NgModule({
  declarations: [    
    CompraTuAutoComponent, 
    VehicleComponent, 
    DetailComponent, 
    AskInformationComponent,
    ConditionsComponent,
    VehiclePanoramaViewerComponent,
  ],
  imports: [
    CommonModule,
    ComprarAutosRoutingModule,
    FormsModule, 
    ReactiveFormsModule,
    AngularMaterialModule,
    MatDialogModule,
    SkCubeComponent,
    NewNavComponent,
    ComprarAutosLayoutComponent,
    VehicleDealershipStatePipe,
  ],
  exports: [
    VehicleComponent,
  ],
  schemas: [CUSTOM_ELEMENTS_SCHEMA]
})

export class ComprarAutosModule { }
