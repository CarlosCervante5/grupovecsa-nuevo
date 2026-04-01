import { Component, OnInit, OnDestroy, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, RouterModule } from '@angular/router';
import { Subscription } from 'rxjs';
import { MatIconModule } from '@angular/material/icon';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { BoutiqueCatalogService } from '../../services/boutique-catalog.service';
import { BoutiqueProduct, BoutiqueCategory } from '../../interfaces/boutique.interfaces';
import { ProductCardComponent } from '../../components/product-card/product-card.component';

@Component({
  selector: 'app-shop',
  standalone: true,
  imports: [
    CommonModule, FormsModule, RouterModule,
    MatIconModule, MatPaginatorModule,
    ProductCardComponent,
  ],
  templateUrl: './shop.component.html',
  styleUrls: ['./shop.component.css'],
})
export class ShopComponent implements OnInit, OnDestroy {
  products: BoutiqueProduct[] = [];
  categories: BoutiqueCategory[] = [];
  isLoading = true;
  sidebarOpen = false;
  expandedCategories: Record<string, boolean> = {};

  selectedCategoryUuid: string | null = null;
  searchTerm = '';
  minPrice: number | null = null;
  maxPrice: number | null = null;

  totalProducts = 0;
  pageSize = 12;
  currentPage = 1;

  private searchSub?: Subscription;
  private categoriesSub?: Subscription;
  private routeSub?: Subscription;
  private searchTimeout?: ReturnType<typeof setTimeout>;

  constructor(
    private catalogService: BoutiqueCatalogService,
    private route: ActivatedRoute,
    private cdr: ChangeDetectorRef
  ) {}

  ngOnInit(): void {
    this.loadCategories();
    this.routeSub = this.route.paramMap.subscribe(params => {
      const uuid = params.get('categoryUuid');
      this.selectedCategoryUuid = uuid ?? null;
      this.currentPage = 1;
      this.loadProducts();
    });
  }

  ngOnDestroy(): void {
    this.searchSub?.unsubscribe();
    this.categoriesSub?.unsubscribe();
    this.routeSub?.unsubscribe();
    if (this.searchTimeout) clearTimeout(this.searchTimeout);
  }

  loadCategories(): void {
    this.categoriesSub = this.catalogService.categories().subscribe({
      next: (res) => {
        const cats = res?.data?.categories;
        this.categories = Array.isArray(cats) ? [...cats] : [];
        this.cdr.detectChanges();
      },
      error: () => { this.categories = []; },
    });
  }

  loadProducts(): void {
    this.isLoading = true;
    this.searchSub?.unsubscribe();
    const params: Record<string, any> = { page: this.currentPage, per_page: this.pageSize };
    if (this.selectedCategoryUuid) params['category_uuid'] = this.selectedCategoryUuid;
    if (this.searchTerm.trim()) params['search'] = this.searchTerm.trim();
    if (this.minPrice !== null && this.minPrice >= 0) params['min_price'] = this.minPrice;
    if (this.maxPrice !== null && this.maxPrice > 0) params['max_price'] = this.maxPrice;
    this.searchSub = this.catalogService.search(params).subscribe({
      next: (res) => {
        const d = res?.data as any;
        const paginated = d?.products ?? d;
        const items = paginated?.data;
        this.products = Array.isArray(items) ? [...items] : [];
        this.totalProducts = paginated?.total ?? 0;
        this.isLoading = false;
        this.cdr.detectChanges();
      },
      error: () => {
        this.products = [];
        this.isLoading = false;
        this.cdr.detectChanges();
      },
    });
  }

  onCategorySelect(uuid: string | null): void {
    this.selectedCategoryUuid = uuid;
    this.currentPage = 1;
    this.sidebarOpen = false;
    this.loadProducts();
  }

  onSearchChange(): void {
    if (this.searchTimeout) clearTimeout(this.searchTimeout);
    this.searchTimeout = setTimeout(() => { this.currentPage = 1; this.loadProducts(); }, 400);
  }

  onPriceFilterApply(): void { this.currentPage = 1; this.loadProducts(); }

  onClearFilters(): void {
    this.selectedCategoryUuid = null;
    this.searchTerm = '';
    this.minPrice = null;
    this.maxPrice = null;
    this.currentPage = 1;
    this.loadProducts();
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadProducts();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  getCategoryIcon(name: string): string {
    const n = name.toLowerCase();
    if (n.includes('accesorio')) return 'build_circle';
    if (n.includes('clean') || n.includes('care')) return 'cleaning_services';
    if (n.includes('life') || n.includes('style')) return 'style';
    if (n.includes('llanta') || n.includes('rin')) return 'tire_repair';
    if (n.includes('rider') || n.includes('g&g') || n.includes('g & g')) return 'sports_motorsports';
    return 'category';
  }

  get hasActiveFilters(): boolean {
    return !!this.selectedCategoryUuid || !!this.searchTerm.trim() || this.minPrice !== null || this.maxPrice !== null;
  }

  get safeCategories(): BoutiqueCategory[] {
    return Array.isArray(this.categories) ? this.categories : [];
  }

  get safeProducts(): BoutiqueProduct[] {
    return Array.isArray(this.products) ? this.products : [];
  }

  get selectedCategoryName(): string {
    if (!this.selectedCategoryUuid) return 'Todos los productos';
    // Check children too
    for (const cat of this.categories) {
      if (cat.uuid === this.selectedCategoryUuid) return cat.name;
      if (cat.children) {
        const child = cat.children.find(c => c.uuid === this.selectedCategoryUuid);
        if (child) return child.name;
      }
    }
    return 'Productos';
  }

  toggleCategory(uuid: string): void {
    this.expandedCategories[uuid] = !this.expandedCategories[uuid];
  }
}
