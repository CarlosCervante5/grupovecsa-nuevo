import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ExperienceRoutingModule } from './experience-routing.module';
import { ExperienceHomeComponent } from './pages/experience-home/experience-home.component';
import { StoryDetailComponent } from './pages/story-detail/story-detail.component';
import { GalleryDetailComponent } from './pages/gallery-detail/gallery-detail.component';
import { AngularMaterialModule } from '../angular-material/angular-material.module';

@NgModule({
  declarations: [ExperienceHomeComponent, StoryDetailComponent, GalleryDetailComponent],
  imports: [CommonModule, RouterModule, ExperienceRoutingModule, AngularMaterialModule],
})
export class ExperienceModule {}
