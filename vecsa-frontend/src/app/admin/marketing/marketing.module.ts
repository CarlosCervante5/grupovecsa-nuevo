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
import { CarcareBannersComponent } from './pages/carcare-banners/carcare-banners.component';
import { MarketingLayoutComponent } from './pages/layout/marketing-layout.component';
import { ExperienceStoriesSharedModule } from '../shared/experience-stories/experience-stories-shared.module';
import { AssistantChatsSharedModule } from '../shared/assistant-chats/assistant-chats-shared.module';
import { VehicleStockModule } from '../shared/vehicle-stock/vehicle-stock.module';
import { BenchmarkModule } from '../shared/benchmark/benchmark.module';

@NgModule({
  declarations: [
    DashboardComponent,
    HomeSlidesComponent,
    HomeTestimonialsComponent,
    BoutiqueBannersComponent,
    CarcareBannersComponent,
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
    AssistantChatsSharedModule,
    VehicleStockModule,
    BenchmarkModule,
  ],
})
export class MarketingModule {}
