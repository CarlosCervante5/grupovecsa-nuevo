import { Component, Input, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';
import { Router } from '@angular/router';

// Services
import { AccountService } from 'src/app/auth/pages/account/services/account.service';

// Interfaces
import { Overview } from '@interfaces/admin.interfaces';

@Component({
    selector: 'app-overview',
    templateUrl: './overview.component.html',
    styleUrls: ['./overview.component.css'],
    standalone: false
})

export class OverviewComponent implements OnInit {

    // Input get information overview
    @Input() overview?: Overview;
    @Input() url_index?: String;

    // References    
    public image_path: string = '';
    public name: string = '';
    public email: string = '';
    public role: string = '';

    constructor(private _accountService: AccountService, private titleService: Title, private router: Router) {}
    
    ngOnInit(): void {   
        this.userSessionStorage()
    }

    private userSessionStorage() {

        const user = JSON.parse(localStorage.getItem('user')!);
        const profile = JSON.parse(localStorage.getItem('profile')!);
            
        this.role = localStorage.getItem('role')!;

        this.name = profile.name;

        this.image_path = profile.picture || `assets/icons/profile.svg`;

        this.email = user.email;

        this.titleService.setTitle(`Vecsa | ${ this.role }`);
    }

    getModuleIcon(title: string): string {
        const t = (title || '').toLowerCase();
        if (t.includes('vehículo') || t.includes('vehiculo')) return 'directions_car';
        if (t.includes('promocion')) return 'campaign';
        if (t.includes('evento')) return 'event';
        if (t.includes('recompensa') || t.includes('reward')) return 'emoji_events';
        if (t.includes('tienda') || t.includes('store')) return 'storefront';
        if (t.includes('benchmark')) return 'monitoring';
        if (t.includes('km') || t.includes('registro')) return 'speed';
        if (t.includes('rider')) return 'sports_motorsports';
        if (t.includes('venta') || t.includes('sale')) return 'point_of_sale';
        if (t.includes('recepción') || t.includes('formulario')) return 'assignment';
        if (t.includes('cita') || t.includes('valuación')) return 'event_available';
        if (t.includes('checklist')) return 'checklist';
        if (t.includes('refaccion') || t.includes('spare')) return 'build';
        if (t.includes('hojalatería') || t.includes('hyp') || t.includes('bodywork')) return 'construction';
        if (t.includes('usuario')) return 'people';
        if (t.includes('pedido') || t.includes('boutique')) return 'receipt_long';
        if (t.includes('home') || t.includes('slide')) return 'slideshow';
        if (t.includes('testimonio')) return 'format_quote';
        return 'folder_open';
    }

    logout(): void {
        localStorage.clear();
        this.router.navigateByUrl('/auth/login');
    }
}