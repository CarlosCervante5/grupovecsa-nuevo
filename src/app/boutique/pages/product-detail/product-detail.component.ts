import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { Subscription } from 'rxjs';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { BoutiqueCatalogService } from '../../services/boutique-catalog.service';
import { BoutiqueCartService } from '../../services/boutique-cart.service';
import { BoutiqueProduct, BoutiqueProductVariant, BoutiqueColorOption } from '../../interfaces/boutique.interfaces';
import { ProductCardComponent } from '../../components/product-card/product-card.component';

@Component({
  selector: 'app-product-detail',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    FormsModule,
    CurrencyPipe,
    MatButtonModule,
    MatIconModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    ProductCardComponent,
  ],
  templateUrl: './product-detail.component.html',
  styleUrls: ['./product-detail.component.css'],
})
export class ProductDetailComponent implements OnInit, OnDestroy {
  product: BoutiqueProduct | null = null;
  relatedProducts: BoutiqueProduct[] = [];
  isLoading = true;
  hasError = false;
  addingToCart = false;
  justAdded = false;

  private justAddedTimer?: ReturnType<typeof setTimeout>;

  selectedImageIndex = 0;
  quantity = 1;

  // Variants
  selectedColor: string = '';
  selectedSize: string = '';
  availableColors: BoutiqueColorOption[] = [];
  availableSizes: string[] = [];
  variants: BoutiqueProductVariant[] = [];

  private routeSub?: Subscription;
  private detailSub?: Subscription;
  private cartSub?: Subscription;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private catalogService: BoutiqueCatalogService,
    private cartService: BoutiqueCartService,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.routeSub = this.route.params.subscribe((params) => {
      const uuid = params['uuid'];
      if (uuid) {
        this.loadProduct(uuid);
      }
    });
  }

  ngOnDestroy(): void {
    this.routeSub?.unsubscribe();
    this.detailSub?.unsubscribe();
    this.cartSub?.unsubscribe();
    if (this.justAddedTimer) clearTimeout(this.justAddedTimer);
  }

  loadProduct(uuid: string): void {
    this.isLoading = true;
    this.hasError = false;
    this.selectedImageIndex = 0;
    this.quantity = 1;

    this.detailSub?.unsubscribe();
    this.detailSub = this.catalogService.detail(uuid).subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        this.product = wrapper.product || res.data;
        this.relatedProducts = (wrapper.related_products || wrapper.related || []).slice(0, 4);
        this.initVariants(wrapper.variants || this.product?.variants || []);
        this.isLoading = false;
      },
      error: () => {
        this.hasError = true;
        this.isLoading = false;
      },
    });
  }

  get mainImage(): string {
    if (this.product?.images && this.product.images.length > 0) {
      return this.product.images[this.selectedImageIndex]?.image_path ?? '';
    }
    return '';
  }

  get hasImages(): boolean {
    return !!this.product?.images && this.product.images.length > 0;
  }

  get isLoggedIn(): boolean {
    return !!localStorage.getItem('user_token');
  }

  get isOutOfStock(): boolean {
    return this.product?.stock === 0;
  }

  selectImage(index: number): void {
    this.selectedImageIndex = index;
  }

  onQuantityChange(): void {
    if (!this.product) return;
    if (this.quantity < 1) this.quantity = 1;
    if (this.quantity > this.product.stock) this.quantity = this.product.stock;
  }

  addToCart(): void {
    if (!this.product || this.isOutOfStock || this.addingToCart) return;

    if (this.variantRequired) {
      this.snackBar.open('Selecciona talla y color antes de agregar', 'OK', { duration: 3000 });
      return;
    }

    this.addingToCart = true;
    this.justAdded = false;
    this.cartSub?.unsubscribe();

    const variantUuid = this.variants.find(v =>
      (!this.selectedColor || v.color === this.selectedColor) &&
      (!this.selectedSize  || v.size  === this.selectedSize)
    )?.uuid;

    // Pass product snapshot so guest (local) cart can display product info
    this.cartSub = this.cartService.add(this.product.uuid, this.quantity, variantUuid, this.product).subscribe({
      next: () => {
        this.addingToCart = false;
        this.justAdded = true;
        if (this.justAddedTimer) clearTimeout(this.justAddedTimer);
        this.justAddedTimer = setTimeout(() => { this.justAdded = false; }, 4000);
        this.snackBar.open('Producto agregado al carrito', 'Ver carrito', {
          duration: 4000,
          panelClass: ['snackbar-success'],
        }).onAction().subscribe(() => {
          this.router.navigate(['/boutique/cart']);
        });
      },
      error: () => {
        this.addingToCart = false;
        this.snackBar.open('Error al agregar al carrito', 'Cerrar', {
          duration: 4000,
          panelClass: ['snackbar-error'],
        });
      },
    });
  }

  goBack(): void {
    this.router.navigate(['/boutique']);
  }

  // ── Variants ──────────────────────────────────────────────────────────────

  get hasColors(): boolean { return this.availableColors.length > 0; }
  get hasSizes(): boolean  { return this.availableSizes.length > 0; }

  private initVariants(variants: BoutiqueProductVariant[]): void {
    this.variants = variants;
    if (!variants.length) return;

    // Unique colors
    const colorMap = new Map<string, string>();
    variants.forEach(v => {
      if (v.color && v.color_hex) colorMap.set(v.color, v.color_hex);
    });
    this.availableColors = Array.from(colorMap.entries()).map(([name, hex]) => ({ name, hex }));

    // Unique sizes
    const sizes = [...new Set(variants.filter(v => v.size).map(v => v.size!))];
    this.availableSizes = sizes;

    // Default selections
    if (this.availableColors.length) this.selectedColor = this.availableColors[0].name;
    if (this.availableSizes.length)  this.selectedSize  = this.availableSizes[0];
  }

  selectColor(color: string): void { this.selectedColor = color; }
  selectSize(size: string): void   { this.selectedSize  = size; }

  isSizeAvailable(size: string): boolean {
    if (!this.variants.length) return true;
    return this.variants.some(v =>
      v.size === size &&
      (!this.selectedColor || v.color === this.selectedColor) &&
      v.stock > 0
    );
  }

  get variantRequired(): boolean {
    return (this.hasColors && !this.selectedColor) || (this.hasSizes && !this.selectedSize);
  }
}
