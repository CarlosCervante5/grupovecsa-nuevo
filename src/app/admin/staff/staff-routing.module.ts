import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { RidersComponent } from './pages/riders/riders.component';
// import { NewCustomerComponent } from './components/new-customer/new-customer.component';
import { SalesComponent } from './pages/sales/sales.component';

const routes: Routes = [
    { path: '', component: DashboardComponent},
    { path: 'riders', component: RidersComponent },
    { path: 'sales', component: SalesComponent},
    // { path: 'newc', component: NewCustomerComponent},
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class StaffRoutingModule { }
