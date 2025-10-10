import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DragDropModule } from '@angular/cdk/drag-drop';
import { ReceptionistRoutingModule } from './receptionist-routing.module';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ReceptionFormComponent } from './pages/reception-form/reception-form.component';
import { AdminModule } from '../admin.module';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component'
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';

@NgModule({
  declarations: [
    DashboardComponent,
    ReceptionFormComponent
  ],
  imports: [
    CommonModule,
    AngularMaterialModule,
    FormsModule,
    ReceptionistRoutingModule,
    AdminModule,
    DragDropModule,
    ReactiveFormsModule,
    SkCubeComponent,
    NewNavComponent
  ]
})
export class ReceptionistModule { }
