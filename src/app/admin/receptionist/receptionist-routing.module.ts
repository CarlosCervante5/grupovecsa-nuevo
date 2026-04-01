import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ReceptionistLayoutComponent } from './pages/layout/receptionist-layout.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { ReceptionFormComponent } from './pages/reception-form/reception-form.component';

const routes: Routes = [
  {
    path: '',
    component: ReceptionistLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      { path: 'reception-form', component: ReceptionFormComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ReceptionistRoutingModule { }
