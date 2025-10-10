import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

// Components
import { SettingsComponent } from './pages/settings/settings.component';
// import { AssistsComponent } from './pages/assists/assists.component';
// import { IncidentsComponent } from './pages/incidents/incidents.component';
// import { MyCarsComponent } from './pages/my-cars/my-cars.component';
// import { MyServicesComponent } from './pages/my-services/my-services.component';
import { ProfileComponent } from './pages/profile/profile.component';
// import { RewardsComponent } from './pages/rewards/rewards.component';
// import { FinancingComponent } from './pages/financing/financing.component';
// import { ReservedComponent } from './pages/reserved/reserved.component';

// Guard
import { CustomerGuard } from './guards/customer.guard';

const routes: Routes = [
  { path: '', component: ProfileComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  { path: 'ajustes', component: SettingsComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'autos', component: MyCarsComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'servicios', component: MyServicesComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'incidencias', component: IncidentsComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'asistencias', component: AssistsComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'recompensas', component: RewardsComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'financiamientos', component: FinancingComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  // { path: 'apartados', component: ReservedComponent, canActivate: [CustomerGuard], canLoad: [CustomerGuard] },
  { path: '**', pathMatch: 'full', redirectTo: ''}
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})

export class AccountRoutingModule { }
