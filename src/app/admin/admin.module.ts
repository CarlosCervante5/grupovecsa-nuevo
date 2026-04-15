import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { AdminRoutingModule } from './admin-routing.module';
import { AdminEntryRedirectComponent } from './admin-entry-redirect.component';
import { OverviewComponent } from './components/overview/overview.component';
import { AngularMaterialModule } from '../angular-material/angular-material.module';
import { ReactiveFormsModule } from '@angular/forms';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';

@NgModule({
  declarations: [
    OverviewComponent,
  ],
  imports: [
    CommonModule,
    AdminEntryRedirectComponent,
    AdminRoutingModule,
    AngularMaterialModule,
    SkCubeComponent
  ],
  exports: [
    OverviewComponent,
    ReactiveFormsModule
  ]
})
export class AdminModule { }
