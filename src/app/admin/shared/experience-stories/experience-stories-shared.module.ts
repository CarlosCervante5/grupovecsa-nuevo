import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AngularMaterialModule } from 'src/app/angular-material/angular-material.module';
import { ExperienceStoriesComponent } from '../../marketing/pages/experience-stories/experience-stories.component';

/**
 * Vista compartida: historias Experience + importación WordPress.
 * Usada en Marketing y en Administrador.
 */
@NgModule({
  declarations: [ExperienceStoriesComponent],
  imports: [CommonModule, FormsModule, AngularMaterialModule],
  exports: [ExperienceStoriesComponent],
})
export class ExperienceStoriesSharedModule {}
