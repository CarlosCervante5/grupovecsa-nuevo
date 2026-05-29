import { Component, OnInit } from '@angular/core';
import { UntypedFormBuilder, UntypedFormGroup, Validators } from '@angular/forms';
import { Title } from '@angular/platform-browser';
import { ActivatedRoute, Router } from '@angular/router';
import Swal from 'sweetalert2';
import { catchError, finalize, of } from 'rxjs';
import { AuthService } from '../../services/auth.service';
import { LoginResponse } from '@interfaces/auth.interface';
import { BoutiqueCartService } from 'src/app/boutique/services/boutique-cart.service';
import { adminRouteSegmentForRole } from 'src/app/admin/utils/admin-route.util';
import { markPostLoginLoading } from '../../constants/post-login-loading';

@Component({
    selector: 'app-login2',
    templateUrl: './login2.component.html',
    styleUrls: ['./login2.component.css'],
    standalone: false
})
export class Login2Component implements OnInit {

    // References of Help
    public hide: boolean = true;
    public spinner: boolean = false;  

    // Form References
    public form!: UntypedFormGroup;

    constructor(
        private _authService: AuthService,
        private _formBuilder: UntypedFormBuilder, 
        private _router: Router,
        private _route: ActivatedRoute,
        private titleService: Title,
        private _cartService: BoutiqueCartService
    ) { 
        // Set Title View
        this.titleService.setTitle('BMW VECSA HIDALGO');

        // Create form
        this.createForm();
    }

    ngOnInit(): void {
        const token = localStorage.getItem('user_token');
        const role = localStorage.getItem('role');
        if (!token || !role) {
            return;
        }
        const dest =
            role === 'client'
                ? '/auth/mi-cuenta'
                : `/admin/${adminRouteSegmentForRole(role)}`;
        if (dest === '/admin/') {
            return;
        }
        void this._router.navigateByUrl(dest);
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

                const returnUrl = this._route.snapshot.queryParamMap.get('returnUrl');
                const safeReturnUrl = this.resolveSafeReturnUrl(returnUrl, loginResponse.data.role);

                const afterCartSync$ = safeReturnUrl && this.isBoutiqueReturnUrl(safeReturnUrl)
                    ? this._cartService.syncLocalCartToServer().pipe(catchError(() => of(undefined)))
                    : of(undefined);

                afterCartSync$.pipe(
                    finalize(() => {
                        sessionStorage.removeItem('vecsa_chunk_reload');
                        markPostLoginLoading();
                        if (safeReturnUrl) {
                            window.location.assign(safeReturnUrl);
                        } else if (loginResponse.data.role === 'client') {
                            window.location.assign('/auth/mi-cuenta');
                        } else {
                            const seg = adminRouteSegmentForRole(loginResponse.data.role);
                            window.location.assign(seg ? `/admin/${seg}` : '/');
                        }
                        this.spinner = false;
                    }),
                ).subscribe();

                if (!safeReturnUrl || !this.isBoutiqueReturnUrl(safeReturnUrl)) {
                    this._cartService.syncLocalCartToServer().subscribe();
                }

            },
            error: ( errorResponse ) => {
                const status = errorResponse?.status ?? 0;
                const msg =
                    status === 401
                        ? 'Correo o contraseña incorrectos. En sandbox la cuenta debe existir en la base de datos de pruebas (no es la misma que producción).'
                        : (errorResponse?.error?.message || 'No se pudo iniciar sesión. Intenta de nuevo.');

                Swal.fire({
                    icon: 'error',
                    title: 'Error al autenticar usuario',
                    text: msg,
                    showConfirmButton: true,
                    confirmButtonColor: '#EEB838'
                });

                this.spinner = false;
            }
        });
    }

    /** Rutas internas permitidas tras login (evita open redirect). */
    private resolveSafeReturnUrl(returnUrl: string | null, role: string): string | null {
        if (!returnUrl || !returnUrl.startsWith('/') || returnUrl.startsWith('//')) {
            return null;
        }

        if (returnUrl.startsWith('/boutique')) {
            return returnUrl;
        }

        if (role === 'client') {
            return returnUrl;
        }

        return null;
    }

    private isBoutiqueReturnUrl(url: string): boolean {
        return url === '/boutique' || url.startsWith('/boutique/');
    }
}
