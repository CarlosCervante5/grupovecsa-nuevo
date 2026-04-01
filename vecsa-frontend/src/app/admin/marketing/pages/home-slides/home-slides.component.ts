import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { reload } from '@helpers/session.helper';
import { HomeSlideService } from '@services/home-slide.service';
import { HomeSlide, HomeSlidesResponse } from '@interfaces/admin.interfaces';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-home-slides',
  templateUrl: './home-slides.component.html',
  styleUrls: ['./home-slides.component.css'],
  standalone: false
})
export class HomeSlidesComponent {

  public slides: HomeSlide[] = [];
  public loading: boolean = true;
  public showForm: boolean = false;
  public editingSlide: HomeSlide | null = null;

  // Form fields
  public title: string = '';
  public subtitle: string = '';
  public offer_main: string = '';
  public offer_main_text: string = '';
  public offer_sub: string = '';
  public offer_secondary: string = '';
  public offer_secondary_text: string = '';
  public button_text: string = '';
  public button_link: string = '';
  public disclaimer: string = '';
  public desktopImageFile: File | null = null;
  public mobileImageFile: File | null = null;
  public desktopImagePreview: string = '';
  public mobileImagePreview: string = '';
  public saving: boolean = false;

  constructor(
    private _homeSlideService: HomeSlideService,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {
    this.loadSlides();
  }

  loadSlides(): void {
    this.loading = true;
    this._homeSlideService.search().subscribe({
      next: (response: HomeSlidesResponse) => {
        this.slides = response.data.slides;
        this.loading = false;
      },
      error: (error: any) => {
        this.loading = false;
        reload(error, this._router);
      }
    });
  }

  openCreateForm(): void {
    this.resetForm();
    this.editingSlide = null;
    this.showForm = true;
  }

  openEditForm(slide: HomeSlide): void {
    this.editingSlide = slide;
    this.title = slide.title || '';
    this.subtitle = slide.subtitle || '';
    this.offer_main = slide.offer_main || '';
    this.offer_main_text = slide.offer_main_text || '';
    this.offer_sub = slide.offer_sub || '';
    this.offer_secondary = slide.offer_secondary || '';
    this.offer_secondary_text = slide.offer_secondary_text || '';
    this.button_text = slide.button_text || '';
    this.button_link = slide.button_link || '';
    this.disclaimer = slide.disclaimer || '';
    this.desktopImageFile = null;
    this.mobileImageFile = null;
    this.desktopImagePreview = slide.desktop_image_path || '';
    this.mobileImagePreview = slide.mobile_image_path || '';
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
    this.resetForm();
  }

  resetForm(): void {
    this.title = '';
    this.subtitle = '';
    this.offer_main = '';
    this.offer_main_text = '';
    this.offer_sub = '';
    this.offer_secondary = '';
    this.offer_secondary_text = '';
    this.button_text = '';
    this.button_link = '';
    this.disclaimer = '';
    this.desktopImageFile = null;
    this.mobileImageFile = null;
    this.desktopImagePreview = '';
    this.mobileImagePreview = '';
    this.editingSlide = null;
  }

  onDesktopImageSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.desktopImageFile = input.files[0];
      const reader = new FileReader();
      reader.onload = () => {
        this.desktopImagePreview = reader.result as string;
      };
      reader.readAsDataURL(this.desktopImageFile);
    }
  }

  onMobileImageSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.mobileImageFile = input.files[0];
      const reader = new FileReader();
      reader.onload = () => {
        this.mobileImagePreview = reader.result as string;
      };
      reader.readAsDataURL(this.mobileImageFile);
    }
  }

  saveSlide(): void {
    if (!this.title.trim()) {
      this.openSnackBar('El título es requerido', 'top', 'snack-error');
      return;
    }

    this.saving = true;
    const formData = new FormData();
    formData.append('title', this.title);
    formData.append('subtitle', this.subtitle);
    formData.append('offer_main', this.offer_main);
    formData.append('offer_main_text', this.offer_main_text);
    formData.append('offer_sub', this.offer_sub);
    formData.append('offer_secondary', this.offer_secondary);
    formData.append('offer_secondary_text', this.offer_secondary_text);
    formData.append('button_text', this.button_text);
    formData.append('button_link', this.button_link);
    formData.append('disclaimer', this.disclaimer);

    if (this.desktopImageFile) {
      formData.append('desktop_image', this.desktopImageFile);
    }
    if (this.mobileImageFile) {
      formData.append('mobile_image', this.mobileImageFile);
    }

    if (this.editingSlide) {
      formData.append('uuid', this.editingSlide.uuid);
      this._homeSlideService.update(formData).subscribe({
        next: () => {
          this.openSnackBar('Slide actualizado correctamente', 'top', 'snack-success');
          this.showForm = false;
          this.resetForm();
          this.saving = false;
          this.loadSlides();
        },
        error: (error: any) => {
          this.saving = false;
          reload(error, this._router);
        }
      });
    } else {
      this._homeSlideService.store(formData).subscribe({
        next: () => {
          this.openSnackBar('Slide creado correctamente', 'top', 'snack-success');
          this.showForm = false;
          this.resetForm();
          this.saving = false;
          this.loadSlides();
        },
        error: (error: any) => {
          this.saving = false;
          reload(error, this._router);
        }
      });
    }
  }

  toggleActive(slide: HomeSlide): void {
    this._homeSlideService.toggle(slide.uuid).subscribe({
      next: () => {
        slide.active = !slide.active;
        this.openSnackBar(
          slide.active ? 'Slide activado' : 'Slide desactivado',
          'top',
          'snack-success'
        );
      },
      error: (error: any) => {
        reload(error, this._router);
      }
    });
  }

  deleteSlide(slide: HomeSlide): void {
    Swal.fire({
      title: '¿Estás segur@ que quieres eliminar este slide?',
      text: slide.title,
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        this._homeSlideService.delete(slide.uuid).subscribe({
          next: () => {
            Swal.fire('Slide eliminado con éxito', '', 'success');
            this.loadSlides();
          },
          error: (error: any) => {
            reload(error, this._router);
          }
        });
      }
    });
  }

  drop(event: CdkDragDrop<HomeSlide[]>): void {
    moveItemInArray(this.slides, event.previousIndex, event.currentIndex);
    const image_order = this.slides.map((slide, index) => ({
      uuid: slide.uuid,
      sort_id: index + 1
    }));
    this._homeSlideService.sortUpdate(image_order).subscribe({
      next: () => {
        this.openSnackBar('Orden actualizado', 'top', 'snack-success');
      },
      error: (error: any) => {
        reload(error, this._router);
      }
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
