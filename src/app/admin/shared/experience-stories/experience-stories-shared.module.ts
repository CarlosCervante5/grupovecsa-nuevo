import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { ExperienceStoriesComponent } from '../../marketing/pages/experience-stories/experience-stories.component';
import { ExperienceStoryFormDialogComponent } from '../../marketing/pages/experience-stories/experience-story-form-dialog.component';

/**
 * Vista compartida: historias Experience + importación WordPress.
 * Usada en Marketing y en Administrador.
 */
@NgModule({
  declarations: [ExperienceStoriesComponent, ExperienceStoryFormDialogComponent],
  imports: [CommonModule, FormsModule, AngularMaterialModule],
  exports: [ExperienceStoriesComponent],
})
export class ExperienceStoriesSharedModule {}
