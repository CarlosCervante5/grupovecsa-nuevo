import { Injectable } from '@angular/core';
import { Route, UrlSegment, UrlTree, Router } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { AccountService } from '../../../auth/pages/account/services/account.service';

@Injectable({
  providedIn: 'root'
})
export class GestorGuard  {

  constructor(
    private _router: Router, 
    private _accountService: AccountService
  ) { }

  canActivate(): Observable<boolean> | boolean {
    

    var subject = new Subject<boolean>();
    
    this._accountService.validateRole('gestor')
    .subscribe({
      next: () => {
        subject.next(true);
      },
      error: () => {
        this._router.navigateByUrl('/auth/iniciar-sesion');
        subject.next(false);
      }
    });

    return subject.asObservable();
    
  }

  canLoad(): Observable<boolean> | boolean {
    
    var subject = new Subject<boolean>();
    
    this._accountService.validateRole('gestor')
    .subscribe({
      next: () => {
        subject.next(true);
      },
      error: () => {
        this._router.navigateByUrl('/auth/iniciar-sesion');
        subject.next(false);
      }
    });

    return subject.asObservable();
  }
}
