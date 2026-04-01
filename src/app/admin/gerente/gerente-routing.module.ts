import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { GerenteDashboardComponent } from './pages/dashboard/gerente-dashboard.component';
import { GerenteLayoutComponent } from './pages/layout/gerente-layout.component';

const routes: Routes = [
  {
    path: '',
    component: GerenteLayoutComponent,
    children: [
      { path: '', component: GerenteDashboardComponent },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class GerenteRoutingModule { }
