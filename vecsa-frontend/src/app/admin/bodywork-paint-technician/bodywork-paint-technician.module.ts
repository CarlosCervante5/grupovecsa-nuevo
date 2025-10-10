import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { BodyworkPaintTechnicianRoutingModule } from './bodywork-paint-technician-routing.module';
import { AdminModule } from '../admin.module';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';

import { BodyworkPaintTechnicianComponent } from './pages/bodywork-paint/bodywork-paint-technician.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { BodyworkPaintFormComponent } from './components/bodywork-paint-form/bodywork-paint-form.component';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';


@NgModule({
  declarations: [
    BodyworkPaintFormComponent,
    BodyworkPaintTechnicianComponent,
    DashboardComponent
  ],
  imports: [
    AdminModule,
    AngularMaterialModule,
    BodyworkPaintTechnicianRoutingModule,
    CommonModule,
    FormsModule,
    NewNavComponent
  ]
})
export class BodyworkPaintTechnicianModule { }
