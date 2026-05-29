import { Component, OnDestroy } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { Subscription, fromEvent, merge } from 'rxjs';
import { debounceTime, filter } from 'rxjs/operators';
import { AuthService } from './auth/services/auth.service';
import {
  clearPostLoginLoading,
  isPostLoginLoading,
  removePostLoginOverlayElement,
} from './auth/constants/post-login-loading';


@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css'],
    standalone: false
})

export class AppComponent implements OnDestroy {
    public auth_user: boolean = false;
    public url_dashboard: string = '/auth/mi-cuenta';
    public spinner: boolean = false;
    public isHomeRoute: boolean = false;
    public hideChrome: boolean = false;
    /** Cubo de carga tras login (pantalla en blanco al cargar admin / mi-cuenta). */
    public postLoginLoading = isPostLoginLoading();

    private routerSub: Subscription;
    private sessionRefreshSub?: Subscription;

    constructor(
        private _router: Router,
        private _authService: AuthService,
    ) { 
        this._router.routeReuseStrategy.shouldReuseRoute = () => false;

        const initialUrl = this._router.url || '';
        this.hideChrome = initialUrl.startsWith('/admin/');
        this.isHomeRoute = initialUrl === '/' || initialUrl === '';

        if (localStorage.getItem('user_token')) {
            this._authService.refreshPermissionsInStorage().subscribe({ error: () => {} });
        }

        this.routerSub = this._router.events
            .pipe(filter(event => event instanceof NavigationEnd))
            .subscribe((event) => {
                const navEnd = event as NavigationEnd;
                const url = navEnd.urlAfterRedirects || navEnd.url;
                this.isHomeRoute = url === '/' || navEnd.url === '/';
                this.hideChrome = url.startsWith('/admin/');
                if (url.startsWith('/auth/iniciar-sesion') || url.startsWith('/auth/login')) {
                    clearPostLoginLoading();
                    removePostLoginOverlayElement();
                    this.postLoginLoading = false;
                } else if (this.postLoginLoading && this.isPostLoginDestination(url)) {
                    clearPostLoginLoading();
                    removePostLoginOverlayElement();
                    setTimeout(() => {
                        this.postLoginLoading = false;
                    }, 120);
                }
                if (localStorage.getItem('user_token') && url.startsWith('/admin/')) {
                    this._authService.refreshPermissionsInStorage().subscribe({ error: () => {} });
                }
            });

        // Tras cambios de rol/permisos en otro tab o por un admin, al volver al foco se re-sincroniza /me.
        this.sessionRefreshSub = merge(
            fromEvent(window, 'focus'),
            fromEvent(document, 'visibilitychange').pipe(
                filter(() => document.visibilityState === 'visible'),
            ),
        )
            .pipe(debounceTime(500))
            .subscribe(() => {
                if (localStorage.getItem('user_token')) {
                    this._authService.refreshPermissionsInStorage().subscribe({ error: () => {} });
                }
            });
    }

    ngOnDestroy(): void {
        this.routerSub.unsubscribe();
        this.sessionRefreshSub?.unsubscribe();
    }

    private isPostLoginDestination(url: string): boolean {
        return url.startsWith('/admin/') || url.startsWith('/auth/mi-cuenta');
    }
}