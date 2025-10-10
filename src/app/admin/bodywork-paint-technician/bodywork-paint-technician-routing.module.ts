import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { BodyworkPaintTechnicianComponent } from './pages/bodywork-paint/bodywork-paint-technician.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';

const routes: Routes = [
  { path: '', component: DashboardComponent },
  { path: 'bodywork-paint', component: BodyworkPaintTechnicianComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class BodyworkPaintTechnicianRoutingModule { }
