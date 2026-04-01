import { Component, OnDestroy } from '@angular/core';
import { Router, NavigationEnd } from '@angular/router';
import { filter } from 'rxjs/operators';
import { Subscription } from 'rxjs';


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
    ) { 
        this._router.routeReuseStrategy.shouldReuseRoute = () => false;

        this.routerSub = this._router.events
            .pipe(filter(event => event instanceof NavigationEnd))
            .subscribe((event) => {
                const navEnd = event as NavigationEnd;
                const url = navEnd.urlAfterRedirects || navEnd.url;
                this.isHomeRoute = url === '/' || navEnd.url === '/';
                this.hideChrome = url.startsWith('/admin/');
            });
    }

    ngOnDestroy(): void {
        this.routerSub.unsubscribe();
    }
}