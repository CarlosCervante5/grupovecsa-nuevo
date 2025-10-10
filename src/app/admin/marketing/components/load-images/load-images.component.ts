import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { VehiclesComponent } from '../../pages/vehicles/vehicles.component';
import { ImagesService } from '@services/images.service';
import Swal from 'sweetalert2';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

interface Result {  
  reload: boolean;
}
@Component({
    selector: 'app-load-images',
    templateUrl: './load-images.component.html',
    styleUrls: ['./load-images.component.css'],
    standalone: false
})
export class LoadImagesComponent {
  files:File[] = [];
  disabled:Boolean = true;
  loading:Boolean = false;
  result:Result = {      
    reload: false
  }    
  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<VehiclesComponent>,
    private _imagesService:ImagesService,
    private _router: Router
  ) {}

  openLink(event: MouseEvent): void {
    this._bottomSheetRef.dismiss(
      this.result
    );
    event.preventDefault();
  }

  assignImage( event: Event ){
    const element = event.currentTarget as HTMLInputElement;
    let fileList: FileList | null = element.files;
    if (fileList) {  
      this.files = Array.from(fileList);  
      if(this.files.length > 0){
        this.disabled = false;
      }else{
        this.disabled = true;
      }
    }    
  }

  uploadImages() {
    this.disabled = true;
    this.loading = true;
    this._imagesService.setImage(
      this.data.vehicle_id,
      this.files
    ).subscribe({
      next: () => {
        // if( resp.status == "success" ){          
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Carga de imagenes de manera correcta',
            showConfirmButton: true,
            confirmButtonColor: '#008bcc',
            timer: 3500
          });
          this.loading = false;
          this.result.reload = true;
          this._bottomSheetRef.dismiss(
            this.result
          );
        // }
      },
      error: (err) => {
        // Swal.fire({
        //   icon: 'error',
        //   title: 'Oupps..',
        //   text: err.error.message,
        //   showConfirmButton: true,
        //   confirmButtonColor: '#008bcc',
        //   timer: 3500         
        // });
        reload(err, this._router);
        this.loading = false;
        this._bottomSheetRef.dismiss(this.result);
      }
    });
  }
  
  close() {
    this._bottomSheetRef.dismiss(this.result);
  }
}
