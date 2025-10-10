import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { GestorRoutingModule } from './gestor-routing.module';
import { DashboardComponent } from './pages/dashboard/dashboard.component';
import { AdminModule } from '../admin.module';
import { PromotionsComponent } from './pages/promotions/promotions.component';
import { ScheduleEventsComponent } from './pages/schedule-events/schedule-events.component';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { LoadImagesPromoComponent } from './components/load-images-promo/load-images-promo.component';
import { UpdateImagesPromoComponent } from './components/update-images-promo/update-images-promo.component';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { LoadVideoComponent } from './components/events/load-video/load-video.component';
import { ShowEventComponent } from './components/show-event/show-event.component';
import { CreateEventComponent } from './components/create-event/create-event.component';
import { ReactiveFormsModule } from '@angular/forms';
import { EventCardComponent } from './components/event-card/event-card.component';
import { LoadPrincipalImagesComponent } from './components/load-principal-images/load-principal-images.component';
import { CreateCampaingComponent } from './components/create-campaing/create-campaing.component';
import { UpdateImagesComponent } from './components/update-images/update-images.component';
import { RewardsComponent } from './pages/rewards/rewards.component';
import { AddRewardrComponent } from './components/add-rewardr/add-rewardr.component';
import { UpdateRewardsComponent } from './components/update-rewards/update-rewards.component';
import { NewNavComponent } from 'src/app/shared/versiones-nav/new-nav/new-nav.component';


@NgModule({
  declarations: [
    DashboardComponent,
    PromotionsComponent,
    ScheduleEventsComponent,
    LoadImagesPromoComponent,
    UpdateImagesPromoComponent,
    LoadVideoComponent,
    ShowEventComponent,
    CreateEventComponent,
    EventCardComponent,
    LoadPrincipalImagesComponent,
    CreateCampaingComponent,
    UpdateImagesComponent,
    RewardsComponent,
    AddRewardrComponent,
    UpdateRewardsComponent,
  ],
  imports: [
    AngularMaterialModule,
    CommonModule,
    GestorRoutingModule,
    AdminModule,
    DragDropModule,
    ReactiveFormsModule,
    NewNavComponent
  ]
})
export class GestorModule { }
