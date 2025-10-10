import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, Route, Router, RouterStateSnapshot, UrlSegment, UrlTree } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';

@Injectable({
  providedIn: 'root'
})
export class MarketingGuard  {

  constructor(
    private _router: Router, 
    private _accountService: AccountService
  ) {    
  }

  canActivate(): Observable<boolean> | Promise<boolean> | boolean {
    
    var subject = new Subject<boolean>();
    
    this._accountService.validateRole('marketing')
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
    
    this._accountService.validateRole('marketing')
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
