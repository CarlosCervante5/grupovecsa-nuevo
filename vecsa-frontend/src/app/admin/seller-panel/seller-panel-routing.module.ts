import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { SellerLayoutComponent } from './pages/layout/seller-layout.component';
import { SellerValuationsComponent } from './pages/valuations/seller-valuations.component';

const routes: Routes = [
  {
    path: '',
    component: SellerLayoutComponent,
    children: [{ path: '', component: SellerValuationsComponent }],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class SellerPanelRoutingModule {}
