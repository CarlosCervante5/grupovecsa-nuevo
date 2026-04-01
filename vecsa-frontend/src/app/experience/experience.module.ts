import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { ExperienceRoutingModule } from './experience-routing.module';
import { ExperienceHomeComponent } from './pages/experience-home/experience-home.component';

@NgModule({
  declarations: [ExperienceHomeComponent],
  imports: [CommonModule, RouterModule, ExperienceRoutingModule]
})
export class ExperienceModule {}
