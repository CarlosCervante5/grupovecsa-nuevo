import { Component, Input } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { AbstractControl, ValidatorFn } from '@angular/forms';
import Swal from 'sweetalert2';
import { LeadsService } from '../../services/leads.service';

export interface Marca {
  brand: string,
  type:string
}
@Component({
    selector: 'app-formulario-cita',
    templateUrl: './formulario-cita.component.html',
    styleUrls: ['./formulario-cita.component.css'],
    standalone: false
})
export class FormularioCitaComponent {

  @Input() Marca!: Marca;

  formCita:UntypedFormGroup;
  disabled:boolean = false;

  constructor(
    private fb: UntypedFormBuilder,
    private _citaservice:LeadsService

   ) {


    this.formCita = this.fb.group(
      {
        name: ["",[Validators.required]],
        email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],       
        phone: ['', [Validators.required, Validators.pattern("[0-9]+"), Validators.minLength(10), Validators.maxLength(10)]],   
        brand: [''],
        description: ['', [Validators.required]],
        type: [''],
        date: ["", [Validators.required, this.dateNotEarlierThanTodayValidator()]],
        hour: ['', [Validators.required]],
        appointment_date: ['' ],
      }
    );
  }


  
    dateNotEarlierThanTodayValidator(): ValidatorFn {
      return (control: AbstractControl): { [key: string]: any } | null => {
        const selectedDate = new Date(control.value);
        const currentDate = new Date(); 
        currentDate.setDate(currentDate.getDate() - 1);
        if (selectedDate < currentDate) {
          return { dateInvalid: true };
        }
        return null;
      };
    }

    
    onSubmit(){    
      this.disabled = true;
      let fecha = this.formCita.get('date')?.value+" "+this.formCita.get('hour')?.value+ ":00";
      this.formCita.controls['appointment_date'].setValue(fecha);
      this.formCita.controls['brand'].setValue(this.Marca.brand.toLowerCase());
      this.formCita.controls['type'].setValue(this.Marca.type.toLowerCase());
      this._citaservice.generateQuote(this.formCita.value)
          .subscribe({
            next: (response: any) => {

              if (response['status'] === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Envio correctamente',
                  text: `Cita a Servicio Agendada Correctamente`,
                  showConfirmButton: true,
                  confirmButtonColor: '#6689ff',
                  timer: 3500
                });     
                
                this.formCita.reset();
                this.disabled = false;
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Ooopppps!',
                  text: `Al parecer ocurrio un error al intentar Agendar tu Cita a Servicio, intenta más tarde.`,
                  showConfirmButton: true,
                  confirmButtonColor: '#6689ff',
                  timer: 3500
                });      
                this.disabled = false;        
              }
            }
          });
    }

  get nameInvalid() {
    return this.formCita.get('name')!.invalid && (this.formCita.get('name')!.dirty || this.formCita.get('name')!.touched);
  }  

  get emailInvalid() {
    return this.formCita.get('email')!.invalid && (this.formCita.get('email')!.dirty || this.formCita.get('email')!.touched);
  }

  get phoneInvalid() {
    return this.formCita.get('phone')!.invalid && (this.formCita.get('phone')!.dirty || this.formCita.get('phone')!.touched);
  }
  
  get messageInvalid() {
    return this.formCita.get('description')!.invalid && (this.formCita.get('description')!.dirty || this.formCita.get('description')!.touched);
  } 

  get dateInvalid() {
    return this.formCita.get('date')!.invalid && (this.formCita.get('date')!.dirty || this.formCita.get('date')!.touched);
  }
  get hourInvalid() {
    return this.formCita.get('hour')!.invalid && (this.formCita.get('hour')!.dirty || this.formCita.get('hour')!.touched);
  }
}
