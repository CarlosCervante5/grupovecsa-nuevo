import {
  Component,
  Input,
  Output,
  EventEmitter,
  OnInit,
  AfterViewInit,
  OnDestroy,
  NgZone,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';

import { environment } from '@environments/environment';
import { BoutiqueCheckoutService } from '../../services/boutique-checkout.service';

declare var Stripe: any;

@Component({
  selector: 'app-stripe-payment',
  standalone: true,
  imports: [
    CommonModule,
    MatProgressSpinnerModule,
    MatIconModule,
    MatButtonModule,
  ],
  templateUrl: './stripe-payment.component.html',
  styleUrls: ['./stripe-payment.component.css'],
})
export class StripePaymentComponent implements OnInit, AfterViewInit, OnDestroy {
  @Input() order_uuid!: string;
  @Output() paymentSuccess = new EventEmitter<string>();

  isLoadingStripe = true;
  isProcessing = false;
  errorMessage: string | null = null;
  stripeReady = false;

  private stripe: any;
  private cardElement: any;
  private clientSecret: string | null = null;

  constructor(
    private checkoutService: BoutiqueCheckoutService,
    private ngZone: NgZone
  ) {}

  ngOnInit(): void {
    this.loadStripeScript();
  }

  ngAfterViewInit(): void {
    // Card element will be mounted after both Stripe.js loads and view is ready
  }

  ngOnDestroy(): void {
    if (this.cardElement) {
      this.cardElement.destroy();
    }
  }

  private loadStripeScript(): void {
    if (typeof Stripe !== 'undefined') {
      this.initStripe();
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://js.stripe.com/v3/';
    script.async = true;
    script.onload = () => {
      this.ngZone.run(() => this.initStripe());
    };
    script.onerror = () => {
      this.ngZone.run(() => {
        this.isLoadingStripe = false;
        this.errorMessage = 'No se pudo cargar el procesador de pagos. Intenta recargar la página.';
      });
    };
    document.head.appendChild(script);
  }

  private initStripe(): void {
    this.stripe = Stripe(environment.stripePublishableKey);
    this.fetchPaymentIntent();
  }

  private fetchPaymentIntent(): void {
    this.checkoutService.createPaymentIntent(this.order_uuid).subscribe({
      next: (res) => {
        this.clientSecret = res.data.client_secret;
        this.mountCardElement();
      },
      error: (err) => {
        this.isLoadingStripe = false;
        this.errorMessage =
          err?.error?.message || 'Error al inicializar el pago. Intenta de nuevo.';
      },
    });
  }

  private mountCardElement(): void {
    const elements = this.stripe.elements();

    const style = {
      base: {
        color: '#f9fafb',
        fontFamily: '"Roboto", "Helvetica Neue", sans-serif',
        fontSize: '16px',
        '::placeholder': {
          color: '#6b7280',
        },
      },
      invalid: {
        color: '#f87171',
        iconColor: '#f87171',
      },
    };

    this.cardElement = elements.create('card', { style });

    const container = document.getElementById('stripe-card-element');
    if (container) {
      this.cardElement.mount('#stripe-card-element');
    }

    this.cardElement.on('ready', () => {
      this.ngZone.run(() => {
        this.isLoadingStripe = false;
        this.stripeReady = true;
      });
    });

    this.cardElement.on('change', (event: any) => {
      this.ngZone.run(() => {
        this.errorMessage = event.error ? event.error.message : null;
      });
    });
  }

  async confirmPayment(): Promise<void> {
    if (!this.stripe || !this.cardElement || !this.clientSecret || this.isProcessing) {
      return;
    }

    this.isProcessing = true;
    this.errorMessage = null;

    const { error, paymentIntent } = await this.stripe.confirmCardPayment(
      this.clientSecret,
      {
        payment_method: {
          card: this.cardElement,
        },
      }
    );

    this.ngZone.run(() => {
      this.isProcessing = false;

      if (error) {
        this.errorMessage = error.message || 'Error al procesar el pago.';
      } else if (paymentIntent && paymentIntent.status === 'succeeded') {
        this.paymentSuccess.emit(this.order_uuid);
      }
    });
  }
}
