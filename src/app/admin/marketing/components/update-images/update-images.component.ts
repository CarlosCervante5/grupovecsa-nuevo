import { Component, ElementRef, Inject, ViewChild } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { VehiclesComponent } from '../../pages/vehicles/vehicles.component';
import { ImagesService } from '@services/images.service';
import Swal from 'sweetalert2';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';

import { environment } from '@environments/environment';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

// Interfaces
import { ImageOrder } from 'src/app/dashboard/pages/comprar-autos/interfaces/detail/vehicle_data.interface';

interface Result {
  reload: boolean;
  first_image: boolean;
}

@Component({
    selector: 'app-update-images',
    templateUrl: './update-images.component.html',
    styleUrls: ['./update-images.component.css'],
    standalone: false
})
export class UpdateImagesComponent {
  public imagesForSlider: ImageOrder[] = [];
  public spinner: boolean = false;
  private baseUrl: string = environment.baseUrl;

  public vehicle_id!: number;
  public result: Result = {
    reload: false,
    first_image: false
  };

  @ViewChild('images') images!: ElementRef;

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<VehiclesComponent>,
    private _imagesService: ImagesService,
    private _router: Router
  ) {
    // Set images
    for (let i = 0; i < this.data.images.length; i++) {
      this.imagesForSlider.push({
        id: this.data.images[i].uuid,
        sort_id: this.data.images[i].sort_id,
        path: this.data.images[i].service_image_url,
        path_public: this.data.images[i].service_public_id,
        external_website: "no",
        selected: false // Agregar propiedad selected
      });
    }

    this.vehicle_id = data.vehicle_id;
  }

  // Función para mover ítems seleccionados o uno individual si no hay selección
  drop(event: CdkDragDrop<ImageOrder[]>) {
    const selectedImages = this.imagesForSlider.filter(image => image.selected);
  
    if (selectedImages.length > 0) {
      // Remover las imágenes seleccionadas manteniendo el orden original
      const remainingImages = this.imagesForSlider.filter(image => !image.selected);
      
      // Insertar las imágenes seleccionadas en el nuevo índice del drop
      const insertIndex = event.currentIndex;
  
      // Mantener el orden original de las seleccionadas e insertarlas en la posición del drop
      this.imagesForSlider = [
        ...remainingImages.slice(0, insertIndex),
        ...selectedImages,
        ...remainingImages.slice(insertIndex)
      ];
    } else {
      // Si no hay imágenes seleccionadas, mover solo la arrastrada
      moveItemInArray(this.imagesForSlider, event.previousIndex, event.currentIndex);
    }
  }

  isDragDrop(object: any): object is CdkDragDrop<ImageOrder[]> {
    return 'previousIndex' in object;
  }

  // Cambiar el orden de las imágenes basado en el nuevo arreglo
  changeOrder(): void {
    this._imagesService.changeOrder(this.imagesForSlider)
      .subscribe({
        next: (resp) => {
          Swal.fire({
            icon: 'success',
            title: resp.message,
            showConfirmButton: false,
            timer: 2000
          });
          this.result.reload = true;
          this._bottomSheetRef.dismiss(this.result);
        },
        error: (err) => {
          reload(err, this._router);
        }
      });
  }

  deleteimage(vehicle_image_uuid: string, index: number): void {
    Swal.fire({
      title: '¿Estás segur@ que quieres eliminar esta imagen?',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
    }).then((result) => {
      if (result.isConfirmed) {
        this._imagesService.deleteImage(vehicle_image_uuid)
          .subscribe({
            next: (resp) => {
              this.imagesForSlider.splice(index, 1);

              if (index === 0) {
                this.result.first_image = true;
                this.result.reload = false;
                this._bottomSheetRef.dismiss(this.result);
              }

              Swal.fire(resp.message, '', 'success');
            },
            error: (err: any) => {
              reload(err, this._router);
            }
          })
      }
    })
  }
}
