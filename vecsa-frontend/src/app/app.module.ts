

import {  CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { BrowserModule, Title } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

// Components
import { AppComponent } from './app.component';
import { NotFoundComponent } from './shared/not-found/not-found.component';
import { FooterComponent } from './shared/footer/footer.component';

// Angular Material & Flex Layout
import { AngularMaterialModule } from './angular-material/angular-material.module';

// Modules
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AppRoutingModule } from './app-routing.module';
import { AuthRoutingModule } from './auth/auth-routing.module';
import { AuthModule } from './auth/auth.module';
import { ComprarAutosRoutingModule } from './dashboard/pages/comprar-autos/comprar-autos-routing.module';
import { ComprarAutosModule } from './dashboard/pages/comprar-autos/comprar-autos.module';
import { MatSliderModule } from '@angular/material/slider';
import { HomeVecsaComponent } from './dashboard/pages/home-vecsa/home-vecsa.component';
import { TeamComponent } from './dashboard/pages/home-vecsa/children/team/team.component';
import { CtComponent } from './dashboard/pages/home-vecsa/children/ct/ct.component';
import { CarruselComponent } from './dashboard/pages/home-vecsa/children/carrusel/carrusel.component';
import { CardshopComponent } from './dashboard/pages/home-vecsa/children/cardshop/cardshop.component';
import { PromotionsComponent } from './dashboard/pages/home-vecsa/children/promotions/promotions.component';
import { LifestyleComponent } from './dashboard/pages/home-vecsa/children/lifestyle/lifestyle.component';
import { ProductComponent } from './dashboard/pages/home-vecsa/children/promotions/product/product.component';
import { FormLeadsComponent } from './dashboard/pages/home-vecsa/children/form-leads/form-leads.component';
import { CardscontactoComponent } from './dashboard/pages/home-vecsa/children/cardscontacto/cardscontacto.component';
import { MapComponent } from './dashboard/pages/home-vecsa/children/map/map.component';
import { EventsComponent } from './dashboard/pages/home-vecsa/pages/events/events.component';
import { BannerComponent } from './dashboard/pages/home-vecsa/children/banner/banner.component';
import { PromotionscardComponent } from './dashboard/pages/home-vecsa/children/promotionscard/promotionscard.component';
import { CommunityComponent } from './dashboard/pages/home-vecsa/pages/community/community.component';
import { CommunitycardComponent } from './dashboard/pages/home-vecsa/children/communitycard/communitycard.component';
import { CommunityvideoComponent } from './dashboard/pages/home-vecsa/children/communityvideo/communityvideo.component';
import { CommunityeventsComponent } from './dashboard/pages/home-vecsa/children/communityevents/communityevents.component';
import { CalendaryComponent } from './dashboard/pages/home-vecsa/children/calendary/calendary.component';
import { ModaleventsComponent } from './dashboard/pages/home-vecsa/children/modalevents/modalevents.component';
import { FormRiderComponent } from './dashboard/pages/home-vecsa/pages/form-rider/form-rider.component';
import { SurveyRiderComponent } from './dashboard/pages/home-vecsa/survey-rider/survey-rider.component';
import { RequestQuoteModule } from './dashboard/pages/request-quote/request-quote.module';
import { BarChartComponent } from './shared/bar-chart/bar-chart.component';
import { RidersComponent } from './dashboard/pages/riders/riders.component';
import { NewNavComponent } from './shared/versiones-nav/new-nav/new-nav.component';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { CarCareComponent } from './dashboard/pages/car-care/car-care.component';
import { NavPromotionsComponent } from './shared/versiones-nav/nav-promotions/nav-promotions.component';
import { NavCarcareComponent } from './shared/versiones-nav/nav-carcare/nav-carcare.component';
import { NavRewardsComponent } from './shared/versiones-nav/nav-rewards/nav-rewards.component';
import { SwiperComponent } from './shared/components/swiper/swiper.component';



@NgModule({
  declarations: [
    AppComponent,
    NotFoundComponent,
    FooterComponent,
    HomeVecsaComponent,
    TeamComponent,
    CtComponent,
    CarruselComponent,
    CardshopComponent,
    PromotionsComponent,
    LifestyleComponent,
    ProductComponent,
    FormLeadsComponent,
    CardscontactoComponent,
    MapComponent,
    EventsComponent,
    BannerComponent,
    PromotionscardComponent,
    CommunityComponent,
    CommunitycardComponent,
    CommunityvideoComponent,
    CommunityeventsComponent,
    CalendaryComponent,
    ModaleventsComponent,
    FormRiderComponent,
    SurveyRiderComponent,
    BarChartComponent,
    RidersComponent,
    CarCareComponent,
    SwiperComponent,
  ],
  exports:[
    PromotionscardComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FormsModule, 
    ReactiveFormsModule,
    AngularMaterialModule,
    AuthRoutingModule,
    AuthModule,
    ComprarAutosRoutingModule,  
    ComprarAutosModule ,
    BrowserAnimationsModule,
    MatSliderModule,
    RequestQuoteModule,
    DragDropModule,
    NavPromotionsComponent,
    NavCarcareComponent,
    NavRewardsComponent,
    NewNavComponent,
  ],
  providers: [Title], 
  bootstrap: [AppComponent],
  schemas:[CUSTOM_ELEMENTS_SCHEMA]
})

export class AppModule { }