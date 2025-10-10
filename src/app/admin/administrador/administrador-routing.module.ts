import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardAdminComponent } from './pages/dashboard/dashboardAdmin.component';
import { AdminUsersComponent } from './pages/admin-users/admin-users.component';
import { AdminPermisosComponent } from './pages/admin-permisos/admin-permisos.component';

const routes: Routes = [
  { path: '', component: DashboardAdminComponent },
  { path: 'users', component: AdminUsersComponent },
  { path: 'permissions', component: AdminPermisosComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdministradorRoutingModule { }

