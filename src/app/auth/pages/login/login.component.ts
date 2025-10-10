import { Component } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { Title } from '@angular/platform-browser';
import { Router } from '@angular/router';

// Animations
import Swal from 'sweetalert2';

// Services
import { AuthService } from '../../services/auth.service';

// Interfaces
import { LoginResponse } from '@interfaces/auth.interface';

@Component({
    selector: 'app-login',
    templateUrl: './login.component.html',
    styles: [`
    .container{
      margin-top:73px;
    }

    mat-form-field { 
      width: 100%;
    }

    button {
      width: 80%;
    }
  `],
    standalone: false
})

export class LoginComponent {

    // References of Help
    public hide: boolean = true;
    public spinner: boolean = false;  

    // Form References
    public form!: UntypedFormGroup;

    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder, 
        private _router: Router,
        private titleService: Title
    ) { 
        // Set Title View
        this.titleService.setTitle('BMW VECSA HIDALGO');

        // Create form
        this.createForm();
    }

    /**
     * Getters Inputs Check
     */
    get emailInvalid() {
        return this.form.get('email')!.invalid && (this.form.get('email')!.dirty || this.form.get('email')!.touched);
    }
  
    get passwordInvalid() {
        return this.form.get('password')!.invalid && this.form.get('password')!.dirty;
    }

    get passwordLength() {
        let password = this.form.get('password')!.value;
        return this.form.get('password')!.touched && (password.length < 8 || password.length > 32); 
    }

    /**
     * Login Form Initialization
     */
    // public createForm() {
    //     this.form = this._formBuilder.group({
    //     email: ['', [Validators.required, Validators.pattern("[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$")]],
    //     password: ['', [Validators.required, Validators.pattern(/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+/), Validators.minLength(8), Validators.maxLength(32)]]  
    //     });
    // }

    public createForm() {
        this.form = this._formBuilder.group({
            email: ['', [Validators.required, Validators.pattern("[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$")]],
            password: ['', [Validators.required, Validators.pattern(/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+/), Validators.minLength(8), Validators.maxLength(32)]]
        });
    }

    /**
     * Form Client Information
     */
    public onSubmit() { 
        // Change spinner
        this.spinner = true;

        // Launch request
        this._authService.login(this.form.value)
        .subscribe({
            next: ( loginResponse : LoginResponse) => {
                localStorage.setItem('user_token', loginResponse.data.token);
                localStorage.setItem('user', JSON.stringify( loginResponse.data.user));
                localStorage.setItem('role', loginResponse.data.role);
                localStorage.setItem('profile', JSON.stringify( loginResponse.data.profile));

                if( loginResponse.data.role === 'client'){
                    this._router.navigate(['/auth/mi-cuenta'])
                } else {
                    this._router.navigate(['/admin', loginResponse.data.role])
                }

            },
            error: ( errorResponse ) => {

                Swal.fire({
                    icon: 'error',
                    title: 'Error al autenticar usuario',
                    text: errorResponse.error.message,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838'
                });

                this.spinner = false;
            }
        });
    }

}
