import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { ValuatorRoutingModule } from './valuator-routing.module';
import { AppointmentsComponent } from './pages/appointments/appointments.component';
import { AdminModule } from '../admin.module';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { AppointmentFormComponent } from './components/appointment-form/appointment-form.component';
import { ChecklistComponent } from './pages/checklist/checklist.component';
import { ExternalRevisionPictureComponent } from './components/external-revision-picture/external-revision-picture.component';
import { InternalRevisionPictureComponent } from './components/internal-revision-picture/internal-revision-picture.component';
import { SparePartsFormComponent } from './components/spare-parts-form/spare-parts-form.component';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';
import { BodyworkPaintValuatorFormComponent } from './components/bodywork-paint-valuator-form/bodywork-paint-valuator-form.component';
import { QuoteSellCarRequestComponent } from './pages/quote-sell-car-request/quote-sell-car-request.component';
import { DocumentationVehicleComponent } from './components/documentation-vehicle/documentation-vehicle.component';
import { ValuatorLayoutComponent } from './pages/layout/valuator-layout.component';
import { SkCubeComponent } from "@components/sk-cube/sk-cube.component";
import { BenchmarkModule } from '../shared/benchmark/benchmark.module';


@NgModule({
  declarations: [
    AppointmentFormComponent,
    AppointmentsComponent,
    BodyworkPaintValuatorFormComponent,
    ChecklistComponent,
    DashboardComponent,
    ExternalRevisionPictureComponent,
    InternalRevisionPictureComponent,
    QuoteSellCarRequestComponent,
    SparePartsFormComponent,
    DocumentationVehicleComponent,
    ValuatorLayoutComponent
  ],
  imports: [
    AdminModule,
    AngularMaterialModule,
    ValuatorRoutingModule,
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    NewNavComponent,
    SkCubeComponent,
    BenchmarkModule
  ]
})
// export class AppointmentValuationsModule { }
export class ValuatorModule { }
