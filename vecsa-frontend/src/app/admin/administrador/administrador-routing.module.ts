import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminLayoutComponent } from './pages/layout/admin-layout.component';
import { DashboardAdminComponent } from './pages/dashboard/dashboardAdmin.component';
import { AdminUsersComponent } from './pages/admin-users/admin-users.component';
import { AdminPermisosComponent } from './pages/admin-permisos/admin-permisos.component';
import { ExperienceStoriesComponent } from '../marketing/pages/experience-stories/experience-stories.component';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';
import { VehicleInventoryGuard } from '../vehicle-inventory/guards/vehicle-inventory.guard';

const routes: Routes = [
  {
    path: '',
    component: AdminLayoutComponent,
    children: [
      { path: '', component: DashboardAdminComponent },
      { path: 'users', component: AdminUsersComponent },
      { path: 'permissions', component: AdminPermisosComponent },
      {
        path: 'experience-wordpress',
        component: ExperienceStoriesComponent,
        data: { title: 'Experience — Importar WordPress' },
      },
      { path: 'boutique', loadChildren: () => import('./pages/boutique/boutique-admin.module').then(m => m.BoutiqueAdminModule) },
      {
        path: 'vehicle-inventory',
        component: VehiclesComponent,
        canActivate: [VehicleInventoryGuard],
      },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdministradorRoutingModule { }
