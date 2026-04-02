import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { DeveloperDashboardComponent } from './pages/dashboard/dashboard.component';
import { IncadeaSyncComponent } from './pages/incadea-sync/incadea-sync.component';

const routes: Routes = [
  { path: '', component: DeveloperDashboardComponent },
  { path: 'incadea-sync', component: IncadeaSyncComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class DeveloperRoutingModule {}
