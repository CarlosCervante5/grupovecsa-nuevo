import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { PromotionsComponent } from './pages/promotions/promotions.component';
import { ScheduleEventsComponent } from './pages/schedule-events/schedule-events.component';
import { RewardsComponent } from './pages/rewards/rewards.component';

const routes: Routes = [
  { path: '', component: DashboardComponent },
  { path: 'promotions', component: PromotionsComponent },
  { path: 'scheduled-events', component: ScheduleEventsComponent },
  { path: 'rewards', component: RewardsComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class GestorRoutingModule { }
