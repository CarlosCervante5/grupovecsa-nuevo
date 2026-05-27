import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, BehaviorSubject, of, throwError } from 'rxjs';
import { catchError, finalize, map, switchMap, tap } from 'rxjs/operators';

// Form
import { UntypedFormGroup } from '@angular/forms';

// HTTP Client
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';

// Enviroment
import { environment } from '@environments/environment';

// Interfaces
import { RecoverAccount , ResetPassword, LoginResponse, LogoutResponse, RegisterResponse, AuthMeResponse } from '@interfaces/auth.interface';


@Injectable({
    providedIn: 'root'
})

export class AuthService {

    private authStatus = new BehaviorSubject<boolean>(this.hasToken());
    public authStatus$ = this.authStatus.asObservable();

    private permissionsRevision = new BehaviorSubject<number>(0);
    public permissionsRevision$ = this.permissionsRevision.asObservable();

    // Global Url
    private url: string = environment.baseUrl;

    // Headers
    private headers = new HttpHeaders().set('Content-Type', 'application/json').set('X-Requested-With', 'XMLHttpRequest');

    constructor(private _http: HttpClient) { }

    private hasToken(): boolean {
        return !!localStorage.getItem('user_token');
    }

    /**
     * Limpia datos de sesión en el cliente (token inválido, logout, estado huérfano).
     */
    public clearClientAuthState(): void {
        localStorage.removeItem('user_token');
        localStorage.removeItem('user_data');
        localStorage.removeItem('user');
        localStorage.removeItem('role');
        localStorage.removeItem('profile');
        localStorage.removeItem('permissions');
        this.authStatus.next(false);
    }

    /**
     * Cierra sesión en API (si hay token), limpia solo claves de auth y redirige al login.
     */
    public signOut(router: Router, redirectUrl = '/auth/iniciar-sesion'): void {
        const navigate = () => void router.navigateByUrl(redirectUrl);
        if (!localStorage.getItem('user_token')) {
            this.clearClientAuthState();
            navigate();
            return;
        }
        this.logout()
            .pipe(finalize(navigate))
            .subscribe({
                error: () => this.clearClientAuthState(),
            });
    }

    private bearerHeaders(): HttpHeaders {
        const token = localStorage.getItem('user_token');
        return new HttpHeaders()
            .set('Content-Type', 'application/json')
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Authorization', `Bearer ${token}`);
    }

    /**
     * Permisos de la sesión actual (mismo contrato que antes en el login).
     */
    public fetchPermissions(): Observable<string[]> {
        if (!localStorage.getItem('user_token')) {
            return of([]);
        }
        return this._http.get<AuthMeResponse>(`${this.url}/api/auth/me`, { headers: this.bearerHeaders() }).pipe(
            map((res) => {
                const payload = res?.data;
                const perms = Array.isArray(payload?.permissions) ? payload.permissions : [];
                const apiProfile = payload?.profile;
                const apiRole = payload?.role;
                if (apiRole != null && String(apiRole).trim() !== '') {
                    localStorage.setItem('role', String(apiRole).trim());
                }
                if (apiProfile && typeof apiProfile === 'object') {
                    try {
                        const prev = JSON.parse(localStorage.getItem('profile') || '{}');
                        const merged = { ...prev, ...apiProfile };
                        localStorage.setItem('profile', JSON.stringify(merged));
                    } catch {
                        localStorage.setItem('profile', JSON.stringify(apiProfile));
                    }
                }
                return perms;
            }),
            catchError((err: unknown) => {
                const status = err instanceof HttpErrorResponse ? err.status : 0;
                if (status === 401) {
                    this.clearClientAuthState();
                    return of([] as string[]);
                }
                // No pisar permisos en localStorage ante error de red / 5xx (antes se guardaba []).
                return throwError(() => err);
            })
        );
    }

