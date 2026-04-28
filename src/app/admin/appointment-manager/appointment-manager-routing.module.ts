import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AppointmentManagerLayoutComponent } from './pages/layout/appointment-manager-layout.component';
import { AppointmentManagerComponent } from './pages/appointment-manager/appointment-manager.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';

const routes: Routes = [
  {
    path: '',
    component: AppointmentManagerLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'assign-valuations', component: AppointmentManagerComponent },
      { path: 'assing-valuations', component: AppointmentManagerComponent },
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
  exports: [RouterModule]
})
export class AppointmentManagerRoutingModule { }
