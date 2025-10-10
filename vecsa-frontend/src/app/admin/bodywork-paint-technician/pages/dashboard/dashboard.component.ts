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
            role: 'Bodywork Paint Technician',
            email: this.user.email,
            picturepath: ''
        },
        pages: [
            {
                title: 'Asignar HyP',
                icon: 'fi fi-rr-car',
                permalink: '/admin/bodywork_paint_technician/bodywork-paint'
            },
        ]
    };
}