    /**
     * GET /me, persiste permisos y devuelve el array (para guards que evalúan en el mismo flujo).
     */
    public refreshPermissionsForGuard(): Observable<string[]> {
        if (!localStorage.getItem('user_token')) {
            this.clearClientAuthState();
            this.permissionsRevision.next(this.permissionsRevision.value + 1);
            return of([]);
        }
        return this.fetchPermissions().pipe(
            tap((perms) => {
                localStorage.setItem('permissions', JSON.stringify(perms));
                this.permissionsRevision.next(this.permissionsRevision.value + 1);
            }),
        );
    }

    /**
     * Refresca permisos en localStorage (p. ej. tras F5 con token válido).
     */
    public refreshPermissionsInStorage(): Observable<void> {
        return this.refreshPermissionsForGuard().pipe(map(() => undefined));
    }

    /**
     * API Login (POST login + GET /me para permisos)
     */
    public login(credentials: UntypedFormGroup | { email?: string; password?: string }): Observable<LoginResponse> {
        const raw =
            credentials && typeof credentials === 'object' && 'value' in credentials
                ? (credentials as UntypedFormGroup).value
                : credentials;
        const body = {
            email: String(raw?.email ?? '').trim().toLowerCase(),
            password: String(raw?.password ?? ''),
        };
        return this._http.post<LoginResponse>(`${this.url}/api/auth/login`, body, { headers: this.headers }).pipe(
            switchMap((response) => {
                if (!response.data?.token) {
                    return of(response);
                }
                localStorage.setItem('user_token', response.data.token);
                return this._http.get<AuthMeResponse>(`${this.url}/api/auth/me`, { headers: this.bearerHeaders() }).pipe(
                    map((me) => ({
                        ...response,
                        data: {
                            ...response.data,
                            permissions: me.data?.permissions ?? [],
                        },
                    })),
                    catchError(() =>
                        of({
                            ...response,
                            data: { ...response.data, permissions: [] as string[] },
                        })
                    )
                );
            }),
            tap((full) => {
                if (full.data?.token) {
                    const { token, ...userData } = full.data;
                    localStorage.setItem('user_data', JSON.stringify(userData));
                    localStorage.setItem('permissions', JSON.stringify(full.data.permissions ?? []));
                    this.permissionsRevision.next(this.permissionsRevision.value + 1);
                    this.authStatus.next(true);
                }
            })
        );
    }

    /**
     * API Logout
     */
    public logout(): Observable<LogoutResponse> {

        let user_token = localStorage.getItem('user_token');
        let headers = new HttpHeaders().set('Authorization', `Bearer ${user_token}`);

        return this._http.post<LogoutResponse>(`${ this.url }/api/auth/logout`, null , { headers }).pipe(
            tap(() => {
                this.clearClientAuthState();
            }),
            catchError((err: unknown) => {
                const status = err instanceof HttpErrorResponse ? err.status : 0;
                if (status === 401) {
                    this.clearClientAuthState();
                }
                return throwError(() => err);
            })
        );
    }

    public getUserFromStorage(): any | null {
        const userData = localStorage.getItem('user_data');
        if (userData) {
            return JSON.parse(userData);
        }
        return null;
    }

    /**
     * API Register
     */
    public register(user: UntypedFormGroup): Observable<RegisterResponse> {
        return this._http.post<RegisterResponse>(`${ this.url }/api/auth/register`, user, { headers: this.headers });
    }

    /**
     * API recover account
     */
    public recoverAccount(email: string): Observable<RecoverAccount> {
        let body = {
        "email":email
        }
        return this._http.post<RecoverAccount>(`${ this.url }/api/auth/recover_account`, body);
    }

    public resetPassword( token_user :string, token_validate :string, password:string, confirmPassword:string ): Observable<ResetPassword>{
        
        let body = {
        "token_user":token_user,
        "token_validate": token_validate,
        "password":password,
        "password_confirmation":confirmPassword
        }

        return this._http.post<ResetPassword>(`${ this.url }/api/auth/reset_password`, body);
    }
}
