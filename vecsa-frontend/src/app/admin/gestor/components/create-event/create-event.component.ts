import { Component, Inject } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MAT_BOTTOM_SHEET_DATA, MatBottomSheetRef } from '@angular/material/bottom-sheet';
// import { EventsService } from '../../services/events.service';
import { AdminService } from '@services/admin.service';
import Swal from 'sweetalert2';
import {reload} from '@helpers/session.helper';
import { Router } from '@angular/router';

@Component({
    selector: 'app-create-event',
    templateUrl: './create-event.component.html',
    styleUrls: ['./create-event.component.css'],
    standalone: false
})
export class CreateEventComponent {
  form!: FormGroup;
  files:File[] = [];
  disabled:Boolean = true;
  loading:Boolean = false;

  fileList!: FileList | null;

  constructor(
    @Inject (MAT_BOTTOM_SHEET_DATA) public data: any,
    private _bottomSheetRef: MatBottomSheetRef<any>,   
    private fb: FormBuilder, 
    private _eventsService:AdminService,
    private _router: Router
  ) {
    this.createForm();
    
  }

  createForm() {
    this.form = this.fb.group({
       title: ['', Validators.required ],       
       description: ['', Validators.required ],
      //  brand: ['', Validators.required ],
       event_date: ['', Validators.required ],       
      //  picture: ['', Validators.required ],
       picture: [null, Validators.required ],
       type: [ this.data.type, Validators.required ]
    });
  }

  get titleInvalid() {
    return this.form.get('title')!.invalid && (this.form.get('title')!.dirty || this.form.get('title')!.touched);
  }

  get descriptionInvalid() {
    return this.form.get('description')!.invalid && (this.form.get('description')!.dirty || this.form.get('description')!.touched);
  }

  // get brandInvalid() {
  //   return this.form.get('brand')!.invalid && (this.form.get('brand')!.dirty || this.form.get('brand')!.touched);
  // }

  get event_dateInvalid() {
    return this.form.get('event_date')!.invalid && (this.form.get('event_date')!.dirty || this.form.get('event_date')!.touched);
  }

  get pictureInvalid() {
    return this.form.get('picture')!.invalid && (this.form.get('picture')!.dirty || this.form.get('picture')!.touched);
  }

  assignImage( event: Event ){
    const element = event.currentTarget as HTMLInputElement;
    this.fileList = element.files;
    
    // if (fileList) {  
    //   this.files = Array.from(fileList);  
    //   if(this.files.length > 0){
    //     this.disabled = false;
    //   }else{
    //     this.disabled = true;
    //   }
    // }  
    
    // const input = event.target as HTMLInputElement;
    // if (input.files && input.files.length > 0) {
    //   this.fileName = input.files[0].name;
    //   this.form.controls['picture'].setValue(input.files[0]); // Actualiza el control del formulario
    //   this.form.controls['picture'].markAsTouched(); // Marca el control como tocado
    //   this.form.controls['picture'].updateValueAndValidity(); // Valida el control
    //   this.disabled = false;
    // }else {
    //   this.fileName = 'Ningún archivo seleccionado';
    //   this.form.controls['picture'].setValue(null);
    //   this.disabled = true;
    // }

  }

  onSubmit(){
    this.loading = true;
    // console.log(this.form.controls.title.value,      
    //     this.form.controls.description.value,
    //     this.form.controls.type.value,
    //     this.form.controls.event_date.value,      
    //     this.fileList![0]);
    
    this._eventsService.createEvent( 
      this.form.controls.title.value,      
      this.form.controls.description.value,
      this.form.controls.type.value,
      this.form.controls.event_date.value,      
      this.fileList![0]
    ).subscribe({
      next: (resp) => {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Evento creado con exito',
          showConfirmButton: true,
          confirmButtonColor: '#008bcc',
          timer: 3500
        });
        this.loading = false;          
        this._bottomSheetRef.dismiss();
      },
      error: (err) => {
        // Animation request  
        reload(err, this._router);
        this.loading = false;
        this._bottomSheetRef.dismiss();
      }
    });
  }

  close() {
    this._bottomSheetRef.dismiss('data');
  }

}
