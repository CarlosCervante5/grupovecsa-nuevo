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
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { CdkDragDrop, DragDropModule, moveItemInArray } from '@angular/cdk/drag-drop';
import { ImageAiDialogComponent } from 'src/app/shared/components/image-ai-dialog/image-ai-dialog.component';

import { BoutiqueAdminProductService } from '../../services/boutique-admin-product.service';
import { BoutiqueAdminCategoryService } from '../../services/boutique-admin-category.service';
import { BoutiqueAdminDealershipService } from '../../services/boutique-admin-dealership.service';
import {
  BoutiqueProduct,
  BoutiqueCategory,
  BoutiqueProductImage,
  BoutiqueDealershipSummary,
} from '../../../../../../boutique/interfaces/boutique.interfaces';
import {
  categoryHasChildren,
  categorySelectionError,
  getChildCategories,
  resolveCategorySelection,
  resolveLeafCategoryUuid,
} from '../../../../../../boutique/utils/boutique-category-tree.util';
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
    MatDialogModule,
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
  private pendingCategoryUuid = '';
  private categoriesLoaded = false;

  constructor(
    private _fb: FormBuilder,
    private _route: ActivatedRoute,
    private _router: Router,
    private _productService: BoutiqueAdminProductService,
    private _categoryService: BoutiqueAdminCategoryService,
    private _dealershipService: BoutiqueAdminDealershipService,
    private _snackBar: MatSnackBar,
    private _dialog: MatDialog,
  ) {}

  ngOnInit(): void {
    this.initForm();
    this.loadCategories();
    this.loadDealerships();
    this.setupCategoryCascade();

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
      parent_category_uuid: ['', Validators.required],
      subcategory_uuid: [''],
      subsubcategory_uuid: [''],
      dealership_id: [null as number | null, Validators.required],
      stock: [0, [Validators.required, Validators.min(0)]],
      active: [true]
    });
  }

  private setupCategoryCascade(): void {
    this.form.get('parent_category_uuid')?.valueChanges.subscribe(() => {
      this.form.patchValue({ subcategory_uuid: '', subsubcategory_uuid: '' }, { emitEvent: false });
    });
    this.form.get('subcategory_uuid')?.valueChanges.subscribe(() => {
      this.form.patchValue({ subsubcategory_uuid: '' }, { emitEvent: false });
    });
  }

  get parentCategories(): BoutiqueCategory[] {
    return getChildCategories(this.categories, null);
  }

  get subCategories(): BoutiqueCategory[] {
    const parentUuid = this.form.get('parent_category_uuid')?.value as string;
    return parentUuid ? getChildCategories(this.categories, parentUuid) : [];
  }

  get sub2Categories(): BoutiqueCategory[] {
    const subUuid = this.form.get('subcategory_uuid')?.value as string;
    return subUuid ? getChildCategories(this.categories, subUuid) : [];
  }

  get showSubCategoryField(): boolean {
    const parentUuid = this.form.get('parent_category_uuid')?.value as string;
    return !!parentUuid && (this.subCategories.length > 0 || categoryHasChildren(this.categories, parentUuid));
  }

  get showSub2CategoryField(): boolean {
    const subUuid = this.form.get('subcategory_uuid')?.value as string;
    return !!subUuid && (this.sub2Categories.length > 0 || categoryHasChildren(this.categories, subUuid));
  }

  private applyCategorySelectionFromProduct(): void {
    if (!this.categoriesLoaded || !this.pendingCategoryUuid) {
      return;
    }
    const selection = resolveCategorySelection(this.pendingCategoryUuid, this.categories);
    this.form.patchValue({
      parent_category_uuid: selection.parentUuid,
      subcategory_uuid: selection.subUuid,
      subsubcategory_uuid: selection.sub2Uuid,
    }, { emitEvent: false });
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
    this.fetchAllCategories(1, []);
  }

  private fetchAllCategories(page: number, accumulated: BoutiqueCategory[]): void {
    this._categoryService.search({ page, per_page: 500 }).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const paginated = wrapper.categories || wrapper;
        const categories = paginated.data || (Array.isArray(paginated) ? paginated : []);
        const list = Array.isArray(categories) ? categories : [];
        const all = accumulated.concat(list);
        const lastPage = paginated.last_page ?? 1;
        if (page < lastPage) {
          this.fetchAllCategories(page + 1, all);
          return;
        }
        this.categories = all;
        this.categoriesLoaded = true;
        this.applyCategorySelectionFromProduct();
      },
      error: (error) => {
        reload(error, this._router);
      }
    });
  }

  private loadProduct(uuid: string): void {
    this.loading = true;
    this._productService.detail(uuid).subscribe({
      next: (response) => {
        const wrapper = response.data as any;
        const found: BoutiqueProduct | undefined = wrapper.product || wrapper;
        if (found) {
          this.product = found;
          this.images = [...(found.images || [])].sort((a, b) => a.sort_id - b.sort_id);
          this.form.patchValue({
            name: found.name,
            description: found.description || '',
            price: found.price,
            sku: found.sku,
            dealership_id: found.dealership_id ?? found.dealership?.id ?? null,
            stock: found.stock,
            active: found.active
          });
          this.pendingCategoryUuid = found.category?.uuid || '';
          this.applyCategorySelectionFromProduct();
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

    const formValue = this.form.value;
    const categoryError = categorySelectionError(
      formValue.parent_category_uuid,
      formValue.subcategory_uuid,
      formValue.subsubcategory_uuid,
      this.categories,
    );
    if (categoryError) {
      this.showSnackBar(categoryError, true);
      return;
    }

    const categoryUuid = resolveLeafCategoryUuid(
      formValue.parent_category_uuid,
      formValue.subcategory_uuid,
      formValue.subsubcategory_uuid,
      this.categories,
    );

    this.saving = true;

    if (this.isEditMode) {
      this._productService.update({
        uuid: this.productUuid,
        category_uuid: categoryUuid,
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
        category_uuid: categoryUuid,
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

  canUseImageAi(image: BoutiqueProductImage): boolean {
    return image.status === 'uploaded' && !!image.uuid && !!image.image_path;
  }

  openProductImageAi(image: BoutiqueProductImage, index: number): void {
    if (!this.canUseImageAi(image)) {
      this.showSnackBar('La imagen debe estar subida antes de usar IA.', true);
      return;
    }
    this._dialog.open(ImageAiDialogComponent, {
      width: '640px',
      maxWidth: '95vw',
      data: {
        sourceUrl: image.image_path,
        targetType: 'boutique_product_image',
        targetUuid: image.uuid,
        title: 'Fondo blanco — foto del producto',
        onSaved: (imageUrl: string) => {
          this.images[index].image_path = imageUrl;
          this.showSnackBar('Imagen del producto actualizada');
        },
      },
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

  private showSnackBar(message: string, isError = false): void {
    this._snackBar.open(message, 'Cerrar', {
      duration: 3000,
      horizontalPosition: 'end',
      verticalPosition: 'top',
      panelClass: isError ? ['snack-error'] : ['snack-success']
    });
  }
}
