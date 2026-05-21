import {
  Component,
  EventEmitter,
  Input,
  NgZone,
  OnDestroy,
  OnInit,
  Output,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

import { BoutiqueCheckoutService } from '../../services/boutique-checkout.service';
import { BoutiqueOpenPayPublicConfig } from '../../interfaces/boutique.interfaces';

/** Datos de facturación / domicilio para token y cargo (OpenPay). */
export interface OpenPayBillingContext {
  holder_name: string;
  line1: string;
  line2: string;
  city: string;
  state: string;
  postal_code: string;
  country_code: string;
}

@Component({
  selector: 'app-openpay-payment',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
  ],
  templateUrl: './openpay-payment.component.html',
  styleUrls: ['./openpay-payment.component.css'],
})
export class OpenpayPaymentComponent implements OnInit, OnDestroy {
  @Input({ required: true }) order_uuid!: string;
  @Input({ required: true }) openPayConfig!: BoutiqueOpenPayPublicConfig;
  @Input({ required: true }) billing!: OpenPayBillingContext;
  @Input({ required: true }) amount!: number;
  /** Cuando se muestra dentro de un modal, se oculta el título duplicado y se suavizan bordes. */
  @Input() inModal = false;

  @Output() paymentSuccess = new EventEmitter<string>();

  isLoading = true;
  isProcessing = false;
  errorMessage: string | null = null;
  formReady = false;

  /** Campos tarjeta (no se envían al backend; solo a OpenPay.js). */
  cardNumber = '';
  expirationMonth = '';
  expirationYear = '';
  cvv2 = '';

  private deviceSessionId: string | null = null;

  private sdkConfigured = false;

  constructor(
    private checkoutService: BoutiqueCheckoutService,
    private ngZone: NgZone,
  ) {}

  ngOnInit(): void {
    this.loadOpenPayScripts();
  }

  ngOnDestroy(): void {}

  private loadOpenPayScripts(): void {
    const w = window as any;
    if (w.OpenPay && w.OpenPay.deviceData) {
      this.initOpenPaySdk();
      return;
    }

    const base = 'https://resources.openpay.mx/lib';
    const ver = '1.2.38';
    const urls = [
      `${base}/openpay-js/${ver}/openpay.v1.min.js`,
      `${base}/openpay-data-js/${ver}/openpay-data.v1.min.js`,
    ];

    const appendScript = (src: string): Promise<void> =>
      new Promise((resolve, reject) => {
        const existing = document.querySelector(`script[src="${src}"]`);
        if (existing) {
          resolve();
          return;
        }
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('script'));
        document.head.appendChild(script);
      });

    let chain = Promise.resolve();
    for (const src of urls) {
      chain = chain.then(() => appendScript(src));
    }

