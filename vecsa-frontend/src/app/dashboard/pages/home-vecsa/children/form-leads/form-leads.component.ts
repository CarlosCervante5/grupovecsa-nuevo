import { Component } from '@angular/core';
import { FormGroup, FormControl, UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { HomeService } from '../../../../services/home.service';
import Swal, { SweetAlertIcon } from 'sweetalert2'
@Component({
    selector: 'app-form-leads',
    templateUrl: './form-leads.component.html',
    styleUrls: ['./form-leads.component.css'],
    standalone: false
})
export class FormLeadsComponent {
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
        message: ['', [Validators.required]]
      }
    );
  }

  onSubmit(){    
    // this.disabled = true;
    // this._homeService.generateLead(this.formLead.value)
    // .subscribe({
    //   next: (response: any) => {
    //     if (response['status'] === 'success') {
    //       Swal.fire({
    //         icon: 'success',
    //         title: 'Envio correctamente',
    //         text: `Solicitud de información enviada correctamente, nos podremos en contacto con usted lo antes posible.`,
    //         showConfirmButton: true,
    //         confirmButtonColor: '#6689ff',
    //         timer: 3500
    //       });     
          
    //       this.formLead.reset();
    //       this.disabled = false;
    //     } else {
    //       Swal.fire({
    //         icon: 'error',
    //         title: 'Ooopppps!',
    //         text: `Al parecer ocurrio un error al intentar enviar la solicitud de información, intenta más tarde.`,
    //         showConfirmButton: true,
    //         confirmButtonColor: '#6689ff',
    //         timer: 3500
    //       });      
    //       this.disabled = false;        
    //     }
    //   }
    // });
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
  
  get messageInvalid() {
    return this.formLead.get('message')!.invalid && (this.formLead.get('message')!.dirty || this.formLead.get('message')!.touched);
  }  
}
