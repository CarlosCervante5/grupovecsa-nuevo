import { Component, OnInit } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';

import { BoutiqueAdminProductService } from '../../services/boutique-admin-product.service';
import { BoutiqueAdminCategoryService } from '../../services/boutique-admin-category.service';
import { BoutiqueAdminDealershipService } from '../../services/boutique-admin-dealership.service';
import {
  BoutiqueProduct,
  BoutiqueCategory,
  BoutiqueProductImage,
  BoutiqueDealershipSummary,
} from '../../../../../../boutique/interfaces/boutique.interfaces';
import { reload } from '@helpers/session.helper';

@Component({
  selector: 'app-product-form',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    DragDropModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatSlideToggleModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    MatTooltipModule,
  ],
  templateUrl: './product-form.component.html',
  styleUrls: ['./product-form.component.css']
})
export class ProductFormComponent implements OnInit {

  form!: FormGroup;
  categories: BoutiqueCategory[] = [];
  dealerships: BoutiqueDealershipSummary[] = [];
  product: BoutiqueProduct | null = null;
  images: BoutiqueProductImage[] = [];

  isEditMode = false;
  productUuid = '';
  loading = true;
  saving = false;
  uploadingImage = false;

  constructor(
    private _fb: FormBuilder,
    private _route: ActivatedRoute,
    private _router: Router,
    private _productService: BoutiqueAdminProductService,
    private _categoryService: BoutiqueAdminCategoryService,
    private _dealershipService: BoutiqueAdminDealershipService,
    private _snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.initForm();
    this.loadCategories();
    this.loadDealerships();

    const uuid = this._route.snapshot.paramMap.get('uuid');
    if (uuid) {
      this.isEditMode = true;
      this.productUuid = uuid;
      this.loadProduct(uuid);
    } else {
      this.loading = false;
    }
  }

  private initForm(): void {
    this.form = this._fb.group({
      name: ['', Validators.required],
      description: [''],
      price: [null, [Validators.required, Validators.min(0)]],
      sku: ['', Validators.required],
      category_uuid: ['', Validators.required],
      dealership_id: [null as number | null, Validators.required],
      stock: [0, [Validators.required, Validators.min(0)]],
      active: [true]
    });
  }

  private loadDealerships(): void {
    this._dealershipService.list().subscribe({
      next: (response) => {
        const rows = response.data?.dealerships || [];
        this.dealerships = Array.isArray(rows) ? rows : [];
        if (this.dealerships.length === 1 && !this.form.get('dealership_id')?.value) {
          this.form.patchValue({ dealership_id: this.dealerships[0].id });
        }
      },
      error: (error) => reload(error, this._router),
    });
  }

