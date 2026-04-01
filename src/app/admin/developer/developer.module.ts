import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { DeveloperRoutingModule } from './developer-routing.module';
import { DeveloperDashboardComponent } from './pages/dashboard/dashboard.component';

@NgModule({
  declarations: [DeveloperDashboardComponent],
  imports: [CommonModule, RouterModule, FormsModule, DeveloperRoutingModule],
})
export class DeveloperModule {}
