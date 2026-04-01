import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StaffLayoutComponent } from './pages/layout/staff-layout.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { RidersComponent } from './pages/riders/riders.component';
import { SalesComponent } from './pages/sales/sales.component';

const routes: Routes = [
  {
    path: '',
    component: StaffLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'riders', component: RidersComponent },
      { path: 'sales', component: SalesComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StaffRoutingModule {}
