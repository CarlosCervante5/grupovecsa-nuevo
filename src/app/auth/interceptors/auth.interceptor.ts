import { Injectable } from '@angular/core';
import {
  HttpErrorResponse,
  HttpEvent,
  HttpHandler,
  HttpInterceptor,
  HttpRequest,
} from '@angular/common/http';
import { Router } from '@angular/router';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';
import { AUTH_LOGIN_PATH } from '../constants/auth-routes';

/** Rutas de API que pueden devolver 401 sin implicar sesión caducada en el cliente. */
const PUBLIC_AUTH_API = /\/api\/auth\/(login|register|recover_account|reset_password)(\/|$|\?)/;

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  private redirecting = false;

  constructor(
    private readonly router: Router,
    private readonly authService: AuthService,
  ) {}

  intercept(req: HttpRequest<unknown>, next: HttpHandler): Observable<HttpEvent<unknown>> {
    return next.handle(req).pipe(
      catchError((err: unknown) => {
        if (!(err instanceof HttpErrorResponse) || err.status !== 401) {
          return throwError(() => err);
        }
        if (PUBLIC_AUTH_API.test(req.url)) {
          return throwError(() => err);
        }
        if (!localStorage.getItem('user_token')) {
          return throwError(() => err);
        }

        this.authService.clearClientAuthState();
        const onLogin = this.router.url.startsWith(AUTH_LOGIN_PATH);
        if (!onLogin && !this.redirecting) {
          this.redirecting = true;
          const returnUrl = this.router.url;
          void this.router
            .navigate([AUTH_LOGIN_PATH], {
              queryParams: returnUrl && returnUrl !== '/' ? { returnUrl } : undefined,
            })
            .finally(() => {
              this.redirecting = false;
            });
        }

        return throwError(() => err);
      }),
    );
  }
}
