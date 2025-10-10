import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { ScheduleEventsComponent } from '../../pages/schedule-events/schedule-events.component';
// import { PrincipalImagesService } from '../../services/principal-images.service';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

interface Result {
  reload: boolean;
}

@Component({
    selector: 'app-load-principal-images',
    templateUrl: './load-principal-images.component.html',
    styleUrls: ['./load-principal-images.component.css'],
    standalone: false
})
export class LoadPrincipalImagesComponent {

  files: File[] = [];
  disabled: Boolean = true;
  loading: Boolean = false;
  result:  Result = {
    reload: false
  }

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<ScheduleEventsComponent>,
    private _principalImages: AdminService,
    private _router: Router
  ){}

  assignImagePromo( event: Event){
    const element = event.currentTarget as HTMLInputElement;
    let fileList: FileList | null = element.files;
    if (fileList) {
      this.files = Array.from(fileList);
      if (this.files.length > 0) {
        this.disabled = false;
      }else{
        this.disabled = true;
      }
    }
  }

  public uploadPrincipalImages(){
    // console.log(this.data.type);
    this.disabled = true;
    this.loading = true;
    this._principalImages.setPrincipalImages(this.data.type, this.files)
      .subscribe({
        next:(resp) => {
          if (resp.status == "success") {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Carga correcta de imágenes',
              showConfirmButton: true,
              confirmButtonColor: '#008bcc',
              timer: 3500
            });
            this.loading = false;
            this.result.reload = true;
            this._bottomSheetRef.dismiss(this.result);
          }
        },
        error: (error:any) => {

          this.loading = false;
          this._bottomSheetRef.dismiss(this.result);
          reload(error, this._router);
        }
      })
  }

  close(){
    this._bottomSheetRef.dismiss(this.result);
  }

}
