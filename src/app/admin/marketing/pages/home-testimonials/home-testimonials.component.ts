import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { MatSnackBar } from '@angular/material/snack-bar';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { reload } from '@helpers/session.helper';
import { HomeTestimonialService } from '@services/home-testimonial.service';
import { HomeTestimonial, HomeTestimonialsResponse } from '@interfaces/admin.interfaces';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-home-testimonials',
  templateUrl: './home-testimonials.component.html',
  styleUrls: ['./home-testimonials.component.css'],
  standalone: false
})
export class HomeTestimonialsComponent {

  public testimonials: HomeTestimonial[] = [];
  public loading: boolean = true;
  public showForm: boolean = false;

  // Form fields
  public imageFile: File | null = null;
  public imagePreview: string = '';
  public alt: string = '';
  public saving: boolean = false;

  constructor(
    private _homeTestimonialService: HomeTestimonialService,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {
    this.loadTestimonials();
  }

  loadTestimonials(): void {
    this.loading = true;
    this._homeTestimonialService.search().subscribe({
      next: (response: HomeTestimonialsResponse) => {
        this.testimonials = response.data.testimonials;
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
    this.showForm = true;
  }

  cancelForm(): void {
    this.showForm = false;
    this.resetForm();
  }

  resetForm(): void {
    this.imageFile = null;
    this.imagePreview = '';
    this.alt = '';
  }

  onImageSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files.length > 0) {
      this.imageFile = input.files[0];
      this.showForm = true;
      const reader = new FileReader();
      reader.onload = () => {
        this.imagePreview = reader.result as string;
      };
      reader.readAsDataURL(this.imageFile);
    }
  }

  saveTestimonial(): void {
    if (!this.imageFile) {
      this.openSnackBar('La imagen es requerida', 'top', 'snack-error');
      return;
    }

    this.saving = true;
    const formData = new FormData();
    formData.append('image', this.imageFile);
    if (this.alt.trim()) {
      formData.append('alt', this.alt);
    }

    this._homeTestimonialService.store(formData).subscribe({
      next: () => {
        this.openSnackBar('Testimonio creado correctamente', 'top', 'snack-success');
        this.showForm = false;
        this.resetForm();
        this.saving = false;
        this.loadTestimonials();
      },
      error: (error: any) => {
        this.saving = false;
        reload(error, this._router);
      }
    });
  }

  toggleActive(testimonial: HomeTestimonial): void {
    this._homeTestimonialService.toggle(testimonial.uuid).subscribe({
      next: () => {
        testimonial.active = !testimonial.active;
        this.openSnackBar(
          testimonial.active ? 'Testimonio activado' : 'Testimonio desactivado',
          'top',
          'snack-success'
        );
      },
      error: (error: any) => {
        reload(error, this._router);
      }
    });
  }

  deleteTestimonial(testimonial: HomeTestimonial): void {
    Swal.fire({
      title: '¿Estás segur@ que quieres eliminar este testimonio?',
      text: testimonial.alt || 'Sin texto alternativo',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        this._homeTestimonialService.delete(testimonial.uuid).subscribe({
          next: () => {
            Swal.fire('Testimonio eliminado con éxito', '', 'success');
            this.loadTestimonials();
          },
          error: (error: any) => {
            reload(error, this._router);
          }
        });
      }
    });
  }

  drop(event: CdkDragDrop<HomeTestimonial[]>): void {
    moveItemInArray(this.testimonials, event.previousIndex, event.currentIndex);
    const image_order = this.testimonials.map((testimonial, index) => ({
      uuid: testimonial.uuid,
      sort_id: index + 1
    }));
    this._homeTestimonialService.sortUpdate(image_order).subscribe({
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
