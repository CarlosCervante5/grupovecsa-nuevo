import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
import Swal from 'sweetalert2';

@Component({
    selector: 'app-sidebar',
    templateUrl: './sidebar.component.html',
    styleUrls: ['./sidebar.component.css'],
    standalone: false
})
export class SidebarComponent {
  @Input() promociones!: string; 
  constructor(
    private _authService: AuthService,
    private _router: Router,
  ){}
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
        error: () => {}
        
    });
    localStorage.clear();
    this._router.navigate(['/auth/iniciar-sesion']);
}
}
