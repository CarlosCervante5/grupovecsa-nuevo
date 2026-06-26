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
import { BoutiqueAdminInventoryService } from '../../services/boutique-admin-inventory.service';
import {
  BoutiqueProduct,
  BoutiqueCategory,
  BoutiqueInventoryMovement
} from '../../../../../../boutique/interfaces/boutique.interfaces';
import { BoutiqueAdminCategoryService } from '../../services/boutique-admin-category.service';
import { formatCategoryPath } from '../../../../../../boutique/utils/boutique-category-tree.util';
import { InventoryAlertComponent } from '../../components/inventory-alert/inventory-alert.component';
import { reload } from '@helpers/session.helper';

@Component({
  selector: 'app-inventory',
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
    InventoryAlertComponent,
  ],
  templateUrl: './inventory.component.html',
  styleUrls: ['./inventory.component.css']
})
export class InventoryComponent implements OnInit {

  displayedColumns: string[] = ['name', 'sku', 'category', 'price', 'stock', 'status', 'actions'];
  products: BoutiqueProduct[] = [];
  categories: BoutiqueCategory[] = [];
  loading = true;
  search = '';
  selectedCategoryUuid = '';

  // Pagination
  totalItems = 0;
  pageSize = 15;
  currentPage = 1;

  constructor(
    private _productService: BoutiqueAdminProductService,
    private _inventoryService: BoutiqueAdminInventoryService,
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

  openAdjustDialog(product: BoutiqueProduct): void {
    const dialogRef = this._dialog.open(StockAdjustDialogComponent, {
      width: '440px',
      data: { product }
    });
    dialogRef.afterClosed().subscribe((result) => {
      if (result) {
        this._inventoryService.update({
          product_uuid: product.uuid,
          new_stock: result.newStock,
          reason: result.reason
        }).subscribe({
          next: () => {
            this.showSnackBar('Inventario actualizado correctamente');
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

  openMovementsDialog(product: BoutiqueProduct): void {
    this._dialog.open(MovementsDialogComponent, {
      width: '700px',
      maxHeight: '80vh',
      data: { product, inventoryService: this._inventoryService }
    });
  }

  formatProductCategory(product: BoutiqueProduct): string {
    return formatCategoryPath(product.category);
  }

  isLowStock(stock: number): boolean {
    return stock <= 5;
  }

  getLowStockCount(): number {
    return this.products.filter(p => p.stock <= 5 && p.stock > 0).length;
  }

  getOutOfStockCount(): number {
    return this.products.filter(p => p.stock === 0).length;
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


// ─── Stock Adjust Dialog ──────────────────────────────────────────────

@Component({
  selector: 'app-stock-adjust-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatDialogModule,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
    MatIconModule,
  ],
  template: `
    <h2 mat-dialog-title>Ajustar inventario</h2>
    <mat-dialog-content>
      <div class="product-info">
        <span class="product-name">{{ data.product.name }}</span>
        <span class="product-sku">SKU: {{ data.product.sku }}</span>
        <span class="current-stock">Stock actual: <strong>{{ data.product.stock }}</strong></span>
      </div>
      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Nuevo stock</mat-label>
        <input matInput type="number" [(ngModel)]="newStock" min="0" required>
      </mat-form-field>
      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Razón del ajuste</mat-label>
        <textarea matInput [(ngModel)]="reason" rows="3" required placeholder="Ej: Reabastecimiento, corrección de conteo..."></textarea>
      </mat-form-field>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="onCancel()">Cancelar</button>
      <button mat-flat-button color="primary" (click)="onConfirm()" [disabled]="!isValid()">Guardar</button>
    </mat-dialog-actions>
  `,
  styles: [`
    .product-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 16px;
      padding: 12px;
      background-color: #374151;
      border-radius: 8px;
    }
    .product-name { font-weight: 600; color: #f9fafb; }
    .product-sku { font-size: 0.8rem; color: #9ca3af; }
    .current-stock { font-size: 0.9rem; color: #93c5fd; }
    .full-width { width: 100%; }
  `]
})
export class StockAdjustDialogComponent {
  newStock: number;
  reason = '';

  constructor(
    private _dialogRef: MatDialogRef<StockAdjustDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { product: BoutiqueProduct }
  ) {
    this.newStock = data.product.stock;
  }

  isValid(): boolean {
    return this.newStock >= 0 && this.reason.trim().length > 0;
  }

  onCancel(): void {
    this._dialogRef.close(null);
  }

  onConfirm(): void {
    this._dialogRef.close({ newStock: this.newStock, reason: this.reason.trim() });
  }
}

// ─── Movements History Dialog ─────────────────────────────────────────

@Component({
  selector: 'app-movements-dialog',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatTableModule,
  ],
  template: `
    <h2 mat-dialog-title>Historial de movimientos</h2>
    <mat-dialog-content>
      <div class="product-info">
        <span class="product-name">{{ data.product.name }}</span>
        <span class="product-sku">SKU: {{ data.product.sku }} · Stock actual: {{ data.product.stock }}</span>
      </div>

      <div *ngIf="loading" class="loading-container">
        <mat-spinner diameter="32"></mat-spinner>
      </div>

      <div *ngIf="!loading && movements.length === 0" class="empty-state">
        <mat-icon>inventory_2</mat-icon>
        <p>No hay movimientos registrados</p>
      </div>

      <table *ngIf="!loading && movements.length > 0" mat-table [dataSource]="movements" class="movements-table">
        <ng-container matColumnDef="date">
          <th mat-header-cell *matHeaderCellDef>Fecha</th>
          <td mat-cell *matCellDef="let m">{{ m.created_at | date:'dd/MM/yyyy HH:mm' }}</td>
        </ng-container>

        <ng-container matColumnDef="change">
          <th mat-header-cell *matHeaderCellDef>Cambio</th>
          <td mat-cell *matCellDef="let m">
            <span [class]="m.quantity_change > 0 ? 'change-positive' : 'change-negative'">
              {{ m.quantity_change > 0 ? '+' : '' }}{{ m.quantity_change }}
            </span>
          </td>
        </ng-container>

        <ng-container matColumnDef="stock">
          <th mat-header-cell *matHeaderCellDef>Stock</th>
          <td mat-cell *matCellDef="let m">{{ m.previous_stock }} → {{ m.new_stock }}</td>
        </ng-container>

        <ng-container matColumnDef="reason">
          <th mat-header-cell *matHeaderCellDef>Razón</th>
          <td mat-cell *matCellDef="let m">{{ m.reason }}</td>
        </ng-container>

        <tr mat-header-row *matHeaderRowDef="movementColumns"></tr>
        <tr mat-row *matRowDef="let row; columns: movementColumns;"></tr>
      </table>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button mat-dialog-close>Cerrar</button>
    </mat-dialog-actions>
  `,
  styles: [`
    .product-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 16px;
      padding: 12px;
      background-color: #374151;
      border-radius: 8px;
    }
    .product-name { font-weight: 600; color: #f9fafb; }
    .product-sku { font-size: 0.8rem; color: #9ca3af; }
    .loading-container {
      display: flex;
      justify-content: center;
      padding: 32px 0;
    }
    .empty-state {
      text-align: center;
      padding: 32px 0;
      color: #9ca3af;
    }
    .empty-state mat-icon {
      font-size: 48px;
      width: 48px;
      height: 48px;
      margin-bottom: 8px;
    }
    .movements-table { width: 100%; }
    .change-positive { color: #6ee7b7; font-weight: 600; }
    .change-negative { color: #fca5a5; font-weight: 600; }

    :host ::ng-deep .mat-mdc-header-cell {
      background-color: #374151 !important;
      color: #d1d5db !important;
      font-weight: 600;
      border-bottom-color: #4b5563 !important;
    }
    :host ::ng-deep .mat-mdc-cell {
      color: #f9fafb !important;
      border-bottom-color: #374151 !important;
    }
    :host ::ng-deep .mat-mdc-row {
      background-color: #1f2937 !important;
    }
    :host ::ng-deep .mat-mdc-row:hover {
      background-color: #283548 !important;
    }
  `]
})
export class MovementsDialogComponent implements OnInit {
  movements: BoutiqueInventoryMovement[] = [];
  movementColumns = ['date', 'change', 'stock', 'reason'];
  loading = true;

  constructor(
    @Inject(MAT_DIALOG_DATA) public data: {
      product: BoutiqueProduct;
      inventoryService: BoutiqueAdminInventoryService;
    }
  ) {}

  ngOnInit(): void {
    this.data.inventoryService.movements({
      product_uuid: this.data.product.uuid
    }).subscribe({
      next: (response) => {
        this.movements = (response.data as any).movements || response.data as any;
        this.loading = false;
      },
      error: () => {
        this.loading = false;
      }
    });
  }
}
