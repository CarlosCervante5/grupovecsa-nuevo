import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { RewardsHomeComponent } from './pages/rewards-home/rewards-home.component';

const routes: Routes = [
  { path: '', component: RewardsHomeComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class RewardsRoutingModule {}
