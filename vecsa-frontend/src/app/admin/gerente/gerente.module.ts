import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { GerenteRoutingModule } from './gerente-routing.module';
import { GerenteDashboardComponent } from './pages/dashboard/gerente-dashboard.component';
import { GerenteLayoutComponent } from './pages/layout/gerente-layout.component';

@NgModule({
  declarations: [
    GerenteDashboardComponent,
    GerenteLayoutComponent,
  ],
  imports: [
    CommonModule,
    GerenteRoutingModule,
    FormsModule,
  ]
})
export class GerenteModule { }
