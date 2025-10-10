import { Component, OnInit } from '@angular/core';
import { AbstractControlOptions, UntypedFormGroup, UntypedFormBuilder, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';

// Services
import { AuthService } from '../../services/auth.service';
import { ResetPassword } from '@interfaces/auth.interface';

// Alertas
import Swal from 'sweetalert2';
@Component({
    selector: 'app-password-reset',
    templateUrl: './password-reset.component.html',
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
export class PasswordResetComponent implements OnInit {
    public hide: boolean = true;
    public spinner: boolean = false;  
    public form!: UntypedFormGroup;

    private token_user:string = '';
    private token_validate:string = '';

    constructor(
        private _formBuilder: UntypedFormBuilder, 
        private _activatedRoute: ActivatedRoute,
        private _authService: AuthService,
        private _router: Router
    ) {
        this.createForm();
    }

    ngOnInit(): void {
        this._activatedRoute.params
        .subscribe({
            next: (params) => {
                this.token_user = params['token_user'];
                this.token_validate = params['token_validate'];
            }
        });
    }

    get passwordInvalid() {
        return this.form.get('password')!.invalid && this.form.get('password')!.dirty;
    }

    get passwordLength() {
        let password = this.form.get('password')!.value;
        return this.form.get('password')!.touched && (password.length < 8 || password.length > 32);
    }

    public createForm() {
        this.form = this._formBuilder.group({
            password: ['', [Validators.required, Validators.pattern(/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+/), Validators.minLength(8), Validators.maxLength(32)]],
            confirmPassword: ['', Validators.required]
        }, {validators: [this.passwordMatchValidator]} as AbstractControlOptions);
    }

    public onSubmit() {
        this._authService.resetPassword( this.token_user, this.token_validate, this.form.get('password')?.value, this.form.get('confirmPassword')?.value )
        .subscribe({
            next: (resp: ResetPassword) => {

                Swal.fire({
                    icon: 'success',
                    title: 'Proceso exitoso',
                    text: resp.message,
                    confirmButtonColor: '#EEB838',
                }).then( () => {
                    this._router.navigate(['/', 'auth', 'iniciar-sesion']);
                });

            },
            error: () => {
                
                Swal.fire({
                    icon: 'error',
                    title: 'Oupps..',
                    text: 'Al parecer ocurrio un error al restablecer la contraseña, verifique y vuelva a intentarlo.',
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838',
                    timer: 3500
                });
    
                this.spinner = false;
            }

        });
    }

    passwordMatchValidator(formGroup: UntypedFormGroup) {
        return formGroup.get('password')!.value === formGroup.get('confirmPassword')!.value ? null : { mismatch: true };
    }

}
