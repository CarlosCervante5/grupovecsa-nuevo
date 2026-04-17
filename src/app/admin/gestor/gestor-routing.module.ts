import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { PromotionsComponent } from './pages/promotions/promotions.component';
import { ExperienceStoriesComponent } from '../marketing/pages/experience-stories/experience-stories.component';
import { RewardsComponent } from './pages/rewards/rewards.component';
import { GestorLayoutComponent } from './pages/layout/gestor-layout.component';
import { GestorFeatureGuard } from './guards/gestor-feature.guard';
import { GESTOR_FEATURE_PERMISSIONS } from 'src/app/admin/utils/gestor-feature-permissions';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';
import { VehicleInventoryGuard } from '../vehicle-inventory/guards/vehicle-inventory.guard';

const routes: Routes = [
  {
    path: '',
    component: GestorLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      {
        path: 'promotions',
        component: PromotionsComponent,
        canActivate: [GestorFeatureGuard],
        data: { requiredPermission: GESTOR_FEATURE_PERMISSIONS.promotions },
      },
      {
        path: 'scheduled-events',
        component: ExperienceStoriesComponent,
        canActivate: [GestorFeatureGuard],
        data: { requiredPermission: GESTOR_FEATURE_PERMISSIONS.scheduledEvents },
      },
      {
        path: 'rewards',
        component: RewardsComponent,
        canActivate: [GestorFeatureGuard],
        data: { requiredPermission: GESTOR_FEATURE_PERMISSIONS.rewards },
      },
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
export class GestorRoutingModule { }
