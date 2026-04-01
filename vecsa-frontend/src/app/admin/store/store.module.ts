import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { StoreRoutingModule } from './store-routing.module';
import { StoreLayoutComponent } from './pages/layout/store-layout.component';

@NgModule({
  declarations: [StoreLayoutComponent],
  imports: [CommonModule, FormsModule, RouterModule, StoreRoutingModule],
})
export class StoreModule {}
