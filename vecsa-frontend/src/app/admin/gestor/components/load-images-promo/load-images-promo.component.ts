import { Component, Inject } from '@angular/core';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
// import { ImagesPromoService } from '../../services/images-promo.service';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import { PromotionsComponent } from '../../pages/promotions/promotions.component';
import { FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';

import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

interface Result {
  reload: boolean;
}

@Component({
    selector: 'app-load-images-promo',
    templateUrl: './load-images-promo.component.html',
    styleUrls: ['./load-images-promo.component.css'],
    standalone: false
})
export class LoadImagesPromoComponent {
  public form!: FormGroup;
  files: File[] = [];
  promo: string= '';
  link: string= '';
  disabled: Boolean = true;
  result:  Result = {
    reload: false
  }
  resp2 = false;
  loading: Boolean = false;

  constructor(
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<PromotionsComponent>,
    private _imagePromoService: AdminService,
    private _formBuilder: UntypedFormBuilder,
    private _router: Router
  ){
    this.formInit();
  }

  private formInit(){
    this.form = this._formBuilder.group({
      name:    ['', [Validators.required]],
      spec_sheet:    ['', [Validators.required]],
    })
  }

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

  uploadImagesPromo(){
    this.disabled = true;
    this.loading = true;
    this.promo = this.form.get('name')?.value;
    this.link = this.form.get('spec_sheet')?.value;
    this._imagePromoService.setImagesPromo( this.data.campaign, this.files, this.promo, this.link)
    
    .subscribe({
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
        reload(err, this._router);
        this.loading = false;
        this._bottomSheetRef.dismiss(this.result);
      }
    });
  }

  close(){
    this._bottomSheetRef.dismiss(this.result);
  }

}
