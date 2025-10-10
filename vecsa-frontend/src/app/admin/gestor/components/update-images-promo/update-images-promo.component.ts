import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { ImageOrder } from '../../../../shared/interfaces/admin.interfaces';
import { environment } from 'src/environments/environment';
import { CdkDragDrop, moveItemInArray } from '@angular/cdk/drag-drop';
import { ImagesPromoService } from '../../services/images-promo.service';
import Swal from 'sweetalert2';
import { PromotionsComponent } from '../../pages/promotions/promotions.component';

// import { EventsService } from '../../services/events.service';
import { AdminService } from '@services/admin.service';
import { FormBuilder, FormGroup } from '@angular/forms';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';
interface Result {  
  reload: boolean;
}

@Component({
    selector: 'app-update-images-promo',
    templateUrl: './update-images-promo.component.html',
    styleUrls: ['./update-images-promo.component.css'],
    standalone: false
})
export class UpdateImagesPromoComponent {

  // References
  public imagesForSlider: ImageOrder[] = [];
  private baseUrl: string = environment.baseUrl;
  public form!: FormGroup;
  public mylegales!: HTMLElement;
  public disabled: Boolean[] = [];

  public result:Result = {      
    reload: false
  }

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<PromotionsComponent>,
    private _imagesPromoService: ImagesPromoService,
    private _eventsService: AdminService,
    private fb: FormBuilder,
    private _router: Router
  ){
    
    for (let i = 0; i < this.data.images.length; i++) {

      this.disabled.push(true);

      if (this.data.images[i].type == 'principal') {
        this.imagesForSlider.push(
          {
            id: this.data.images[i].id,
            path: this.baseUrl + '/api/getEventImage/' + this.data.images[i].path,
            legal: this.data.images[i].legal
          }
        );
      }else{

        this.imagesForSlider.push(
          {
            id: this.data.images[i].id,
            path: this.baseUrl + '/api/getPromotionImage/' + this.data.images[i].path,
            legal: this.data.images[i].legal
          }
        );

      }

    }
  }

  public onActive(index: number){
    this.disabled.map( (v, i) => i != index ? this.disabled[i] = true : this.disabled[i] = false );
  }

  public onSubmit(id: number, legales: any, index: number){
    this.disabled[index] = true;
    this.mylegales = legales.value;
    this._imagesPromoService.updateLegal(id, this.mylegales.toString())
      .subscribe( {
        next: (resp)=>{
          Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Legal actualizado con éxito',
            showConfirmButton: true,
            confirmButtonColor: '#008bcc',
            timer: 3500
          }); 
        },
        error: (error:any) => {
          reload(error, this._router);
        }
      });
  }
  drop(event: CdkDragDrop<ImageOrder[]>){
    moveItemInArray(this.imagesForSlider, event.previousIndex, event.currentIndex);
  }

  isDragDrop(object: any): object is CdkDragDrop<ImageOrder[]> {
    return 'previousIndex' in object;
  }

  public changeOrderPromo():void {
    
    if (this.data.type == 'principal') {
      this._imagesPromoService.changeOrderPrincipal(this.data.type, this.imagesForSlider)
      .subscribe({
        next:(resp)=>{
          Swal.fire({
            icon: 'success',
            title: resp.message,
            showConfirmButton: false,
            timer: 2000
          });
          this.result.reload = true;
          this._bottomSheetRef.dismiss(this.result);
        },
        error: (error:any) => {
          reload(error, this._router);
        }
      });
    }else{
      this._imagesPromoService.changeOrderPromo(this.data.brand, this.imagesForSlider)
      .subscribe({
        next:(resp)=>{
          Swal.fire({
            icon: 'success',
            title: resp.message,
            showConfirmButton: false,
            timer: 2000
          });
          this.result.reload = true;
          this._bottomSheetRef.dismiss(this.result);
        },
        error: (error:any) => {
          reload(error, this._router);
        }
      });
    }

  }

  public deleteImagePromo( vehicle_image_id: number ): void {
    Swal.fire({
      title: `Estas segur@ que quieres eliminar esta imagen?`,
      showCancelButton: true,
      confirmButtonText: 'Eliminar',
      confirmButtonColor: '#008bcc'
    }).then((result) => {
      if (result.isConfirmed) {

        if (this.data.type == 'principal') {
          this._imagesPromoService.deletePromoImage( vehicle_image_id )
            .subscribe(
              (resp) => {
                Swal.fire({
                  icon: 'success',
                  title: resp.message,
                  showConfirmButton: true,
                  confirmButtonText: 'Aceptar',
                  confirmButtonColor: '#008bcc'
                }).then((answer) => {
                  if (answer.isConfirmed) {
                    this.imageDelete();
                    setTimeout(() => {
                      this.emptyImagesForSlider();
                    }, 1000);
                  }else{
                    this.imageDelete();
                    setTimeout(() => {
                      this.emptyImagesForSlider();
                    }, 1000);
                  }
                });
              }
            );
        }else{
          this._imagesPromoService.deleteImage( vehicle_image_id )
            .subscribe(
              (resp) => {
                Swal.fire({
                  icon: 'success',
                  title: resp.message,
                  showConfirmButton: true,
                  confirmButtonText: 'Aceptar',
                  confirmButtonColor: '#008bcc'
                }).then((answer) => {
                  if(answer.isConfirmed){
                    this.imageDelete();
                    setTimeout(() => {
                      this.emptyImagesForSlider();
                    }, 1000);
                  }else{
                    this.imageDelete();
                    setTimeout(() => {
                      this.emptyImagesForSlider();
                    }, 1000);
                  }
                });
              }
            );
        }

      }
    });
  }

  public imageDelete(){

    if (this.data.type == 'principal') {
      this.imagesForSlider = [];
      this._eventsService.getEvents(this.data.type)
        .subscribe( resp => {
          for (let i = 0; i < resp.events.length; i++) {
            this.imagesForSlider.push(
              {
                id: resp.events[i].id,
                path: this.baseUrl + '/api/getEventImage/' + resp.events[i].path,
                legal: this.data.images[i].legal
              }
            );
          }
        });
    }else{
      this.imagesForSlider = [];
      this._imagesPromoService.getPromotionsByBrand(this.data.brand)
        .subscribe( resp => {
          for (let i = 0; i < resp.promotions.length; i++) {
            this.imagesForSlider.push(
              {
                id: resp.promotions[i].id,
                path: this.baseUrl + '/api/getPromotionImage/' + resp.promotions[i].path,
                legal: this.data.images[i].legal
              }
            );
          }
        });
    }

  }

  public emptyImagesForSlider(){
    if (this.imagesForSlider.length == 0) {
      this.result.reload = true;
      this._bottomSheetRef.dismiss(this.result);
    }
  }

}
