import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { SellerPanelRoutingModule } from './seller-panel-routing.module';
import { SellerLayoutComponent } from './pages/layout/seller-layout.component';
import { SellerValuationsComponent } from './pages/valuations/seller-valuations.component';
import { AdminModule } from '../admin.module';
import { AngularMaterialModule } from '../../angular-material/angular-material.module';
import { MatTableModule } from '@angular/material/table';

@NgModule({
  declarations: [SellerLayoutComponent, SellerValuationsComponent],
  imports: [
    CommonModule,
    HttpClientModule,
    FormsModule,
    SellerPanelRoutingModule,
    AdminModule,
    AngularMaterialModule,
    MatTableModule,
  ],
})
export class SellerPanelModule {}
