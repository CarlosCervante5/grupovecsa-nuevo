import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { RewardsRoutingModule } from './rewards-routing.module';
import { RewardsHomeComponent } from './pages/rewards-home/rewards-home.component';

@NgModule({
  declarations: [RewardsHomeComponent],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule,
    RewardsRoutingModule,
  ]
})
export class RewardsModule {}
