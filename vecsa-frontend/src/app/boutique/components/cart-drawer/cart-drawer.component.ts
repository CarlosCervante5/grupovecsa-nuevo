import { Component, OnInit, OnDestroy, Output, EventEmitter, HostListener } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { BoutiqueCartService } from '../../services/boutique-cart.service';
import { BoutiqueCart, BoutiqueCartItem } from '../../interfaces/boutique.interfaces';

@Component({
  selector: 'app-cart-drawer',
  standalone: true,
  imports: [CommonModule, RouterModule, CurrencyPipe],
  templateUrl: './cart-drawer.component.html',
  styleUrls: ['./cart-drawer.component.css'],
})
export class CartDrawerComponent implements OnInit, OnDestroy {
  @Output() close = new EventEmitter<void>();

  cart: BoutiqueCart | null = null;
  isLoading = true;
  removingItems = new Set<string>();

  private cartSub?: Subscription;

  constructor(
    private cartService: BoutiqueCartService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.loadCart();
  }

  ngOnDestroy(): void {
    this.cartSub?.unsubscribe();
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
      error: () => { this.isLoading = false; },
    });
  }

  removeItem(item: BoutiqueCartItem): void {
    this.removingItems.add(item.uuid);
    this.cartService.remove(item.uuid).subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        this.cart = this.extractCart(wrapper);
        this.removingItems.delete(item.uuid);
      },
      error: () => { this.removingItems.delete(item.uuid); },
    });
  }

  goToCart(): void {
    this.close.emit();
    this.router.navigate(['/boutique/cart']);
  }

  goToCheckout(): void {
    this.close.emit();
    this.router.navigate(['/boutique/checkout']);
  }

  getItemImage(item: BoutiqueCartItem): string {
    return item.product?.images?.[0]?.image_path || 'assets/images/placeholder-product.svg';
  }

  get isEmpty(): boolean {
    return !this.cart?.items?.length;
  }

  get total(): number {
    return this.cart?.total ?? 0;
  }

  get itemCount(): number {
    return this.cart?.items?.reduce((s, i) => s + i.quantity, 0) ?? 0;
  }

  @HostListener('document:keydown.escape')
  onEscape(): void { this.close.emit(); }

  private extractCart(wrapper: any): BoutiqueCart {
    if (wrapper?.items !== undefined && wrapper?.total !== undefined) {
      return { uuid: wrapper.cart?.uuid || 'local', items: wrapper.items || [], total: wrapper.total || 0 };
    }
    return wrapper;
  }
}
