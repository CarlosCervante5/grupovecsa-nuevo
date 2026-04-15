import { CommonModule } from '@angular/common';
import { Component, HostListener } from '@angular/core';
import { Router, RouterModule } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';
import Swal from 'sweetalert2';

@Component({
    selector: 'nav-carcare',
    templateUrl: './nav-carcare.component.html',
    styleUrls: ['./nav-carcare.component.css'],
    imports: [CommonModule, RouterModule]
})
export class NavCarcareComponent {
  public url_dashboard: string = '/auth/mi-cuenta';
  public spinner: boolean = false;
  public auth_user: boolean = false;
  public imag_size: string = '60px';
  public anchoW!: number;
  public movil = false;

  constructor(
    private _router: Router,
    private _authService: AuthService,
  ) { 
  }

  ngDoCheck(): void {
    this.checkSessionStorageUser();
    this.url_dashboard = this.get_url_dashboard();
  }

  closeNavbar() {
    const navbar = document.getElementById('navbarSupportedContent') as HTMLElement;
    if (navbar) {
      navbar.classList.remove('show');
    }
  }

  public logout() {
          
    this._authService.logout()
    .subscribe({
        next: () => {
        Swal.fire({
            icon: 'success',
            title: 'Hasta luego!',
            text: 'Haz cerrado sesión correctamente.',
            showConfirmButton: true,
            confirmButtonColor: '#EEB838',
            timer: 3500
        });
        },
        error: () => {
          Swal.fire({
            icon: 'success',
            title: 'Hasta luego!',
            text: 'Haz cerrado sesión correctamente.',
            showConfirmButton: true,
            confirmButtonColor: '#EEB838',
            timer: 3500
          });
        }
    });
    this.spinner = false;
    localStorage.clear();
    this._router.navigate(['/auth/iniciar-sesion']);
}

  public get_url_dashboard() {
        
    let role: any = localStorage.getItem('role');
    
    if(role != null){

        if(role === 'client')
            return `/auth/mi-cuenta`

        return adminDashboardUrl(role);
    }

    return `/admin/not-autorized`;

  }

  public checkSessionStorageUser() {

    this.auth_user = (localStorage.getItem('user_token') && localStorage.getItem('user')) ? true : false;    
  }
  @HostListener('window:resize', ['$event'])
  onResize(event: Event) {
    this.anchoW = window.innerWidth;
    this.imag_size = this.anchoW - 50+ 'px';
    
    if(this.anchoW < 300){
      this.imag_size = '30px';
    }else{
      if(this.anchoW < 540){
        this.imag_size = '50px';
        this.movil = true;
      }else{
          this.imag_size = '60px';
    }
    }
  }
}
