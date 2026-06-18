import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';


// Services
import { AccountService } from '../services/account.service';
import { AuthService } from '../../../services/auth.service';

@Injectable({
  providedIn: 'root'
})

export class CustomerGuard  {

  constructor(
    private _router: Router, 
    private _accountService: AccountService,
    private _authService: AuthService,
  ) {}

  canActivate(): Observable<boolean> {
    this._authService.syncSessionKeysFromUserData();
    return this._accountService.validateRole('client').pipe(
      map(() => true),
      catchError(() => {
        this._router.navigateByUrl('/auth/iniciar-sesion');
        return of(false);
      })
    );
  }

  canLoad(): Observable<boolean> {
    // Skip API call for canLoad — canActivate already validates
    this._authService.syncSessionKeysFromUserData();
    const token = localStorage.getItem('user_token');
    const role = localStorage.getItem('role');
    if (token && role === 'client') {
      return of(true);
    }
    this._router.navigateByUrl('/auth/iniciar-sesion');
    return of(false);
  }
}
