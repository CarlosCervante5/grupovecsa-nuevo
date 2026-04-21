import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { SparePartsLayoutComponent } from './pages/layout/spare-parts-layout.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { SparePartsAdministrationComponent } from './pages/spare-parts-administration/spare-parts-administration.component';
import { SparePartsViewComponent } from './pages/spare-parts-view/spare-parts-view.component';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';

const routes: Routes = [
  {
    path: '',
    component: SparePartsLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'administration', component: SparePartsAdministrationComponent },
      { path: 'administration/view/:uuid', component: SparePartsViewComponent },
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
export class SparePartsRoutingModule { }
