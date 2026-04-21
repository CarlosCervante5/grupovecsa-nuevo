import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { BoutiqueAuthGuard } from './guards/boutique-auth.guard';

const routes: Routes = [
  { path: '', loadComponent: () => import('./pages/catalog/catalog.component').then(m => m.CatalogComponent) },
  { path: 'shop', loadComponent: () => import('./pages/shop/shop.component').then(m => m.ShopComponent) },
  { path: 'shop/:categoryUuid', loadComponent: () => import('./pages/shop/shop.component').then(m => m.ShopComponent) },
  { path: 'product/:uuid', loadComponent: () => import('./pages/product-detail/product-detail.component').then(m => m.ProductDetailComponent) },
  { path: 'cart', loadComponent: () => import('./pages/cart/cart.component').then(m => m.CartComponent) },
  { path: 'checkout', loadComponent: () => import('./pages/checkout/checkout.component').then(m => m.CheckoutComponent) },
  {
    path: 'gracias/:uuid',
    loadComponent: () =>
      import('./pages/guest-order-thanks/guest-order-thanks.component').then((m) => m.GuestOrderThanksComponent),
  },
  { path: 'orders', loadComponent: () => import('./pages/order-history/order-history.component').then(m => m.OrderHistoryComponent), canActivate: [BoutiqueAuthGuard] },
  { path: 'orders/:uuid', loadComponent: () => import('./pages/order-detail/order-detail.component').then(m => m.OrderDetailComponent), canActivate: [BoutiqueAuthGuard] },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class BoutiqueRoutingModule { }
