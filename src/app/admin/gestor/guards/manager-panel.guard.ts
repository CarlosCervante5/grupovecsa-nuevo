import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { AccountService } from '../../../auth/pages/account/services/account.service';

/**
 * Panel marketing compartido (mismo módulo que gestor) con URL /admin/manager.
 */
@Injectable({ providedIn: 'root' })
export class ManagerPanelGuard {
  constructor(
    private _router: Router,
    private _accountService: AccountService,
  ) {}

  canActivate(): Observable<boolean> {
    const subject = new Subject<boolean>();
    this._accountService.validateRole('manager').subscribe({
      next: () => subject.next(true),
      error: () => {
        this._router.navigateByUrl('/auth/iniciar-sesion');
        subject.next(false);
      },
    });
    return subject.asObservable();
  }

  canLoad(): Observable<boolean> {
    return this.canActivate();
  }
}
