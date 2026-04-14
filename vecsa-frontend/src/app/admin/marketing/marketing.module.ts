import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { DragDropModule } from '@angular/cdk/drag-drop';
import { MarketingRoutingModule } from './marketing-routing.module';
import { VehiclesComponent } from './pages/vehicles/vehicles.component';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { AdminModule } from '../admin.module';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { LoadImagesComponent } from './components/load-images/load-images.component';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { UpdateImagesComponent } from './components/update-images/update-images.component';
import { UpdateVehicleComponent } from './components/update-vehicle/update-vehicle.component';
import { StoreVehicleComponent } from './components/store-vehicle/store-vehicle.component';
import { AVehicleComponent } from './components/a-vehicle/a-vehicle.component';
import { SkCubeComponent } from '@components/sk-cube/sk-cube.component';
import { HomeSlidesComponent } from './pages/home-slides/home-slides.component';
import { HomeTestimonialsComponent } from './pages/home-testimonials/home-testimonials.component';
import { BoutiqueBannersComponent } from './pages/boutique-banners/boutique-banners.component';
import { MarketingLayoutComponent } from './pages/layout/marketing-layout.component';
import { ExperienceStoriesComponent } from './pages/experience-stories/experience-stories.component';

@NgModule({
  declarations: [
    DashboardComponent,
    VehiclesComponent,
    LoadImagesComponent,
    UpdateImagesComponent,
    UpdateVehicleComponent,
    StoreVehicleComponent,
    AVehicleComponent,
    HomeSlidesComponent,
    HomeTestimonialsComponent,
    BoutiqueBannersComponent,
    ExperienceStoriesComponent,
    MarketingLayoutComponent
  ],
  imports: [
    CommonModule,
    AngularMaterialModule,
    FormsModule,
    MarketingRoutingModule,
    AdminModule,
    DragDropModule,
    ReactiveFormsModule,
    SkCubeComponent
  ]
})
export class MarketingModule { }
