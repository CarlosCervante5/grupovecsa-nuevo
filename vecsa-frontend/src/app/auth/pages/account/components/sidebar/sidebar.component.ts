import { Component, Input } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
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
    this._authService.signOut(this._router);
  }
}
