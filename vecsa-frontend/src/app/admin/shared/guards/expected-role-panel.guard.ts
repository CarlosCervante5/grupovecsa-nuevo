import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, CanLoad, Route, Router } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';

/**
 * Activa rutas de panel donde `data.expectedRole` coincide con el rol validado en API.
 */
@Injectable({ providedIn: 'root' })
export class ExpectedRolePanelGuard implements CanActivate, CanLoad {
  constructor(
    private readonly accountService: AccountService,
    private readonly router: Router,
  ) {}

  canActivate(route: ActivatedRouteSnapshot): Observable<boolean> {
    const expected = route.data['expectedRole'] as string | undefined;
    return this.validate(expected);
  }

  canLoad(route: Route): Observable<boolean> {
    const expected = route.data?.['expectedRole'] as string | undefined;
    return this.validate(expected);
  }

  private validate(expected: string | undefined): Observable<boolean> {
    const subject = new Subject<boolean>();
    if (!expected?.trim()) {
      void this.router.navigateByUrl('/404');
      subject.next(false);
      return subject.asObservable();
    }
    this.accountService.validateRole(expected).subscribe({
      next: () => subject.next(true),
      error: () => {
        void this.router.navigateByUrl('/auth/iniciar-sesion');
        subject.next(false);
      },
    });
    return subject.asObservable();
  }
}
