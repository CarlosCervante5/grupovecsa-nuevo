import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ExperienceHomeComponent } from './pages/experience-home/experience-home.component';
import { StoryDetailComponent } from './pages/story-detail/story-detail.component';

const routes: Routes = [
  { path: '', component: ExperienceHomeComponent },
  { path: 'historia/:slug', component: StoryDetailComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ExperienceRoutingModule {}
