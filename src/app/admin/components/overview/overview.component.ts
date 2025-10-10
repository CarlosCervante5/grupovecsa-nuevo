import { Component, Input, OnInit } from '@angular/core';
import { Title } from '@angular/platform-browser';

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

    constructor(private _accountService: AccountService, private titleService: Title) {}
    
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
}