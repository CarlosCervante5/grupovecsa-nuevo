import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AppointmentManagerLayoutComponent } from './pages/layout/appointment-manager-layout.component';
import { AppointmentManagerComponent } from './pages/appointment-manager/appointment-manager.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';

const routes: Routes = [
  {
    path: '',
    component: AppointmentManagerLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'assing-valuations', component: AppointmentManagerComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AppointmentManagerRoutingModule { }
