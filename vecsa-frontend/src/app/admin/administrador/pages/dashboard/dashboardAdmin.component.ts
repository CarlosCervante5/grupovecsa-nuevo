import { Component } from '@angular/core';
import { Overview } from '@interfaces/admin.interfaces';

@Component({
    selector: 'app-dashboard',
    templateUrl: './dashboard.component.html',
    styleUrls: ['./dashboard.component.css'],
    standalone: false
})
export class DashboardAdminComponent {

  private user = JSON.parse(localStorage.getItem('user')!);
  public itemOverview: Overview = {
    user: {
      name: this.user.name,
      surname: this.user.surname,
      role: 'Admin',
      email: this.user.email,
      picturepath: ''
    },
    pages: [
      {
        title: 'Usuarios',
        icon: 'fi fi-rr-car',
        permalink: '/admin/administrator/users'
      },
      // {
      //   title: 'Permisos',
      //   icon: 'fi fi-rr-id-badge',
      //   permalink: '/admin/administrator/permissions'
      // },
    ]
  }

}
