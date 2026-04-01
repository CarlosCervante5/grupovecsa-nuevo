import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, of } from 'rxjs';
import { catchError, map } from 'rxjs/operators';

import { AccountService } from 'src/app/auth/pages/account/services/account.service';

@Injectable({
  providedIn: 'root'
})
export class BoutiqueAuthGuard {

  constructor(
    private _router: Router,
    private _accountService: AccountService
  ) {}

  canActivate(): Observable<boolean> {
    return this._accountService.validateRole('client').pipe(
      map(() => true),
      catchError(() => {
        this._router.navigateByUrl('/auth/iniciar-sesion');
        return of(false);
      })
    );
  }

  canLoad(): Observable<boolean> {
    return this._accountService.validateRole('client').pipe(
      map(() => true),
      catchError(() => {
        this._router.navigateByUrl('/auth/iniciar-sesion');
        return of(false);
      })
    );
  }
}
