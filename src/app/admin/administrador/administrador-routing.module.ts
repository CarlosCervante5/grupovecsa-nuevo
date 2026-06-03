import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminLayoutComponent } from './pages/layout/admin-layout.component';
import { DashboardAdminComponent } from './pages/dashboard/dashboardAdmin.component';
import { AdminUsersComponent } from './pages/admin-users/admin-users.component';
import { AdminPermisosComponent } from './pages/admin-permisos/admin-permisos.component';
import { ExperienceStoriesComponent } from '../marketing/pages/experience-stories/experience-stories.component';
import { AssistantChatsComponent } from '../marketing/pages/assistant-chats/assistant-chats.component';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';
import { VehicleInventoryGuard } from '../vehicle-inventory/guards/vehicle-inventory.guard';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';
import { AdminManualsComponent } from './pages/manuales/admin-manuals.component';
import { AdminManualDetailComponent } from './pages/manuales/admin-manual-detail.component';
import { AdminDealershipsComponent } from './pages/admin-dealerships/admin-dealerships.component';
import { AdminLegalesListComponent } from './pages/legales/admin-legales-list.component';
import { AdminLegalEditComponent } from './pages/legales/admin-legal-edit.component';

const routes: Routes = [
  {
    path: '',
    component: AdminLayoutComponent,
    children: [
      { path: '', component: DashboardAdminComponent },
      { path: 'sucursales', component: AdminDealershipsComponent },
      { path: 'users', component: AdminUsersComponent },
      { path: 'permissions', component: AdminPermisosComponent },
      { path: 'legales', component: AdminLegalesListComponent },
      { path: 'legales/:slug', component: AdminLegalEditComponent },
      { path: 'manuales', component: AdminManualsComponent },
      { path: 'manuales/:panel', component: AdminManualDetailComponent },
      {
        path: 'experience-wordpress',
        component: ExperienceStoriesComponent,
        data: { title: 'Experience — Importar WordPress' },
      },
      {
        path: 'assistant-chats',
        component: AssistantChatsComponent,
        data: { title: 'Chats del asistente' },
      },
      { path: 'boutique', loadChildren: () => import('./pages/boutique/boutique-admin.module').then(m => m.BoutiqueAdminModule) },
      {
        path: 'vehicle-inventory',
        component: VehiclesComponent,
        canActivate: [VehicleInventoryGuard],
      },
      {
        path: 'benchmark',
        component: BenchmarkComponent,
        canActivate: [BenchmarkGuard],
      },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdministradorRoutingModule { }
