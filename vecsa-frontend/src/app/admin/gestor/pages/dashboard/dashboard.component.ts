import { Component } from '@angular/core';
import { Overview } from '@interfaces/admin.interfaces';
//import { Overview } from 'src/app/admin/interfaces/overview.interface';

@Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.css'],
    standalone: false
})
export class DashboardComponent {

  // References Overview
  private user = JSON.parse(localStorage.getItem('user')!);
  public itemOverview: Overview = {
    user: {
      name: this.user.name,
      surname: this.user.surname,
      role: 'Gestor de marketing',
      email: this.user.email,
      picturepath: ''
    },
    pages: [
      {
        title: 'Promociones',
        icon: 'fi fi-rr-car',
        permalink: '/admin/gestor/promotions'
      },
      {
        title: 'Eventos',
        icon: 'fi fi-rr-id-badge',
        permalink: '/admin/gestor/scheduled-events'
      },
      {
        title: 'Recompensas',
        icon: 'fi fi-rr-id-badge',
        permalink: '/admin/gestor/rewards'
      },
      // {
      //   title: 'Accesorios',
      //   icon: 'fi fi-rr-id-badge',
      //   permalink: '/admin/gestor/quizzes'
      // }
    ]
  }

}
