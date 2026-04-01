import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ExperienceHomeComponent } from './pages/experience-home/experience-home.component';

const routes: Routes = [
  { path: '', component: ExperienceHomeComponent }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ExperienceRoutingModule {}
