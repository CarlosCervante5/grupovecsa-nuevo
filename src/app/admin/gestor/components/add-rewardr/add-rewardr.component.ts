import { Component } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatBottomSheetRef } from '@angular/material/bottom-sheet';
import { rewardsResponse } from '@interfaces/admin.interfaces';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-add-rewardr',
    templateUrl: './add-rewardr.component.html',
    styleUrls: ['./add-rewardr.component.css'],
    standalone: false
})
export class AddRewardrComponent {
  form!: FormGroup;

  constructor(
    private fb: FormBuilder, 
    private _bottomSheetRef: MatBottomSheetRef<any>,
    private _eventsService:AdminService,
    private _router: Router
  ){
    this.createForm();
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
  // get nameInvalid() {
  //   return this.form.get('name')!.invalid && (this.form.get('name')!.dirty || this.form.get('name')!.touched);
  // }

  // get descInvalid() {
  //   return this.form.get('description')!.invalid && (this.form.get('description')!.dirty || this.form.get('description')!.touched);
  // }

  // get fecha_iInvalid() {
  //   return this.form.get('fecha_i')!.invalid && (this.form.get('fecha_i')!.dirty || this.form.get('fecha_i')!.touched);
  // }

  // get fecha_fInvalid() {
  //   return this.form.get('fecha_f')!.invalid && (this.form.get('fecha_f')!.dirty || this.form.get('fecha_f')!.touched);
  // }

  // get typeInvalid() {
  //   return this.form.get('type')!.invalid && (this.form.get('type')!.dirty || this.form.get('type')!.touched);
  // }

  onSubmit(){

    this._eventsService.newRewards( 
      this.form.value ).subscribe({
      next: (resp) => {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Evento creado con exito',
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

  close() {
    this._bottomSheetRef.dismiss('data');
  }


}
