import { Component, Input } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { RouterModule } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { BoutiqueProduct } from '../../interfaces/boutique.interfaces';

@Component({
  selector: 'app-product-card',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    MatCardModule,
    MatIconModule,
    CurrencyPipe,
  ],
  templateUrl: './product-card.component.html',
  styleUrls: ['./product-card.component.css'],
})
export class ProductCardComponent {
  @Input({ required: true }) product!: BoutiqueProduct;

  get mainImage(): string {
    if (this.product.images && this.product.images.length > 0) {
      return this.product.images[0].image_path;
    }
    return 'assets/images/placeholder-product.svg';
  }

  get displayPrice(): number {
    const catalog = this.product.catalog_price;
    if (catalog != null && catalog > 0) {
      return catalog;
    }
    return this.product.price;
  }

  get isOutOfStock(): boolean {
    if (typeof this.product.in_stock === 'boolean') {
      return !this.product.in_stock;
    }
    if (this.product.catalog_stock != null) {
      return this.product.catalog_stock <= 0;
    }
    return this.product.stock <= 0;
  }
}
