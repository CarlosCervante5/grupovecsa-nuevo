import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ValuatorLayoutComponent } from './pages/layout/valuator-layout.component';
import { AppointmentsComponent } from './pages/appointments/appointments.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ChecklistComponent } from './pages/checklist/checklist.component';
import { QuoteSellCarRequestComponent } from './pages/quote-sell-car-request/quote-sell-car-request.component';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';

const routes: Routes = [
  {
    path: '',
    component: ValuatorLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'appointment', component: AppointmentsComponent },
      { path: 'checklist/:uuid_valuation', component: ChecklistComponent },
      { path: 'quote-request/:uuid_valuation', component: QuoteSellCarRequestComponent },
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
export class ValuatorRoutingModule { }
