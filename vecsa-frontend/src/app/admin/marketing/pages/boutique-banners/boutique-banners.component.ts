import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { reload } from '@helpers/session.helper';
import { BoutiqueBannerService } from '@services/boutique-banner.service';
import { BoutiqueBanner, BoutiqueBannersResponse } from '@interfaces/admin.interfaces';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-boutique-banners',
  templateUrl: './boutique-banners.component.html',
  styleUrls: ['./boutique-banners.component.css'],
  standalone: false
})
export class BoutiqueBannersComponent {

  public banners: BoutiqueBanner[] = [];
  public loading = true;
  public showForm = false;
  public editingBanner: BoutiqueBanner | null = null;
  public saving = false;

  // Form fields
  public title = '';
  public subtitle = '';
  public cta_text = '';
  public cta_link = '';
  public bg_class = '';
  public desktopImageFile: File | null = null;
  public mobileImageFile: File | null = null;
  public desktopImagePreview = '';
  public mobileImagePreview = '';

  constructor(
    private _bannerService: BoutiqueBannerService,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {
    this.loadBanners();
  }

  loadBanners(): void {
    this.loading = true;
    this._bannerService.search().subscribe({
      next: (response: BoutiqueBannersResponse) => {
        this.banners = response.data.banners;
        this.loading = false;
      },
      error: (error: any) => {
        this.loading = false;
        reload(error, this._router);
      }
    });
  }

  /** Tras subir imágenes async, reintenta hasta que las rutas estén en BD o timeout. */
  private pollBannersUntilImagesReady(
    bannerUuid: string,
    expectDesktop: boolean,
    expectMobile: boolean,
    attempt: number,
  ): void {
    if (attempt > 12) {
      this.openSnackBar('La imagen sigue en cola o falló; revisa logs del backend', 'top', 'snack-error');
      return;
    }
    const delayMs = attempt === 0 ? 2000 : 3000;
    setTimeout(() => {
      this._bannerService.search().subscribe({
        next: (response: BoutiqueBannersResponse) => {
          this.banners = response.data.banners;
          this.loading = false;
          const banner = this.banners.find((b) => b.uuid === bannerUuid);
          const desktopReady = !expectDesktop || (banner?.desktop_image_path?.length ?? 0) > 0;
          const mobileReady = !expectMobile || (banner?.mobile_image_path?.length ?? 0) > 0;
          if ((!desktopReady || !mobileReady) && attempt < 12) {
            this.pollBannersUntilImagesReady(bannerUuid, expectDesktop, expectMobile, attempt + 1);
          } else if (desktopReady && mobileReady) {
            this.openSnackBar('Imagen(es) del banner listas', 'top', 'snack-success');
          }
        },
        error: (error: any) => reload(error, this._router),
      });
    }, delayMs);
  }

  openCreateForm(): void {
    this.resetForm();
    this.editingBanner = null;
    this.showForm = true;
  }

  openEditForm(banner: BoutiqueBanner): void {
    this.editingBanner = banner;
    this.title = banner.title || '';
    this.subtitle = banner.subtitle || '';
    this.cta_text = banner.cta_text || '';
    this.cta_link = banner.cta_link || '';
    this.bg_class = banner.bg_class || '';
    this.desktopImageFile = null;
    this.mobileImageFile = null;
    this.desktopImagePreview = banner.desktop_image_path || '';
    this.mobileImagePreview = banner.mobile_image_path || '';
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
    this.resetForm();
  }

  resetForm(): void {
    this.title = '';
    this.subtitle = '';
    this.cta_text = '';
    this.cta_link = '';
    this.bg_class = '';
    this.desktopImageFile = null;
    this.mobileImageFile = null;
    this.desktopImagePreview = '';
    this.mobileImagePreview = '';
    this.editingBanner = null;
  }

  onDesktopImageSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.desktopImageFile = input.files[0];
      const reader = new FileReader();
      reader.onload = () => { this.desktopImagePreview = reader.result as string; };
      reader.readAsDataURL(this.desktopImageFile);
    }
  }

  onMobileImageSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.mobileImageFile = input.files[0];
      const reader = new FileReader();
      reader.onload = () => { this.mobileImagePreview = reader.result as string; };
      reader.readAsDataURL(this.mobileImageFile);
    }
  }

  saveBanner(): void {
    if (!this.title.trim()) {
      this.openSnackBar('El título es requerido', 'top', 'snack-error');
      return;
    }

    this.saving = true;
    const hadDesktopImage = !!this.desktopImageFile;
    const hadMobileImage = !!this.mobileImageFile;
    const formData = new FormData();
    formData.append('title', this.title);
    formData.append('subtitle', this.subtitle);
    formData.append('cta_text', this.cta_text);
    formData.append('cta_link', this.cta_link);
    formData.append('bg_class', this.bg_class);

    if (this.desktopImageFile) formData.append('desktop_image', this.desktopImageFile);
    if (this.mobileImageFile) formData.append('mobile_image', this.mobileImageFile);

    if (this.editingBanner) {
      formData.append('uuid', this.editingBanner.uuid);
      const bannerUuid = this.editingBanner.uuid;
      this._bannerService.update(formData).subscribe({
        next: () => {
          this.openSnackBar('Banner guardado; imagen en proceso de subida', 'top', 'snack-success');
          this.showForm = false;
          this.resetForm();
          this.saving = false;
          if (hadDesktopImage || hadMobileImage) {
            this.pollBannersUntilImagesReady(bannerUuid, hadDesktopImage, hadMobileImage, 0);
          } else {
            this.loadBanners();
          }
        },
        error: (error: any) => { this.saving = false; reload(error, this._router); }
      });
    } else {
      this._bannerService.store(formData).subscribe({
        next: (res: any) => {
          const bannerUuid = res?.data?.banner?.uuid as string | undefined;
          this.openSnackBar('Banner creado; imagen en proceso de subida', 'top', 'snack-success');
          this.showForm = false;
          this.resetForm();
          this.saving = false;
          if (bannerUuid && (hadDesktopImage || hadMobileImage)) {
            this.pollBannersUntilImagesReady(bannerUuid, hadDesktopImage, hadMobileImage, 0);
          } else {
            this.loadBanners();
          }
        },
        error: (error: any) => { this.saving = false; reload(error, this._router); }
      });
    }
  }

  toggleActive(banner: BoutiqueBanner): void {
    this._bannerService.toggle(banner.uuid).subscribe({
      next: () => {
        banner.active = !banner.active;
        this.openSnackBar(banner.active ? 'Banner activado' : 'Banner desactivado', 'top', 'snack-success');
      },
      error: (error: any) => { reload(error, this._router); }
    });
  }

  deleteBanner(banner: BoutiqueBanner): void {
    Swal.fire({
      title: '¿Eliminar este banner?',
      text: banner.title,
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        this._bannerService.delete(banner.uuid).subscribe({
          next: () => { Swal.fire('Banner eliminado', '', 'success'); this.loadBanners(); },
          error: (error: any) => { reload(error, this._router); }
        });
      }
    });
  }

  drop(event: CdkDragDrop<BoutiqueBanner[]>): void {
    moveItemInArray(this.banners, event.previousIndex, event.currentIndex);
    const image_order = this.banners.map((b, i) => ({ uuid: b.uuid, sort_id: i + 1 }));
    this._bannerService.sortUpdate(image_order).subscribe({
      next: () => { this.openSnackBar('Orden actualizado', 'top', 'snack-success'); },
      error: (error: any) => { reload(error, this._router); }
    });
  }

  openSnackBar(message: string, verticalPosition: any, className: string): void {
    this._snackBar.open(message, 'cerrar', {
      duration: 3000,
      horizontalPosition: 'end',
      verticalPosition: verticalPosition,
      panelClass: [className],
    });
  }
}
