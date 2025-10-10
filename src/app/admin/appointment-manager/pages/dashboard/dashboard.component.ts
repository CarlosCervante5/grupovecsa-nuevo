import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';
import { Overview } from '@interfaces/admin.interfaces';

@Component({
    selector: 'app-dashboard',
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
            role: 'Valuation Manager',
            email: this.user.email,
            picturepath: ''
        },
        pages: [
            {
                title: 'Asignar Citas de Valuación',
                icon: 'fi fi-rr-car',
                permalink: '/admin/appointment_manager/assing-valuations'
            },
        ]
    };
}
