import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { PromotionsComponent } from './pages/promotions/promotions.component';
import { ScheduleEventsComponent } from './pages/schedule-events/schedule-events.component';
import { RewardsComponent } from './pages/rewards/rewards.component';
import { GestorLayoutComponent } from './pages/layout/gestor-layout.component';
import { GestorFeatureGuard } from './guards/gestor-feature.guard';
import { GESTOR_FEATURE_PERMISSIONS } from 'src/app/admin/utils/gestor-feature-permissions';

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
        component: ScheduleEventsComponent,
        canActivate: [GestorFeatureGuard],
        data: { requiredPermission: GESTOR_FEATURE_PERMISSIONS.scheduledEvents },
      },
      {
        path: 'rewards',
        component: RewardsComponent,
        canActivate: [GestorFeatureGuard],
        data: { requiredPermission: GESTOR_FEATURE_PERMISSIONS.rewards },
      },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class GestorRoutingModule { }
