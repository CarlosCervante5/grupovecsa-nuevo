import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { EventsService } from '../../../services/events.service';
import Swal from 'sweetalert2';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

interface Result {  
  reload: boolean;
}
@Component({
    selector: 'app-load-video',
    templateUrl: './load-video.component.html',
    styleUrls: ['./load-video.component.css'],
    standalone: false
})
export class LoadVideoComponent {
  files:File[] = [];
  disabled:Boolean = true;
  loading:Boolean = false;
  result:Result = {      
    reload: false
  }    
  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<any>,   
    private _eventsService: EventsService,
    private _router: Router
  ) {}

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

  uploadVideo(){
    this.disabled = true;
    this.loading = true;
    this._eventsService.uploadVideo(      
      this.files[0]
    ).subscribe({
      next: (resp) => {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Carga de video completada',
          showConfirmButton: true,
          confirmButtonColor: '#008bcc',
          timer: 3500
        });
        this.loading = false;
        this.result.reload = true;
        this._bottomSheetRef.dismiss(
          this.result
        );
      },
      error: (err) => {
        // Animation request  
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
