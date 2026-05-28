import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ExperienceHomeComponent } from './pages/experience-home/experience-home.component';
import { StoryDetailComponent } from './pages/story-detail/story-detail.component';
import { GalleryDetailComponent } from './pages/gallery-detail/gallery-detail.component';

const routes: Routes = [
  { path: '', component: ExperienceHomeComponent },
  { path: 'historia/:slug', component: StoryDetailComponent },
  { path: 'galeria/:slug', component: GalleryDetailComponent },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class ExperienceRoutingModule {}
