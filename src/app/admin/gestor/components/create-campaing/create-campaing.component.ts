import { Component } from '@angular/core';
import { FormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
//import { CampaingService } from '../../services/campaing.service';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-create-campaing',
    templateUrl: './create-campaing.component.html',
    styleUrls: ['./create-campaing.component.css'],
    standalone: false
})
export class CreateCampaingComponent {

  public form!: FormGroup;
  constructor(
    private _formBuilder: UntypedFormBuilder,
    private _bottomSheetRef: MatBottomSheetRef<any>,
    private _campaingService: AdminService,
    private _router: Router
  ){
    this.formInit();
  }

  private formInit() {
    this.form = this._formBuilder.group({
        name:               ['', [Validators.required]],
        begin_date:         ['', [Validators.required]],
        end_date:           ['', [Validators.required]],
        description:        ['', ],
        segment_name:       ['', [Validators.required]],
        category:           ['', [Validators.required]],
        visits:             [0]
    });
}

onSubmit() {
    this._campaingService.setCampaing(this.form.value)
    .subscribe({
      next: ()=> {
        Swal.fire({                    
          icon: 'success',
          title: 'Campaña creada exitosamente.',
          showConfirmButton: false,
          timer: 2000
        });

        this._bottomSheetRef.dismiss(
          {reload: true}
        );
    },
    error: (error) => {
      reload(error, this._router);
    }
    })
}

public close():void {
  this._bottomSheetRef.dismiss();
}

}
