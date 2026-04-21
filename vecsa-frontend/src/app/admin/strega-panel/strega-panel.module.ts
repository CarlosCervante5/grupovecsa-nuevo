import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { StregaPanelRoutingModule } from './strega-panel-routing.module';
import { StregaLayoutComponent } from './pages/layout/strega-layout.component';
import { StregaLeadsComponent } from './pages/leads/strega-leads.component';
import { StregaAppointmentsComponent } from './pages/appointments/strega-appointments.component';
import { AdminModule } from '../admin.module';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { MatTableModule } from '@angular/material/table';
import { BenchmarkModule } from '../shared/benchmark/benchmark.module';

@NgModule({
  declarations: [StregaLayoutComponent, StregaLeadsComponent, StregaAppointmentsComponent],
  imports: [
    CommonModule,
    FormsModule,
    StregaPanelRoutingModule,
    AdminModule,
    AngularMaterialModule,
    MatTableModule,
    BenchmarkModule,
  ],
})
export class StregaPanelModule {}
