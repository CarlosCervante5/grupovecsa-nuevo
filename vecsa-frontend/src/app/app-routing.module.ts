import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { NotFoundComponent } from './shared/not-found/not-found.component';
import { CompraTuAutoComponent } from './dashboard/pages/comprar-autos/pages/compra-tu-auto/compra-tu-auto.component';
import { EventsComponent } from './dashboard/pages/home-vecsa/pages/events/events.component';
import { CommunityComponent } from './dashboard/pages/home-vecsa/pages/community/community.component';
import { ConditionsComponent } from './dashboard/pages/comprar-autos/pages/conditions/conditions.component';
import { FormRiderComponent } from './dashboard/pages/home-vecsa/pages/form-rider/form-rider.component';
import { QuoteRequestComponent } from './dashboard/pages/request-quote/components/quote-request/quote-request.component';
import { RidersComponent } from './dashboard/pages/riders/riders.component';
import { CarCareComponent } from './dashboard/pages/car-care/car-care.component';


const routes: Routes = [
  // { path: '', component: RidersComponent},
  { path: '', loadChildren: () => import('./dashboard/pages/comprar-autos/comprar-autos.module').then(m => m.ComprarAutosModule) },
  { path: 'promotions', component: EventsComponent },
  { path: 'riders', component: RidersComponent },
  { path: 'carcare', component: CarCareComponent },
  { path: 'community', component: CommunityComponent },
  { path: '404', component: NotFoundComponent },
  { path: 'auth', loadChildren: () => import('./auth/auth.module').then(m => m.AuthModule) },
  { path: 'admin', loadChildren: () => import('./admin/admin.module').then(m => m.AdminModule) },
  { path: 'vender-autos', loadChildren: () => import('./dashboard/pages/vender-autos/vender-autos.module').then(m => m.VenderAutosModule) },
  { path: 'terminos-y-condiciones', component: ConditionsComponent },  
  { path: 'landing', loadChildren: () => import('./landings/landings.module').then(m => m.LandingsModule) },
  { path: 'formrider', component: FormRiderComponent },
  { path: 'form-prospection', component: QuoteRequestComponent},
  { path: '**', redirectTo: '404' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})

export class AppRoutingModule { }