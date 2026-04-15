import { Component, OnDestroy } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { Subscription } from 'rxjs';
import { AuthService } from './auth/services/auth.service';


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

    private routerSub: Subscription;

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
                if (localStorage.getItem('user_token') && url.startsWith('/admin/')) {
                    this._authService.refreshPermissionsInStorage().subscribe({ error: () => {} });
                }
            });
    }

    ngOnDestroy(): void {
        this.routerSub.unsubscribe();
    }
}