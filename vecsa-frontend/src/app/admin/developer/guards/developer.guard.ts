import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, Subject } from 'rxjs';
import { AccountService } from 'src/app/auth/pages/account/services/account.service';

@Injectable({ providedIn: 'root' })
export class DeveloperGuard {

  constructor(private router: Router, private accountService: AccountService) {}

  canActivate(): Observable<boolean> | boolean {
    const subject = new Subject<boolean>();
    this.accountService.validateRole('developer').subscribe({
      next: () => subject.next(true),
      error: () => {
        this.router.navigateByUrl('/auth/login');
        subject.next(false);
      }
    });
    return subject.asObservable();
  }

  canLoad(): Observable<boolean> | boolean {
    return this.canActivate();
  }
}
