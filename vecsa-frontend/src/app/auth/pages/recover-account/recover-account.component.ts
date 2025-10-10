import { Component, OnInit } from '@angular/core';
import { UntypedFormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { Title } from '@angular/platform-browser';

// Alertas
import Swal from 'sweetalert2';

// Services
import { AuthService } from '../../services/auth.service';

@Component({
    selector: 'app-recover-account',
    templateUrl: './recover-account.component.html',
    styles: [`
    mat-form-field { 
      width: 100%;
    }

    button {
      width: 80%;
    }
  `],
    standalone: false
})

export class RecoverAccountComponent {
    public hide: boolean = true;
    public spinner: boolean = false;  
    public form!: UntypedFormGroup;

    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder, 
        private _router: Router,
        private titleService: Title
    ) {
        // Set Title View
        this.titleService.setTitle('Recuperar Cuenta');

        this.createForm();
    }

    get emailInvalid() {
        return this.form.get('email')!.invalid && (this.form.get('email')!.dirty || this.form.get('email')!.touched);
    }

    public createForm() {
        this.form = this._formBuilder.group({
            email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]]
        });
    }

    public onSubmit() { 
        this.spinner = true;

        this._authService.recoverAccount( this.form.get('email')!.value )
        .subscribe({
        next: () => {
            Swal.fire({
                icon: 'success',
                title: 'Se ha enviado un mail para recuperar tu cuenta!',
                text: 'Revisa tu correo',
                confirmButtonColor: '#EEB838',
            }).then( () => {
                this.spinner = false;
            })
        },
        error: (error) => {
            this.spinner = false;

            Swal.fire({
                icon: 'error',
                title: 'Lo sentimos!',
                text: 'No se ha podido realizar la recuperación de la cuenta, pongase en contacto con soporte.',
                confirmButtonColor: '#EEB838',
            });
        }
        });
    }

}
