import { Component } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { Title } from '@angular/platform-browser';
import { Router } from '@angular/router';
import Swal from 'sweetalert2';
import { AuthService } from '../../services/auth.service';
import { LoginResponse } from '@interfaces/auth.interface';
import { BoutiqueCartService } from 'src/app/boutique/services/boutique-cart.service';
import { adminRouteSegmentForRole } from 'src/app/admin/utils/admin-route.util';

@Component({
    selector: 'app-login2',
    templateUrl: './login2.component.html',
    styleUrls: ['./login2.component.css'],
    standalone: false
})
export class Login2Component {


    // References of Help
    public hide: boolean = true;
    public spinner: boolean = false;  

    // Form References
    public form!: UntypedFormGroup;

    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder, 
        private _router: Router,
        private titleService: Title,
        private _cartService: BoutiqueCartService
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
                if (loginResponse.data.permissions) {
                  localStorage.setItem('permissions', JSON.stringify(loginResponse.data.permissions));
                }

                const dest = loginResponse.data.role === 'client'
                    ? ['/auth/mi-cuenta']
                    : ['/admin', adminRouteSegmentForRole(loginResponse.data.role)];
                this._router.navigate(dest).finally(() => { this.spinner = false; });

                this._cartService.syncLocalCartToServer().subscribe();

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
