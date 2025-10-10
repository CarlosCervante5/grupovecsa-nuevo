import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { Overview } from '@interfaces/admin.interfaces';

@Component({
    selector: 'app-dashboard',
    // standalone: true,
    // imports: [
    //     CommonModule,
    // ],
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.css'],
    standalone: false
})
export class DashboardComponent {

    private user = JSON.parse(localStorage.getItem('user')!);

    public itemOverview: Overview = {
        user: {
            name: this.user.name,
            surname: this.user.surname,
            role: 'Valuator',
            email: this.user.email,
            picturepath: ''
        },
        pages: [
            {
                title: 'Citas valuación',
                icon: 'fi fi-rr-car',
                permalink: '/admin/valuator/appointment'
            },
        ]
    };
}
