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
  { path: '', loadComponent: () => import('./home/home.component').then(m => m.HomeComponent) },
  { path: 'compra-tu-auto', loadChildren: () => import('./dashboard/pages/comprar-autos/comprar-autos.module').then(m => m.ComprarAutosModule) },
  { path: 'promociones', component: EventsComponent },
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
  { path: 'boutique', loadChildren: () => import('./boutique/boutique.module').then(m => m.BoutiqueModule) },
  { path: 'rewards', loadChildren: () => import('./rewards/rewards.module').then(m => m.RewardsModule) },
  { path: 'experience', loadChildren: () => import('./experience/experience.module').then(m => m.ExperienceModule) },
  {
    path: 'aviso-privacidad',
    loadComponent: () => import('./legal/legal-page.component').then((m) => m.LegalPageComponent),
    data: {
      legalAsset: 'aviso-privacidad',
      pageTitle: 'Aviso de Privacidad | Grupo VECSA',
      metaDescription: 'Información sobre el tratamiento y protección de sus datos personales en Grupo VECSA.',
    },
  },
  {
    path: 'condiciones-uso',
    loadComponent: () => import('./legal/legal-page.component').then((m) => m.LegalPageComponent),
    data: {
      legalAsset: 'condiciones-uso',
      pageTitle: 'Condiciones de Uso | Grupo VECSA',
      metaDescription: 'Términos y condiciones de uso de los sitios y servicios de Grupo VECSA.',
    },
  },
  {
    path: 'politicas-devolucion',
    loadComponent: () => import('./legal/legal-page.component').then((m) => m.LegalPageComponent),
    data: {
      legalAsset: 'politicas-devolucion',
      pageTitle: 'Políticas de Devolución | Grupo VECSA',
      metaDescription: 'Políticas de devolución y reembolso de productos y servicios de Grupo VECSA.',
    },
  },
  {
    path: 'uso-cookies',
    loadComponent: () => import('./legal/legal-page.component').then((m) => m.LegalPageComponent),
    data: {
      legalAsset: 'uso-cookies',
      pageTitle: 'Uso de Cookies | Grupo VECSA',
      metaDescription: 'Información sobre cookies y tecnologías de rastreo en el sitio de Grupo VECSA.',
    },
  },
  { path: '**', redirectTo: '404' },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})

export class AppRoutingModule { }