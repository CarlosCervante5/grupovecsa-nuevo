import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DragDropModule } from '@angular/cdk/drag-drop';
import { MarketingRoutingModule } from './marketing-routing.module';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { AdminModule } from '../admin.module';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { HomeSlidesComponent } from './pages/home-slides/home-slides.component';
import { HomeTestimonialsComponent } from './pages/home-testimonials/home-testimonials.component';
import { BoutiqueBannersComponent } from './pages/boutique-banners/boutique-banners.component';
import { MarketingLayoutComponent } from './pages/layout/marketing-layout.component';
import { ExperienceStoriesSharedModule } from '../shared/experience-stories/experience-stories-shared.module';
import { VehicleStockModule } from '../shared/vehicle-stock/vehicle-stock.module';

@NgModule({
  declarations: [
    DashboardComponent,
    HomeSlidesComponent,
    HomeTestimonialsComponent,
    BoutiqueBannersComponent,
    MarketingLayoutComponent,
  ],
  imports: [
    CommonModule,
    AngularMaterialModule,
    FormsModule,
    MarketingRoutingModule,
    AdminModule,
    DragDropModule,
    ReactiveFormsModule,
    SkCubeComponent,
    ExperienceStoriesSharedModule,
    VehicleStockModule,
  ],
})
export class MarketingModule {}