    chain
      .then(() => {
        this.ngZone.run(() => this.initOpenPaySdk());
      })
      .catch(() => {
        this.ngZone.run(() => {
          this.isLoading = false;
          this.errorMessage = 'Error al cargar el script de pagos OpenPay.';
        });
      });
  }

  private initOpenPaySdk(): void {
    if (this.sdkConfigured) {
      return;
    }
    const OpenPay = (window as any).OpenPay;
    if (!OpenPay) {
      this.isLoading = false;
      this.errorMessage = 'OpenPay.js no está disponible.';
      return;
    }

    try {
      OpenPay.setId(this.openPayConfig.merchant_id);
      OpenPay.setApiKey(this.openPayConfig.public_key);
      OpenPay.setSandboxMode(!!this.openPayConfig.sandbox);
    } catch {
      this.isLoading = false;
      this.errorMessage = 'No se pudo configurar OpenPay.';
      return;
    }

    setTimeout(() => {
      try {
        this.deviceSessionId = OpenPay.deviceData.setup('openpayChargeForm', 'device_session_id');
      } catch {
        this.deviceSessionId = null;
      }
      this.sdkConfigured = true;
      this.ngZone.run(() => {
        this.isLoading = false;
        this.formReady = true;
        if (!this.deviceSessionId) {
          this.errorMessage =
            'No se pudo generar la sesión del dispositivo (antifraude). Recarga la página e intenta de nuevo.';
        }
      });
    }, 0);
  }

  private normalizeYear(y: string): string {
    const t = String(y || '').trim();
    if (t.length >= 4) {
      return t.slice(-2);
    }
    return t.padStart(2, '0');
  }

  pay(): void {
    const OpenPay = (window as any).OpenPay;
    if (!OpenPay || this.isProcessing || !this.formReady) {
      return;
    }
    if (!this.deviceSessionId || this.deviceSessionId.length !== 32) {
      this.errorMessage = 'Sesión de dispositivo inválida. Recarga la página.';
      return;
    }

    this.errorMessage = null;
    this.isProcessing = true;

    const tokenParams = {
      card_number: String(this.cardNumber).replace(/\s/g, ''),
      holder_name: String(this.billing.holder_name || 'Cliente').trim(),
      expiration_month: String(this.expirationMonth).trim().padStart(2, '0'),
      expiration_year: this.normalizeYear(String(this.expirationYear)),
      cvv2: String(this.cvv2).trim(),
      address: {
        city: String(this.billing.city || '').trim() || 'Ciudad',
        country_code: String(this.billing.country_code || 'MX').trim().slice(0, 3) || 'MX',
        line1: String(this.billing.line1 || 'Domicilio').trim().slice(0, 200),
        line2: String(this.billing.line2 || '').trim().slice(0, 200),
        line3: '',
        postal_code: String(this.billing.postal_code || '00000').replace(/\D/g, '').slice(0, 5) || '00000',
        state: String(this.billing.state || '').trim().slice(0, 100) || 'Estado',
      },
    };

    OpenPay.token.create(
      tokenParams,
      (resp: any) => {
        const tokenId = resp?.data?.id;
        if (!tokenId) {
          this.ngZone.run(() => {
            this.isProcessing = false;
            this.errorMessage = 'No se obtuvo el token de tarjeta.';
          });
          return;
        }
        this.confirmOnServer(String(tokenId));
      },
      (err: any) => {
        const desc = err?.data?.description || err?.message || 'Error al tokenizar la tarjeta.';
        this.ngZone.run(() => {
          this.isProcessing = false;
          this.errorMessage = typeof desc === 'string' ? desc : 'Error al tokenizar la tarjeta.';
        });
      },
    );
  }

  private extractPaymentErrorMessage(err: unknown): string {
    const body = (err as { error?: Record<string, unknown> })?.error;
    const data = body?.['data'] as Record<string, unknown> | undefined;
    const openpayBody = data?.['openpay_body'] as Record<string, unknown> | undefined;
    const candidates = [
      data?.['openpay_error'],
      openpayBody?.['description'],
      openpayBody?.['error_description'],
      body?.['message'],
    ];
    for (const raw of candidates) {
      if (typeof raw === 'string' && raw.trim()) {
        return raw.replace(/^Hubo un problema con su solicitud:\s*/i, '').trim();
      }
    }
    return 'No se pudo completar el pago.';
  }

  private confirmOnServer(sourceId: string): void {
    this.checkoutService
      .confirmOpenPayCharge({
        order_uuid: this.order_uuid,
        source_id: sourceId,
        device_session_id: this.deviceSessionId!,
      })
      .subscribe({
        next: (res) => {
          const data = res?.data as { requires_3ds?: boolean; redirect_url?: string } | undefined;
          if (data?.requires_3ds && data.redirect_url) {
            this.ngZone.run(() => {
              this.isProcessing = false;
              window.location.href = data.redirect_url!;
            });
            return;
          }
          this.ngZone.run(() => {
            this.isProcessing = false;
            this.paymentSuccess.emit(this.order_uuid);
          });
        },
        error: (err) => {
          const msg = this.extractPaymentErrorMessage(err);
          this.ngZone.run(() => {
            this.isProcessing = false;
            this.errorMessage = msg;
          });
        },
      });
  }
}
