import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { HomeSlidesComponent } from './pages/home-slides/home-slides.component';
import { HomeTestimonialsComponent } from './pages/home-testimonials/home-testimonials.component';
import { BoutiqueBannersComponent } from './pages/boutique-banners/boutique-banners.component';
import { CarcareBannersComponent } from './pages/carcare-banners/carcare-banners.component';
import { MarketingLayoutComponent } from './pages/layout/marketing-layout.component';
import { ExperienceStoriesComponent } from './pages/experience-stories/experience-stories.component';
import { AssistantChatsComponent } from './pages/assistant-chats/assistant-chats.component';
import { VehiclesComponent } from '../shared/vehicle-stock/pages/vehicles/vehicles.component';
import { VehicleInventoryGuard } from '../vehicle-inventory/guards/vehicle-inventory.guard';
import { BenchmarkComponent } from '../shared/benchmark/benchmark.component';
import { BenchmarkGuard } from '../shared/benchmark/benchmark.guard';

const routes: Routes = [
  {
    path: '',
    component: MarketingLayoutComponent,
    children: [
      { path: '', component: DashboardComponent },
      {
        path: 'vehicles',
        component: VehiclesComponent,
        canActivate: [VehicleInventoryGuard],
      },
      { path: 'home-slides', component: HomeSlidesComponent },
      { path: 'home-testimonials', component: HomeTestimonialsComponent },
      { path: 'boutique-banners', component: BoutiqueBannersComponent },
      { path: 'carcare-banners', component: CarcareBannersComponent },
      { path: 'experience-stories', component: ExperienceStoriesComponent },
      { path: 'assistant-chats', component: AssistantChatsComponent },
      {
        path: 'benchmark',
        component: BenchmarkComponent,
        canActivate: [BenchmarkGuard],
      },
    ]
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class MarketingRoutingModule { }
