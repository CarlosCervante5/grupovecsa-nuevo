import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StaffLayoutComponent } from './pages/layout/staff-layout.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { RidersComponent } from './pages/riders/riders.component';
import { SalesComponent } from './pages/sales/sales.component';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';

const routes: Routes = [
  {
    path: '',
    component: StaffLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'riders', component: RidersComponent },
      { path: 'sales', component: SalesComponent },
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
export class StaffRoutingModule {}