  private loadCategories(): void {
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

  private loadProduct(uuid: string): void {
    this.loading = true;
    this._productService.search({ search: undefined, page: 1, per_page: 100 }).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const paginated = wrapper.products || wrapper;
        const products: BoutiqueProduct[] = paginated.data || paginated;
        const found = products.find(p => p.uuid === uuid);
        if (found) {
          this.product = found;
          this.images = [...(found.images || [])].sort((a, b) => a.sort_id - b.sort_id);
          this.form.patchValue({
            name: found.name,
            description: found.description || '',
            price: found.price,
            sku: found.sku,
            category_uuid: found.category?.uuid || '',
            dealership_id: found.dealership_id ?? found.dealership?.id ?? null,
            stock: found.stock,
            active: found.active
          });
        }
        this.loading = false;
      },
      error: (error) => {
        this.loading = false;
        reload(error, this._router);
      }
    });
  }

  onSave(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    this.saving = true;
    const formValue = this.form.value;

    if (this.isEditMode) {
      this._productService.update({
        uuid: this.productUuid,
        category_uuid: formValue.category_uuid,
        dealership_id: formValue.dealership_id,
        name: formValue.name.trim(),
        description: formValue.description?.trim() || undefined,
        price: formValue.price,
        sku: formValue.sku.trim(),
        stock: formValue.stock,
        active: formValue.active
      }).subscribe({
        next: () => {
          this.showSnackBar('Producto actualizado correctamente');
          this.navigateBack();
        },
        error: (error) => {
          this.saving = false;
          if (error.error?.message) {
            this.showSnackBar(error.error.message, true);
          } else {
            reload(error, this._router);
          }
        }
      });
    } else {
      this._productService.store({
        category_uuid: formValue.category_uuid,
        dealership_id: formValue.dealership_id,
        name: formValue.name.trim(),
        description: formValue.description?.trim() || undefined,
        price: formValue.price,
        sku: formValue.sku.trim(),
        stock: formValue.stock,
        active: formValue.active
      }).subscribe({
        next: (response) => {
          this.showSnackBar('Producto creado correctamente');
          const wrapper = response.data as any;
          const product = wrapper.product || wrapper;
          this.isEditMode = true;
          this.productUuid = product.uuid;
          this.product = product;
          this.saving = false;
          this.navigateBack();
        },
        error: (error) => {
          this.saving = false;
          if (error.error?.message) {
            this.showSnackBar(error.error.message, true);
          } else {
            reload(error, this._router);
          }
        }
      });
    }
  }

  // ─── Image Management ─────────────────────────────────────────────

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files || input.files.length === 0 || !this.productUuid) return;

    const files = Array.from(input.files);
    this.uploadImages(files);
    input.value = '';
  }

  private uploadImages(files: File[]): void {
    if (files.length === 0) return;

    this.uploadingImage = true;
    let completed = 0;
    let errors = 0;

    files.forEach(file => {
      this._productService.storeImage(this.productUuid, file).subscribe({
        next: (response) => {
          const wrapper = response.data as any;
          const image = wrapper.image || wrapper;
          this.images.push(image);
          completed++;
          if (completed + errors === files.length) {
            this.uploadingImage = false;
            if (errors > 0) {
              this.showSnackBar(`${completed} imagen(es) subida(s), ${errors} error(es)`, true);
            } else {
              this.showSnackBar(`${completed} imagen(es) subida(s) correctamente`);
            }
          }
        },
        error: () => {
          errors++;
          if (completed + errors === files.length) {
            this.uploadingImage = false;
            this.showSnackBar(`${completed} imagen(es) subida(s), ${errors} error(es)`, true);
          }
        }
      });
    });
  }

  onImageDrop(event: CdkDragDrop<BoutiqueProductImage[]>): void {
    moveItemInArray(this.images, event.previousIndex, event.currentIndex);
    this.saveImageOrder();
  }

  private saveImageOrder(): void {
    const imagesPayload = this.images.map((img, index) => ({
      uuid: img.uuid,
      sort_id: index + 1
    }));

    this._productService.sortImages({
      product_uuid: this.productUuid,
      images: imagesPayload
    }).subscribe({
      next: () => {
        this.images.forEach((img, index) => {
          img.sort_id = index + 1;
        });
      },
      error: (error) => {
        if (error.error?.message) {
          this.showSnackBar(error.error.message, true);
        }
      }
    });
  }

  deleteImage(image: BoutiqueProductImage): void {
    this._productService.deleteImage(image.uuid).subscribe({
      next: () => {
        this.images = this.images.filter(img => img.uuid !== image.uuid);
        this.showSnackBar('Imagen eliminada');
      },
      error: (error) => {
        if (error.error?.message) {
          this.showSnackBar(error.error.message, true);
        }
      }
    });
  }

  // ─── Navigation ───────────────────────────────────────────────────

  navigateBack(): void {
    this._router.navigate(['/admin/administrador/boutique/products']);
  }

  get pageTitle(): string {
    return this.isEditMode ? 'Editar producto' : 'Nuevo producto';
  }

  categoryOptionLabel(cat: BoutiqueCategory): string {
    if (cat.parent?.name) {
      return `${cat.parent.name} › ${cat.name}`;
    }
    return cat.name;
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
