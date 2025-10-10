import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import Swal from 'sweetalert2';
import {CdkDragDrop, moveItemInArray} from '@angular/cdk/drag-drop';

import { environment } from '@environments/environment';

// Interfaces
import { ImageOrder } from '@interfaces/vehicle_data.interface';

// import { CampaingService } from '../../services/campaing.service';
import { AdminService } from '@services/admin.service';
import { PromotionsComponent } from '../../pages/promotions/promotions.component';

//prueba
import { ImageOrderPromo } from '@interfaces/admin.interfaces';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

interface Result {
  reload: boolean;
  first_image:boolean;
}

@Component({
    selector: 'app-update-images',
    templateUrl: './update-images.component.html',
    styleUrls: ['./update-images.component.css'],
    standalone: false
})
export class UpdateImagesComponent {
  public imagesForSlider: ImageOrderPromo[] = [];
  private baseUrl: string = environment.baseUrl;
  public sort_id!:number;
  public result:Result = {      
    reload: false,
    first_image:false
  }     

  constructor(@Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
  private _imagesService:AdminService,   
  private _bottomSheetRef: MatBottomSheetRef<PromotionsComponent>,
  private _router: Router
 ){
    if( this.data.images.length == 0 ) {
      this.imagesForSlider.push(
        {
          id: 0,
          uuid: 0,
          path: this.baseUrl + '/api/image_vehicle/vacio',
          external_website: null,
         }
      );      
    }
    for (let i = 0; i < this.data.images.length; i++) {
        this.imagesForSlider.push(
          {
            id: this.data.images[i].sort_id,
            uuid: this.data.images[i].uuid,
            path: this.data.images[i].image_path,
            external_website: "no",
          }
        );

    }
    
    this.sort_id = data.campaign_id;


  }

  drop(event: CdkDragDrop<ImageOrder[]>) {
    moveItemInArray(this.imagesForSlider, event.previousIndex, event.currentIndex);
  }

  isDragDrop(object: any): object is CdkDragDrop<ImageOrder[]> {
    return 'previousIndex' in object;
  }


  changeOrder():void{

    this._imagesService.changeOrder( this.imagesForSlider )
        .subscribe({
          next: (resp) => {
            Swal.fire({                    
              icon: 'success',
              title: resp.message,
              showConfirmButton: false,
              timer: 2000
            });            
            this.result.reload = true;  
            this._bottomSheetRef.dismiss(
              this.result
            );
          },
          error: (err) => {
            reload(err, this._router);
          }
        }
        )
  }

  deleteimage( promo_image_id:number, index:number ):void {

    Swal.fire({
      title: 'Estas segur@ que quieres eliminar esta promoción?',
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc',
    }).then((result) => {
      /* Read more about isConfirmed, isDenied below */
      if (result.isConfirmed) {
        this._imagesService.deleteImage( promo_image_id )
            .subscribe(
             {
              next: (resp) => {
                this.imagesForSlider.splice(index, 1);
                Swal.fire(resp.message, '', 'success');
              },
              error:(error:any)=>{
                reload(error, this._router);
              }
             }
            )
      }
    })
  }

}
