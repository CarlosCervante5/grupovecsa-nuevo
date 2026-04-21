import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialogModule, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';

import { BoutiqueAdminCategoryService } from '../../services/boutique-admin-category.service';
import { BoutiqueCategory } from '../../../../../../boutique/interfaces/boutique.interfaces';
import { reload } from '@helpers/session.helper';

@Component({
  selector: 'app-categories',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatTableModule,
    MatPaginatorModule,
    MatButtonModule,
    MatIconModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSlideToggleModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    MatTooltipModule,
    MatSelectModule,
  ],
  templateUrl: './categories.component.html',
  styleUrls: ['./categories.component.css']
})
export class CategoriesComponent implements OnInit {

  displayedColumns: string[] = ['name', 'hierarchy', 'description', 'active', 'actions'];
  categories: BoutiqueCategory[] = [];
  loading = true;
  search = '';

  // Pagination
  totalItems = 0;
  pageSize = 10;
  currentPage = 1;

  constructor(
    private _categoryService: BoutiqueAdminCategoryService,
    private _dialog: MatDialog,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {}

  ngOnInit(): void {
    this.loadCategories();
  }

  loadCategories(): void {
    this.loading = true;
    this._categoryService.search({
      search: this.search || undefined,
      page: this.currentPage,
      per_page: this.pageSize
    }).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const paginated = wrapper.categories || wrapper;
        this.categories = paginated.data || paginated || [];
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
    this.loadCategories();
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadCategories();
  }

  openCreateDialog(): void {
    const dialogRef = this._dialog.open(CategoryDialogComponent, {
      width: '500px',
      data: { category: null, allCategories: this.categories }
    });
    dialogRef.afterClosed().subscribe((result) => {
      if (result) {
        this.loadCategories();
      }
    });
  }

  openEditDialog(category: BoutiqueCategory): void {
    const dialogRef = this._dialog.open(CategoryDialogComponent, {
      width: '500px',
      data: { category, allCategories: this.categories }
    });
    dialogRef.afterClosed().subscribe((result) => {
      if (result) {
        this.loadCategories();
      }
    });
  }

  deleteCategory(category: BoutiqueCategory): void {
    const dialogRef = this._dialog.open(CategoryDeleteDialogComponent, {
      width: '400px',
      data: { category }
    });
    dialogRef.afterClosed().subscribe((confirmed) => {
      if (confirmed) {
        this._categoryService.delete(category.uuid).subscribe({
          next: () => {
            this.showSnackBar('Categoría eliminada correctamente');
            this.loadCategories();
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

  truncateText(text: string | null, maxLength: number = 60): string {
    if (!text) return '—';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
  }

  categoryHierarchyLabel(c: BoutiqueCategory): string {
    if (c.parent?.name) {
      return `${c.parent.name} › ${c.name}`;
    }
    return c.name;
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

// ─── Create/Edit Dialog ───────────────────────────────────────────────

@Component({
  selector: 'app-category-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatSlideToggleModule,
    MatProgressSpinnerModule,
    MatSelectModule,
  ],
  template: `
    <h2 mat-dialog-title>{{ data.category ? 'Editar categoría' : 'Nueva categoría' }}</h2>
    <mat-dialog-content>
      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Nombre</mat-label>
        <input matInput [(ngModel)]="name" required placeholder="Nombre de la categoría">
      </mat-form-field>

      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Categoría padre (subcategoría)</mat-label>
        <mat-select [(ngModel)]="parentUuid">
          <mat-option [value]="''">(Raíz — sin padre)</mat-option>
          <mat-option *ngFor="let opt of parentSelectOptions" [value]="opt.uuid">{{ opt.label }}</mat-option>
        </mat-select>
      </mat-form-field>

      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Descripción</mat-label>
        <textarea matInput [(ngModel)]="description" rows="3" placeholder="Descripción opcional"></textarea>
      </mat-form-field>

      <mat-slide-toggle [(ngModel)]="active" color="primary">
        {{ active ? 'Activa' : 'Inactiva' }}
      </mat-slide-toggle>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="onCancel()">Cancelar</button>
      <button mat-flat-button color="primary" (click)="onSave()" [disabled]="saving || !name.trim()">
        <mat-spinner *ngIf="saving" diameter="20" class="inline-spinner"></mat-spinner>
        {{ saving ? 'Guardando...' : 'Guardar' }}
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .full-width { width: 100%; margin-bottom: 8px; }
    .inline-spinner { display: inline-block; margin-right: 8px; }
    mat-dialog-content { padding-top: 12px; }
  `]
})
export class CategoryDialogComponent implements OnInit {
  name = '';
  description = '';
  active = true;
  parentUuid = '';
  saving = false;
  /** Listado amplio para el select de padre (la tabla admin va paginada). */
  private allForParent: BoutiqueCategory[] = [];

  constructor(
    private _dialogRef: MatDialogRef<CategoryDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { category: BoutiqueCategory | null; allCategories?: BoutiqueCategory[] },
    private _categoryService: BoutiqueAdminCategoryService,
    private _snackBar: MatSnackBar
  ) {
    if (data.category) {
      this.name = data.category.name;
      this.description = data.category.description || '';
      this.active = data.category.active;
      this.parentUuid = data.category.parent?.uuid || '';
    }
  }

  ngOnInit(): void {
    this._categoryService.search({ page: 1, per_page: 500 }).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const paginated = wrapper.categories || wrapper;
        this.allForParent = paginated.data || paginated || [];
      },
      error: () => {
        this.allForParent = this.data.allCategories || [];
      },
    });
  }

  get parentSelectOptions(): { uuid: string; label: string }[] {
    const all = this.allForParent.length ? this.allForParent : (this.data.allCategories || []);
    const editingUuid = this.data.category?.uuid;
    const exclude = new Set<string>();
    if (editingUuid) {
      exclude.add(editingUuid);
      const addDescendants = (parentUuid: string) => {
        for (const c of all) {
          const pid = c.parent?.uuid || '';
          if (pid === parentUuid) {
            exclude.add(c.uuid);
            addDescendants(c.uuid);
          }
        }
      };
      addDescendants(editingUuid);
    }
    return all
      .filter((c: BoutiqueCategory) => !exclude.has(c.uuid))
      .map((c: BoutiqueCategory) => ({
        uuid: c.uuid,
        label: c.parent?.name ? `${c.parent.name} › ${c.name}` : c.name,
      }));
  }

  onCancel(): void {
    this._dialogRef.close(false);
  }

  onSave(): void {
    if (!this.name.trim()) return;
    this.saving = true;

    if (this.data.category) {
      const upd: Parameters<BoutiqueAdminCategoryService['update']>[0] = {
        uuid: this.data.category.uuid,
        name: this.name.trim(),
        description: this.description.trim() || undefined,
        active: this.active,
        parent_uuid: this.parentUuid ? this.parentUuid : null,
      };
      this._categoryService.update(upd).subscribe({
        next: () => {
          this._snackBar.open('Categoría actualizada correctamente', 'Cerrar', {
            duration: 3000, horizontalPosition: 'end', verticalPosition: 'top', panelClass: ['snack-success']
          });
          this._dialogRef.close(true);
        },
        error: (error) => {
          this.saving = false;
          this._snackBar.open(error.error?.message || 'Error al actualizar', 'Cerrar', {
            duration: 3000, horizontalPosition: 'end', verticalPosition: 'top', panelClass: ['snack-error']
          });
        }
      });
    } else {
      const st: Parameters<BoutiqueAdminCategoryService['store']>[0] = {
        name: this.name.trim(),
        description: this.description.trim() || undefined,
        active: this.active,
      };
      if (this.parentUuid) {
        st.parent_uuid = this.parentUuid;
      }
      this._categoryService.store(st).subscribe({
        next: () => {
          this._snackBar.open('Categoría creada correctamente', 'Cerrar', {
            duration: 3000, horizontalPosition: 'end', verticalPosition: 'top', panelClass: ['snack-success']
          });
          this._dialogRef.close(true);
        },
        error: (error) => {
          this.saving = false;
          this._snackBar.open(error.error?.message || 'Error al crear', 'Cerrar', {
            duration: 3000, horizontalPosition: 'end', verticalPosition: 'top', panelClass: ['snack-error']
          });
        }
      });
    }
  }
}

// ─── Delete Confirmation Dialog ───────────────────────────────────────

@Component({
  selector: 'app-category-delete-dialog',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatButtonModule,
  ],
  template: `
    <h2 mat-dialog-title>Confirmar eliminación</h2>
    <mat-dialog-content>
      <p>¿Estás seguro de que deseas eliminar la categoría <strong>{{ data.category.name }}</strong>?</p>
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
export class CategoryDeleteDialogComponent {
  constructor(
    private _dialogRef: MatDialogRef<CategoryDeleteDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { category: BoutiqueCategory }
  ) {}

  onCancel(): void {
    this._dialogRef.close(false);
  }

  onConfirm(): void {
    this._dialogRef.close(true);
  }
}
