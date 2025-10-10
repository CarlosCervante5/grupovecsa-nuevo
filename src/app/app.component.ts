import { Component, OnInit } from '@angular/core';
import { Router, NavigationEnd, UrlSegment } from '@angular/router';
import { filter } from 'rxjs/operators';


@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.css'],
    standalone: false
})

export class AppComponent{
    public auth_user: boolean = false;
    public url_dashboard: string = '/auth/mi-cuenta';
    public spinner: boolean = false;

    constructor(
        private _router: Router,
    ) { 
        this._router.routeReuseStrategy.shouldReuseRoute = () => false; 
    }
    
}