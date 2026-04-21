import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialogModule, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';

import { BoutiqueAdminProductService } from '../../services/boutique-admin-product.service';
import { BoutiqueAdminCategoryService } from '../../services/boutique-admin-category.service';
import { BoutiqueProduct, BoutiqueCategory } from '../../../../../../boutique/interfaces/boutique.interfaces';
import { reload } from '@helpers/session.helper';

@Component({
  selector: 'app-products',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CurrencyPipe,
    MatTableModule,
    MatPaginatorModule,
    MatButtonModule,
    MatIconModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    MatTooltipModule,
  ],
  templateUrl: './products.component.html',
  styleUrls: ['./products.component.css']
})
export class ProductsComponent implements OnInit {

  displayedColumns: string[] = ['image', 'name', 'sku', 'category', 'price', 'stock', 'active', 'actions'];
  products: BoutiqueProduct[] = [];
  categories: BoutiqueCategory[] = [];
  loading = true;
  search = '';
  selectedCategoryUuid = '';

  // Pagination
  totalItems = 0;
  pageSize = 10;
  currentPage = 1;

  constructor(
    private _productService: BoutiqueAdminProductService,
    private _categoryService: BoutiqueAdminCategoryService,
    private _dialog: MatDialog,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {}

  ngOnInit(): void {
    this.loadCategories();
    this.loadProducts();
  }

  loadCategories(): void {
    this._categoryService.search({ page: 1, per_page: 500 }).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const categories = wrapper.categories || wrapper.data || wrapper;
        this.categories = Array.isArray(categories) ? categories : (categories.data || []);
      },
      error: (error) => {
        reload(error, this._router);
      }
    });
  }

  loadProducts(): void {
    this.loading = true;
    this._productService.search({
      search: this.search || undefined,
      category_uuid: this.selectedCategoryUuid || undefined,
      page: this.currentPage,
      per_page: this.pageSize
    }).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const paginated = wrapper.products || wrapper;
        this.products = paginated.data || paginated;
        this.totalItems = paginated.total ?? 0;
        this.loading = false;
      },
      error: (error) => {
        this.loading = false;
        reload(error, this._router);
      }
    });
  }

  onSearch(): void {
    this.currentPage = 1;
    this.loadProducts();
  }

  onCategoryFilter(): void {
    this.currentPage = 1;
    this.loadProducts();
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadProducts();
  }

  navigateToCreate(): void {
    this._router.navigate(['/admin/administrador/boutique/products/new']);
  }

  navigateToEdit(product: BoutiqueProduct): void {
    this._router.navigate(['/admin/administrador/boutique/products', product.uuid]);
  }

  deleteProduct(product: BoutiqueProduct): void {
    const dialogRef = this._dialog.open(ProductDeleteDialogComponent, {
      width: '400px',
      data: { product }
    });
    dialogRef.afterClosed().subscribe((confirmed) => {
      if (confirmed) {
        this._productService.delete(product.uuid).subscribe({
          next: () => {
            this.showSnackBar('Producto eliminado correctamente');
            this.loadProducts();
          },
          error: (error) => {
            if (error.error?.message) {
              this.showSnackBar(error.error.message, true);
            } else {
              reload(error, this._router);
            }
          }
        });
      }
    });
  }

  getProductThumbnail(product: BoutiqueProduct): string | null {
    if (product.images && product.images.length > 0) {
      const sorted = [...product.images].sort((a, b) => a.sort_id - b.sort_id);
      return sorted[0].image_path;
    }
    return null;
  }

  isLowStock(stock: number): boolean {
    return stock <= 5;
  }

  private showSnackBar(message: string, isError = false): void {
    this._snackBar.open(message, 'Cerrar', {
      duration: 3000,
      horizontalPosition: 'end',
      verticalPosition: 'top',
      panelClass: isError ? ['snack-error'] : ['snack-success']
    });
  }
}

// ─── Delete Confirmation Dialog ───────────────────────────────────────

@Component({
  selector: 'app-product-delete-dialog',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatButtonModule,
  ],
  template: `
    <h2 mat-dialog-title>Confirmar eliminación</h2>
    <mat-dialog-content>
      <p>¿Estás seguro de que deseas eliminar el producto <strong>{{ data.product.name }}</strong>?</p>
      <p class="warning-text">Esta acción no se puede deshacer.</p>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="onCancel()">Cancelar</button>
      <button mat-flat-button color="warn" (click)="onConfirm()">Eliminar</button>
    </mat-dialog-actions>
  `,
  styles: [`
    .warning-text { color: #ef4444; font-size: 0.85rem; margin-top: 4px; }
  `]
})
export class ProductDeleteDialogComponent {
  constructor(
    private _dialogRef: MatDialogRef<ProductDeleteDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { product: BoutiqueProduct }
  ) {}

  onCancel(): void {
    this._dialogRef.close(false);
  }

  onConfirm(): void {
    this._dialogRef.close(true);
  }
}
