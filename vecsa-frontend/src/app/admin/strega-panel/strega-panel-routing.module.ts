import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StregaLayoutComponent } from './pages/layout/strega-layout.component';
import { StregaLeadsComponent } from './pages/leads/strega-leads.component';
import { StregaAppointmentsComponent } from './pages/appointments/strega-appointments.component';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';

const routes: Routes = [
  {
    path: '',
    component: StregaLayoutComponent,
    children: [
      { path: '', component: StregaLeadsComponent },
      { path: 'citas', component: StregaAppointmentsComponent },
      {
        path: 'benchmark',
        component: BenchmarkComponent,
        canActivate: [BenchmarkGuard],
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StregaPanelRoutingModule {}
