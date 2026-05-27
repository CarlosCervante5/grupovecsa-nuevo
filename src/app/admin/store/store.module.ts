import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { StoreRoutingModule } from './store-routing.module';
import { StoreLayoutComponent } from './pages/layout/store-layout.component';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { ImageAiDialogComponent } from 'src/app/shared/components/image-ai-dialog/image-ai-dialog.component';

@NgModule({
  declarations: [StoreLayoutComponent],
  imports: [CommonModule, FormsModule, RouterModule, StoreRoutingModule, AngularMaterialModule, ImageAiDialogComponent],
})
export class StoreModule {}
