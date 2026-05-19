import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { BoutiqueTransferBankDetails } from '../../interfaces/boutique.interfaces';

export interface GuestOrderThanksState {
  orderNumber?: string;
  guestEmail?: string;
  paymentMethod?: string;
  transferBank?: BoutiqueTransferBankDetails | null;
}

@Component({
  selector: 'app-guest-order-thanks',
  standalone: true,
  imports: [CommonModule, RouterModule, MatButtonModule, MatIconModule],
  templateUrl: './guest-order-thanks.component.html',
  styleUrls: ['./guest-order-thanks.component.css'],
})
export class GuestOrderThanksComponent {
  orderReferenceUuid = '';
  orderNumber: string | null = null;
  guestEmail: string | null = null;
  paymentMethod: string | null = null;
  transferBank: BoutiqueTransferBankDetails | null = null;

  constructor(
    private router: Router,
    route: ActivatedRoute,
  ) {
    this.orderReferenceUuid = route.snapshot.paramMap.get('uuid') ?? '';
    const nav = this.router.getCurrentNavigation();
    const st = (nav?.extras?.state ?? null) as GuestOrderThanksState | null;
    if (st?.orderNumber) {
      this.orderNumber = st.orderNumber;
    }
    if (st?.guestEmail) {
      this.guestEmail = st.guestEmail;
    }
    if (st?.paymentMethod) {
      this.paymentMethod = st.paymentMethod;
    }
    if (st?.transferBank) {
      this.transferBank = st.transferBank;
    }
  }

  get showTransferBank(): boolean {
    return this.paymentMethod === 'transferencia';
  }
}
