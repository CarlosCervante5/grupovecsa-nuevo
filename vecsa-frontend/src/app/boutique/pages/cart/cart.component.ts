import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Subject, Subscription } from 'rxjs';
import { debounceTime, switchMap } from 'rxjs/operators';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';

import { BoutiqueCartService } from '../../services/boutique-cart.service';
import { BoutiqueCart, BoutiqueCartItem } from '../../interfaces/boutique.interfaces';

@Component({
  selector: 'app-cart',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    FormsModule,
    CurrencyPipe,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
  ],
  templateUrl: './cart.component.html',
  styleUrls: ['./cart.component.css'],
})
export class CartComponent implements OnInit, OnDestroy {
  cart: BoutiqueCart | null = null;
  isLoading = true;
  updatingItems = new Set<string>();

  private quantityChange$ = new Subject<{ item_uuid: string; quantity: number }>();
  private quantitySub?: Subscription;
  private cartSub?: Subscription;

  constructor(
    private cartService: BoutiqueCartService,
    private snackBar: MatSnackBar,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadCart();
    this.quantitySub = this.quantityChange$
      .pipe(
        debounceTime(500),
        switchMap(({ item_uuid, quantity }) => {
          this.updatingItems.add(item_uuid);
          return this.cartService.update(item_uuid, quantity);
        })
      )
      .subscribe({
        next: (res) => {
          const wrapper = res.data as any;
          this.cart = this.extractCart(wrapper);
          this.updatingItems.clear();
        },
        error: (err) => {
          this.updatingItems.clear();
          this.snackBar.open('Error al actualizar cantidad', 'Cerrar', { duration: 3000 });
          this.loadCart();
        },
      });
  }

  ngOnDestroy(): void {
    this.cartSub?.unsubscribe();
    this.quantitySub?.unsubscribe();
  }

  loadCart(): void {
    this.isLoading = true;
    this.cartSub?.unsubscribe();
    this.cartSub = this.cartService.get().subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        this.cart = this.extractCart(wrapper);
        this.isLoading = false;
      },
      error: (err) => {
        this.isLoading = false;
        this.snackBar.open('Error al cargar el carrito', 'Cerrar', { duration: 3000 });
      },
    });
  }

  onQuantityChange(item: BoutiqueCartItem, event: Event): void {
    const input = event.target as HTMLInputElement;
    let quantity = parseInt(input.value, 10);
    if (isNaN(quantity) || quantity < 1) {
      quantity = 1;
    }
    if (quantity > item.product.stock) {
      quantity = item.product.stock;
    }
    input.value = String(quantity);
    this.quantityChange$.next({ item_uuid: item.uuid, quantity });
  }

  incrementQty(item: BoutiqueCartItem): void {
    const newQty = Math.min(item.quantity + 1, item.product.stock);
    if (newQty === item.quantity) return;
    item.quantity = newQty;
    this.quantityChange$.next({ item_uuid: item.uuid, quantity: newQty });
  }

  decrementQty(item: BoutiqueCartItem): void {
    const newQty = Math.max(item.quantity - 1, 1);
    if (newQty === item.quantity) return;
    item.quantity = newQty;
    this.quantityChange$.next({ item_uuid: item.uuid, quantity: newQty });
  }

  removeItem(item: BoutiqueCartItem): void {
    this.updatingItems.add(item.uuid);
    this.cartService.remove(item.uuid).subscribe({
      next: (res) => {
        if (res.data) {
          const wrapper = res.data as any;
          this.cart = this.extractCart(wrapper);
        } else {
          // remove endpoint may not return cart data, reload
          this.loadCart();
        }
        this.updatingItems.delete(item.uuid);
        this.snackBar.open('Producto eliminado del carrito', 'Cerrar', { duration: 2000 });
      },
      error: () => {
        this.updatingItems.delete(item.uuid);
        this.snackBar.open('Error al eliminar producto', 'Cerrar', { duration: 3000 });
      },
    });
  }

  proceedToCheckout(): void {
    this.router.navigate(['/boutique/checkout']);
  }

  getItemImage(item: BoutiqueCartItem): string {
    if (item.product.images && item.product.images.length > 0) {
      return item.product.images[0].image_path;
    }
    return 'assets/images/placeholder-product.svg';
  }

  get totalItems(): number {
    if (!this.cart || !this.cart.items) return 0;
    return this.cart.items.reduce((sum, item) => sum + item.quantity, 0);
  }

  get isEmpty(): boolean {
    return !this.cart || !this.cart.items || this.cart.items.length === 0;
  }

  private extractCart(wrapper: any): BoutiqueCart {
    // Backend returns { cart: {...}, items: [...], total: N }
    // We need to map it to BoutiqueCart interface
    if (wrapper && wrapper.items !== undefined && wrapper.total !== undefined) {
      return {
        uuid: wrapper.cart?.uuid || '',
        items: wrapper.items || [],
        total: wrapper.total || 0,
      };
    }
    // Fallback: already a BoutiqueCart shape
    return wrapper;
  }
}
