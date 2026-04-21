import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { SparePartsRoutingModule } from './spare-parts-routing.module';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { SparePartsLayoutComponent } from './pages/layout/spare-parts-layout.component';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';
import { AdminModule } from '../admin.module';
import { SparePartsAdministrationComponent } from './pages/spare-parts-administration/spare-parts-administration.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { SparePartsViewComponent } from './pages/spare-parts-view/spare-parts-view.component';
import { SparePartsEditComponent } from './components/spare-parts-edit/spare-parts-edit.component';
import { BenchmarkModule } from '../shared/benchmark/benchmark.module';


@NgModule({
  declarations: [
    DashboardComponent,
    SparePartsLayoutComponent,
    SparePartsAdministrationComponent,
    SparePartsEditComponent,
    SparePartsViewComponent
  ],
  imports: [
    AdminModule,
    AngularMaterialModule,
    CommonModule,
    FormsModule,
    NewNavComponent,
    ReactiveFormsModule,
    SparePartsRoutingModule,
    BenchmarkModule
  ]
})
export class SparePartsModule { }
