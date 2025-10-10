import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';

@Injectable({
  providedIn: 'root'
})
export class ReceptionistGuard  {

  constructor(
    private _router: Router, 
    private _accountService: AccountService
  ) {    
  }

  canActivate(): Observable<boolean> | Promise<boolean> | boolean {
    
    var subject = new Subject<boolean>();
    
    this._accountService.validateRole('receptionist')
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

  canLoad(): Observable<boolean> | Promise<boolean> | boolean {
    

    var subject = new Subject<boolean>();
    
    this._accountService.validateRole('receptionist')
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
