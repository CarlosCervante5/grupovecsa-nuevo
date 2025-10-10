import { Component } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { HomeService } from 'src/app/dashboard/services/home.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-survey-rider',
    templateUrl: './survey-rider.component.html',
    styleUrls: ['./survey-rider.component.css'],
    standalone: false
})
export class SurveyRiderComponent {
  formLead:UntypedFormGroup;
  disabled:boolean = false;

  constructor(
    private fb: UntypedFormBuilder,
    private _homeService:HomeService
  ) {
    this.formLead = this.fb.group(
      {
        name: ["",[Validators.required]],
        email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],       
        phone: ['', [Validators.required, Validators.pattern("[0-9]+"), Validators.minLength(10), Validators.maxLength(10)]],   
        model: ["",[Validators.required]],
        gloves: ["",[Validators.required]],
        jacket: ["",[Validators.required]],
        footwear: ["",[Validators.required]],
        helmet: ["",[Validators.required]],
        color: ["",[Validators.required]],
      }
    );
  }

  onSubmit(){    
    this.disabled = true;

 
    this._homeService.generateRider(this.formLead.value)
        .subscribe({
          next: (response: any) => {
              Swal.fire({
                icon: 'success',
                title: 'Envio correcto',
                text: `GRACIAS POR CONTESTAR NUESTRA ENCUESTA.`,
                showConfirmButton: true,
                confirmButtonColor: '#6689ff',
                timer: 3500
              });     
              
              this.formLead.reset();
              this.formLead.untouched;
              this.disabled = false;

              setTimeout(function() {
                window.location.href='https://bmwvecsahidalgo.com/shop/';
              }, 3500);
          },
          error: (err) => {
            Swal.fire({
              icon: 'error',
              title: 'Ooopppps!',
              text: `Al parecer ocurrio un error al intentar enviar la solicitud de información, intenta más tarde.`,
              showConfirmButton: true,
              confirmButtonColor: '#6689ff',
              timer: 3500
            });
            this.disabled = false;
          }
        });
  }

  get nameInvalid() {
    return this.formLead.get('name')!.invalid && (this.formLead.get('name')!.dirty || this.formLead.get('name')!.touched);
  }  

  get emailInvalid() {
    return this.formLead.get('email')!.invalid && (this.formLead.get('email')!.dirty || this.formLead.get('email')!.touched);
  }

  get phoneInvalid() {
    return this.formLead.get('phone')!.invalid && (this.formLead.get('phone')!.dirty || this.formLead.get('phone')!.touched);
  }
  
  get modelInvalid() {
    return this.formLead.get('model')!.invalid && (this.formLead.get('model')!.dirty || this.formLead.get('model')!.touched);
  }  

  get colorInvalid() {
    return this.formLead.get('color')!.invalid && (this.formLead.get('color')!.dirty || this.formLead.get('color')!.touched);
  }  

  get helmetInvalid() {
    return this.formLead.get('helmet')!.invalid && (this.formLead.get('helmet')!.dirty || this.formLead.get('helmet')!.touched);
  }

}
