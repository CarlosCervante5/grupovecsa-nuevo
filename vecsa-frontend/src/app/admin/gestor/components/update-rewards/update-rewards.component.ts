import { Component, Inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { DataReward, detailsRewardResponse, rewardsResponse } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-update-rewards',
    templateUrl: './update-rewards.component.html',
    styleUrls: ['./update-rewards.component.css'],
    standalone: false
})
export class UpdateRewardsComponent {
  form!: FormGroup;
  public uuid !: string;
  public reward !: DataReward;
  constructor(
    private fb: FormBuilder, 
    private _bottomSheetRef: MatBottomSheetRef<any>,
    private _eventsService:AdminService,
    @Inject(MAT_BOTTOM_SHEET_DATA) public data: any,
    private _router: Router
  ){
    this.createForm();
    this.uuid = data.uuid;
    this.getReward();
  }

  createForm() {
    this.form = this.fb.group({
       name: ['', Validators.required ],       
       description: ['', Validators.required ],
       begin_date: ['', Validators.required ],
       end_date: ['', Validators.required ],   
      //  type: [null, Validators.required ],
      //  category: [null, Validators.required ],
    });
  }

  close() {
    this._bottomSheetRef.dismiss('');
  }

  getReward() {
    this._eventsService.detailReward(this.uuid)
    .subscribe({
      next: (response: detailsRewardResponse) => {
        setTimeout(() => {
          this.form.patchValue({
            name: response.data.name,
            description: response.data.description,
            begin_date: response.data.begin_date,
            end_date: response.data.end_date
          });
        }, 500);
    },
    error: (error) => {
      reload(error, this._router);
    }
    })
  }

  onSubmit(){
    this._eventsService.updateRewards( 
      this.uuid,
      this.form.get('name')?.value, 
      this.form.get('description')?.value,
      this.form.get('begin_date')?.value,
      this.form.get('end_date')?.value,
      this.form.get('type')?.value)
      .subscribe({
      next: (resp) => {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'actualización de Reward exitosa!!',
          showConfirmButton: true,
          confirmButtonColor: '#008bcc',
          timer: 3500
        });
        this._bottomSheetRef.dismiss();
      },
      error: (err) => {
        // Animation request  
        reload(err, this._router);
        this._bottomSheetRef.dismiss();
      }
    });
  }

}
