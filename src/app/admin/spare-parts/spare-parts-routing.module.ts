import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { SparePartsAdministrationComponent } from './pages/spare-parts-administration/spare-parts-administration.component';
import { SparePartsViewComponent } from './pages/spare-parts-view/spare-parts-view.component';

const routes: Routes = [
  { path: '', component: DashboardComponent },
  { path: 'administration', component: SparePartsAdministrationComponent},
  { path: 'administration/view/:uuid', component: SparePartsViewComponent},
  { path: '', pathMatch: 'full', redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class SparePartsRoutingModule { }
