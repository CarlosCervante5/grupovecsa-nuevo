import { ChangeDetectorRef, Component, OnInit, OnDestroy, ViewChild } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { ReactiveFormsModule, FormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { Subscription } from 'rxjs';
import { debounceTime, distinctUntilChanged, map } from 'rxjs/operators';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';

import { environment } from '@environments/environment';
import { BoutiqueCartService } from '../../services/boutique-cart.service';
import { BoutiqueCheckoutService } from '../../services/boutique-checkout.service';
import {
  BoutiqueCart,
  BoutiqueOpenPayPublicConfig,
  BoutiquePaymentMethodsPublicPayload,
  BoutiqueTransferBankDetails,
  ShippingQuote,
} from '../../interfaces/boutique.interfaces';
import {
  OpenpayPaymentComponent,
  OpenPayBillingContext,
} from '../../components/openpay-payment/openpay-payment.component';
import {
  boutiqueSalesWhatsAppUrl,
  formatMxn,
  resolveBoutiqueSalesWhatsAppPhone,
  type BoutiqueDealershipContact,
} from '../../utils/boutique-sales-whatsapp.util';

interface Dealership extends BoutiqueDealershipContact {
  uuid?: string;
  description?: string | null;
}

@Component({
  selector: 'app-checkout',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    ReactiveFormsModule,
    FormsModule,
    CurrencyPipe,
    MatButtonModule,
    MatIconModule,
    MatSnackBarModule,
    MatRadioModule,
    MatSelectModule,
    OpenpayPaymentComponent,
  ],
  templateUrl: './checkout.component.html',
  styleUrls: ['./checkout.component.css'],
})
export class CheckoutComponent implements OnInit, OnDestroy {
  @ViewChild(OpenpayPaymentComponent) openPayModal?: OpenpayPaymentComponent;

  cart: BoutiqueCart | null = null;
  isLoading = true;

  // Guest mode
  get isLoggedIn(): boolean { return !!localStorage.getItem('user_token'); }
  guestConfirmed = false;
  guestForm!: FormGroup;

  // Delivery
  deliveryMethod: 'envio_domicilio' | 'recoleccion_sucursal' = 'envio_domicilio';
  shippingForm!: FormGroup;

  // Shipping quotes
  shippingQuotes: ShippingQuote[] = [];
  selectedQuote: ShippingQuote | null = null;
  loadingQuotes = false;
  quotesLoaded = false;

  // Dealerships
  dealerships: Dealership[] = [];
  selectedDealership: Dealership | null = null;
  loadingDealerships = false;

  // Payment
  paymentMethod: 'transferencia' | 'sucursal' | 'openpay' = 'openpay';
  transferenciaAvailable = true;
  sucursalAvailable = true;
  /** OpenPay visible solo si el backend expone merchant + llave pública para el modo actual. */
  openPayAvailable = false;
  openPayPublicConfig: BoutiqueOpenPayPublicConfig | null = null;

  // Order creation
  creatingOrder = false;

  createdOrderUuid: string | null = null;

  /** Payload de checkout pendiente hasta confirmar pago OpenPay. */
  private pendingOpenPayCheckout: Record<string, unknown> | null = null;

  // OpenPay (tarjeta)
  showOpenPayPayment = false;
  openPayBilling: OpenPayBillingContext | null = null;
  openPayOrderTotal = 0;

  /** Si el pedido con tarjeta en línea (OpenPay) fue como invitado, al terminar pago ir a /boutique/gracias. */
  private guestThanksAfterOnlinePayment: {
    orderNumber: string;
    guestEmail: string;
    paymentMethod: string;
    transferBank: BoutiqueTransferBankDetails | null;
  } | null = null;

  private subs: Subscription[] = [];

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private cartService: BoutiqueCartService,
    private checkoutService: BoutiqueCheckoutService,
    private snackBar: MatSnackBar,
    private router: Router,
    private cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    this.guestForm = this.fb.group({
      guest_name: ['', Validators.required],
      guest_email: ['', [Validators.required, Validators.email]],
    });
    // If already logged in, guest step is skipped
    if (this.isLoggedIn) this.guestConfirmed = true;

    this.shippingForm = this.fb.group({
      shipping_name: ['', Validators.required],
      shipping_address: ['', Validators.required],
      shipping_city: ['', Validators.required],
      shipping_state: ['', Validators.required],
      shipping_zip: ['', [Validators.required, Validators.pattern(/^\d{5}$/)]],
      shipping_phone: ['', [Validators.required, Validators.pattern(/^\d{10}$/)]],
    });
    const shippingAutoSub = this.shippingForm.valueChanges
      .pipe(
        debounceTime(400),
        map(() => {
          const v = this.shippingForm.getRawValue() as Record<string, string>;
          const sig = `${v.shipping_address ?? ''}|${v.shipping_city ?? ''}|${v.shipping_state ?? ''}|${v.shipping_zip ?? ''}`;
          return { sig, valid: this.shippingForm.valid };
        }),
        distinctUntilChanged((a, b) => a.sig === b.sig && a.valid === b.valid),
      )
      .subscribe(() => this.maybeAutoRefreshShippingQuote());
    this.subs.push(shippingAutoSub);

    this.loadCart();
    this.loadCheckoutPaymentMethods();
  }

  private loadCheckoutPaymentMethods(): void {
    const sub = this.checkoutService.getPaymentMethodsPublic().subscribe({
      next: (res) => {
        const d = res.data as BoutiquePaymentMethodsPublicPayload | null;
        if (!d?.methods) {
          this.applyPaymentMethodsFallback();
          return;
        }
        this.transferenciaAvailable = !!d.methods.transferencia;
        this.sucursalAvailable = !!d.methods.sucursal;
        const op = d.openpay;
        if (op) {
          this.openPayPublicConfig = {
            merchant_id: String(op.merchant_id ?? ''),
            public_key: String(op.public_key ?? ''),
            sandbox: !!op.sandbox,
            available: typeof op.available === 'boolean' ? op.available : !!(String(op.merchant_id || '').trim() && String(op.public_key || '').trim()),
          };
          this.openPayAvailable = this.openPayPublicConfig.available;
        } else {
          this.openPayPublicConfig = null;
          this.openPayAvailable = false;
        }
        this.ensureValidSelectedPaymentMethod();
      },
      error: () => this.applyPaymentMethodsFallback(),
    });
    this.subs.push(sub);
  }

  /** Si falla el endpoint, no bloquear checkout: mostrar todos salvo OpenPay sin credenciales. */
  private applyPaymentMethodsFallback(): void {
    this.transferenciaAvailable = true;
    this.sucursalAvailable = true;
    this.openPayAvailable = false;
    this.openPayPublicConfig = null;
    this.ensureValidSelectedPaymentMethod();
  }

  private ensureValidSelectedPaymentMethod(): void {
    const order: Array<{ id: 'openpay' | 'transferencia' | 'sucursal'; on: boolean }> = [
      { id: 'openpay', on: this.openPayAvailable },
      { id: 'transferencia', on: this.transferenciaAvailable },
      { id: 'sucursal', on: this.sucursalAvailable },
    ];
    if (order.some(o => o.id === this.paymentMethod && o.on)) {
      return;
    }
    const first = order.find(o => o.on);
    this.paymentMethod = first ? first.id : 'openpay';
  }

  private selectedPaymentMethodAllowed(): boolean {
    switch (this.paymentMethod) {
      case 'openpay':
        return this.openPayAvailable;
      case 'transferencia':
        return this.transferenciaAvailable;
      case 'sucursal':
        return this.sucursalAvailable;
      default:
        return false;
    }
  }

  get hasAnyPaymentMethod(): boolean {
    return this.openPayAvailable || this.transferenciaAvailable || this.sucursalAvailable;
  }

  /** Pasarela OpenPay desactivada o sin credenciales. */
  get showSalesWhatsAppCta(): boolean {
    return !this.openPayAvailable;
  }

  /** Datos de envío/cliente listos para armar el mensaje a ventas. */
  get canContactSalesWhatsApp(): boolean {
    if (!this.cart?.items?.length) {
      return false;
    }
    if (!this.guestConfirmed) {
      return false;
    }
    if (this.deliveryMethod === 'envio_domicilio') {
      if (this.shippingForm.invalid) {
        return false;
      }
      if (!this.selectedQuote) {
        return false;
      }
    } else if (!this.selectedDealership) {
      return false;
    }
    return true;
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  loadCart(): void {
    this.isLoading = true;
    const sub = this.cartService.get().subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        // Backend returns { cart, items, total } — map to BoutiqueCart
        if (wrapper && wrapper.items !== undefined) {
          this.cart = {
            uuid: wrapper.cart?.uuid || '',
            items: wrapper.items || [],
            total: wrapper.total || 0,
          } as BoutiqueCart;
        } else {
          this.cart = wrapper;
        }
        this.isLoading = false;
        if (!this.cart || !this.cart.items || this.cart.items.length === 0) {
          this.router.navigate(['/boutique/cart']);
        }
      },
      error: () => {
        this.isLoading = false;
        this.snackBar.open('Error al cargar el carrito', 'Cerrar', { duration: 3000 });
        this.router.navigate(['/boutique/cart']);
      },
    });
    this.subs.push(sub);
  }

  confirmGuestIdentity(): void {
    if (this.guestForm.invalid) {
      this.guestForm.markAllAsTouched();
      return;
    }
    this.guestConfirmed = true;
    this.maybeAutoRefreshShippingQuote();
  }

  onDeliveryMethodChange(): void {
    this.shippingQuotes = [];
    this.selectedQuote = null;
    this.quotesLoaded = false;
    this.selectedDealership = null;

    if (this.deliveryMethod === 'recoleccion_sucursal' && this.dealerships.length === 0) {
      this.loadDealerships();
    }
    this.maybeAutoRefreshShippingQuote();
  }

  /** Cotización al vuelo cuando la dirección de envío es válida (sin botón manual). */
  private maybeAutoRefreshShippingQuote(): void {
    if (!this.guestConfirmed || this.deliveryMethod !== 'envio_domicilio') {
      return;
    }
    if (this.shippingForm.invalid) {
      this.shippingQuotes = [];
      this.selectedQuote = null;
      this.quotesLoaded = false;
      return;
    }
    this.refreshShippingQuoteFromForm();
  }

  private refreshShippingQuoteFromForm(): void {
    this.loadingQuotes = true;
    this.shippingQuotes = [];
    this.selectedQuote = null;
    this.quotesLoaded = false;

    const formVal = this.shippingForm.value;
    const sub = this.checkoutService
      .shippingQuote({
        shipping_address: formVal.shipping_address,
        shipping_city: formVal.shipping_city,
        shipping_state: formVal.shipping_state,
        shipping_zip: formVal.shipping_zip,
      })
      .subscribe({
        next: (res: any) => {
          const data = res.data;
          this.shippingQuotes = Array.isArray(data) ? data : (data?.shipping_options || []);
          this.quotesLoaded = true;
          this.loadingQuotes = false;
          if (this.shippingQuotes.length > 0) {
            this.selectedQuote = this.shippingQuotes[0];
          }
          this.cdr.detectChanges();
        },
        error: () => {
          this.loadingQuotes = false;
          this.snackBar.open('Error al obtener cotizaciones de envío', 'Cerrar', { duration: 3000 });
          this.cdr.detectChanges();
        },
      });
    this.subs.push(sub);
  }

  loadDealerships(): void {
    this.loadingDealerships = true;
    const sub = this.http.post<{ status: number; message: string; data: Dealership[] }>(
      `${environment.baseUrl}/api/dealerships/search`,
      {}
    ).subscribe({
      next: (res) => {
        this.dealerships = res.data || [];
        this.loadingDealerships = false;
      },
      error: () => {
        this.loadingDealerships = false;
        this.snackBar.open('Error al cargar sucursales', 'Cerrar', { duration: 3000 });
      },
    });
    this.subs.push(sub);
  }

  /** Por si en el futuro se expone un botón «Recotizar»; hoy la cotización es automática. */
  getShippingQuotes(): void {
    if (this.shippingForm.invalid) {
      this.shippingForm.markAllAsTouched();
      return;
    }
    this.refreshShippingQuoteFromForm();
  }

  selectQuote(quote: ShippingQuote): void {
    this.selectedQuote = quote;
  }

  selectDealership(dealership: Dealership): void {
    this.selectedDealership = dealership;
  }

  get subtotal(): number {
    return this.cart?.total || 0;
  }

  get shippingCost(): number {
    if (this.deliveryMethod === 'recoleccion_sucursal') return 0;
    return this.selectedQuote?.price || 0;
  }

  get total(): number {
    return this.subtotal + this.shippingCost;
  }

  openSalesWhatsApp(): void {
    if (!this.canContactSalesWhatsApp) {
      this.snackBar.open(
        'Completa tus datos y la entrega antes de contactar a ventas.',
        'Cerrar',
        { duration: 5000 },
      );
      return;
    }
    const phone = resolveBoutiqueSalesWhatsAppPhone({
      pickupDealership:
        this.deliveryMethod === 'recoleccion_sucursal' ? this.selectedDealership : null,
      cartItems: this.cart?.items,
    });
    const url = boutiqueSalesWhatsAppUrl(this.buildSalesWhatsAppMessage(), phone);
    window.open(url, '_blank', 'noopener,noreferrer');
  }

  private buildSalesWhatsAppMessage(): string {
    const lines: string[] = [
      'Hola, requiero realizar una compra en la Boutique VECSA.',
      '',
      '*Productos:*',
    ];

    for (const item of this.cart?.items ?? []) {
      const name = item.product?.name ?? 'Producto';
      const qty = item.quantity;
      const lineTotal = formatMxn(Number(item.subtotal ?? 0));
      const branch = item.product?.dealership?.name;
      const branchSuffix = branch ? ` · ${branch}` : '';
      lines.push(`• ${name} (cant. ${qty}) — ${lineTotal}${branchSuffix}`);
    }

    lines.push(
      '',
      `*Subtotal:* ${formatMxn(this.subtotal)}`,
      `*Envío:* ${formatMxn(this.shippingCost)}`,
      `*Total:* ${formatMxn(this.total)}`,
      '',
    );

    if (this.deliveryMethod === 'envio_domicilio') {
      const v = this.shippingForm.getRawValue() as Record<string, string>;
      lines.push('*Entrega:* Envío a domicilio');
      if (this.selectedQuote) {
        lines.push(`*Paquetería:* ${this.selectedQuote.carrier} — ${this.selectedQuote.service}`);
      }
      lines.push(
        `*Nombre:* ${v.shipping_name ?? ''}`,
        `*Dirección:* ${v.shipping_address ?? ''}`,
        `*Ciudad:* ${v.shipping_city ?? ''}, ${v.shipping_state ?? ''} CP ${v.shipping_zip ?? ''}`,
        `*Teléfono:* ${v.shipping_phone ?? ''}`,
      );
    } else {
      lines.push('*Entrega:* Recolección en sucursal');
      if (this.selectedDealership) {
        lines.push(`*Sucursal:* ${this.selectedDealership.name} — ${this.selectedDealership.location}`);
      }
      const v = this.shippingForm.getRawValue() as Record<string, string>;
      if (v.shipping_name || v.shipping_phone) {
        lines.push(`*Contacto:* ${v.shipping_name ?? ''} · ${v.shipping_phone ?? ''}`);
      }
    }

    if (!this.isLoggedIn) {
      lines.push(
        '',
        `*Cliente invitado:* ${this.guestForm.value.guest_name ?? ''}`,
        `*Correo:* ${this.guestForm.value.guest_email ?? ''}`,
      );
    }

    lines.push(
      '',
      '_El pago en línea con tarjeta no está disponible en este momento. Deseo coordinar la compra con ventas._',
    );

    return lines.join('\n');
  }

  get canConfirm(): boolean {
    if (!this.cart || !this.cart.items || this.cart.items.length === 0) return false;
    if (!this.guestConfirmed) return false;

    if (this.deliveryMethod === 'envio_domicilio') {
      if (this.shippingForm.invalid) return false;
      if (!this.selectedQuote) return false;
    } else {
      if (!this.selectedDealership) return false;
    }

    if (!this.paymentMethod) return false;
    if (!this.hasAnyPaymentMethod || !this.selectedPaymentMethodAllowed()) return false;
    return true;
  }

  confirmOrder(): void {
    if (!this.canConfirm || this.creatingOrder) return;
    if (
      this.paymentMethod === 'openpay' &&
      (!this.openPayPublicConfig?.merchant_id || !this.openPayPublicConfig?.public_key)
    ) {
      this.snackBar.open('OpenPay no está disponible. Elige otro método de pago.', 'Cerrar', { duration: 5000 });
      return;
    }
    this.creatingOrder = true;

    const formVal = this.shippingForm.value;
    const baseParams: any = {
      delivery_method: this.deliveryMethod,
      payment_method: this.paymentMethod,
    };

    if (this.deliveryMethod === 'envio_domicilio') {
      baseParams.shipping_name = formVal.shipping_name;
      baseParams.shipping_address = formVal.shipping_address;
      baseParams.shipping_city = formVal.shipping_city;
      baseParams.shipping_state = formVal.shipping_state;
      baseParams.shipping_zip = formVal.shipping_zip;
      baseParams.shipping_phone = formVal.shipping_phone;
      if (this.selectedQuote) {
        baseParams.shipping_option = {
          carrier: this.selectedQuote.carrier,
          service: this.selectedQuote.service,
          price: this.selectedQuote.price,
          estimated_days: this.selectedQuote.estimated_days,
        };
      }
    } else if (this.selectedDealership) {
      baseParams.dealership_uuid = this.selectedDealership.uuid || String(this.selectedDealership.id);
    }

    if (!this.isLoggedIn) {
      const guestItems = this.buildGuestOrderItems();
      if (guestItems.length === 0) {
        this.creatingOrder = false;
        this.snackBar.open(
          'El carrito no tiene productos con UUID válido. Vuelve a agregar productos al carrito o recarga la página.',
          'Cerrar',
          { duration: 6000 }
        );
        return;
      }
    }

    if (this.paymentMethod === 'openpay') {
      this.creatingOrder = false;
      this.pendingOpenPayCheckout = this.buildOpenPayCheckoutPayload(baseParams);
      this.openPayOrderTotal = this.total;
      this.openPayBilling = this.buildOpenPayBillingFromForms();
      const asGuest = !this.isLoggedIn;
      if (asGuest) {
        this.guestThanksAfterOnlinePayment = {
          orderNumber: '',
          guestEmail: String(this.guestForm.value.guest_email ?? ''),
          paymentMethod: this.paymentMethod,
          transferBank: null,
        };
      } else {
        this.guestThanksAfterOnlinePayment = null;
      }
      this.showOpenPayPayment = true;
      return;
    }

    const order$ = this.isLoggedIn
      ? this.checkoutService.createOrder(baseParams)
      : this.checkoutService.createGuestOrder({
          ...baseParams,
          guest_name: this.guestForm.value.guest_name,
          guest_email: this.guestForm.value.guest_email,
          items: this.buildGuestOrderItems(),
        });

    const sub = order$.subscribe({
      next: (res) => {
        this.creatingOrder = false;
        const wrapper = res.data as any;
        const order = wrapper.order || wrapper;
        const transferBank = (wrapper.transfer_bank as BoutiqueTransferBankDetails | undefined) ?? null;
        this.snackBar.open(`Pedido ${order.order_number} creado exitosamente`, 'Cerrar', { duration: 4000 });

        const asGuest = !this.isLoggedIn;
        if (asGuest) {
          this.cartService.clearLocal();
        }

        if (asGuest) {
          this.router.navigate(['/boutique/gracias', order.uuid], {
            state: {
              orderNumber: order.order_number,
              guestEmail: this.guestForm.value.guest_email,
              paymentMethod: this.paymentMethod,
              transferBank,
            },
          });
        } else {
          this.router.navigate(['/boutique/orders', order.uuid]);
        }
      },
      error: (err) => {
        this.creatingOrder = false;
        this.snackBar.open(this.formatCreateOrderError(err), 'Cerrar', { duration: 7000 });
      },
    });
    this.subs.push(sub);
  }

  private buildOpenPayCheckoutPayload(baseParams: Record<string, unknown>): Record<string, unknown> {
    if (!this.isLoggedIn) {
      return {
        ...baseParams,
        guest_name: this.guestForm.value.guest_name,
        guest_email: this.guestForm.value.guest_email,
        items: this.buildGuestOrderItems(),
      };
    }
    return { ...baseParams };
  }

  private buildOpenPayBillingFromForms(): OpenPayBillingContext {
    const formVal = this.shippingForm.value;
    const holder = this.isLoggedIn
      ? String(formVal.shipping_name || 'Cliente')
      : String(this.guestForm.value.guest_name || formVal.shipping_name || 'Cliente');

    return {
      holder_name: holder,
      line1: String(formVal.shipping_address || 'Domicilio').slice(0, 200),
      line2: '',
      city: String(formVal.shipping_city || '').trim() || 'Ciudad',
      state: String(formVal.shipping_state || '').trim() || 'Estado',
      postal_code:
        String(formVal.shipping_zip || '00000')
          .replace(/\D/g, '')
          .slice(0, 5) || '00000',
      country_code: 'MX',
    };
  }

  onOpenPayChargeAuthorized(tokens: { source_id: string; device_session_id: string }): void {
    if (!this.pendingOpenPayCheckout) {
      return;
    }
    this.creatingOrder = true;
    const payload = {
      ...this.pendingOpenPayCheckout,
      source_id: tokens.source_id,
      device_session_id: tokens.device_session_id,
    };

    const sub = this.checkoutService.placeOpenPayOrder(payload).subscribe({
      next: (res) => {
        this.creatingOrder = false;
        const data = res?.data as {
          order?: { uuid: string; order_number: string };
          requires_3ds?: boolean;
          redirect_url?: string;
        };

        if (data?.requires_3ds && data.redirect_url) {
          window.location.href = data.redirect_url;
          return;
        }

        const order = data?.order;
        if (!order?.uuid) {
          this.snackBar.open('No se recibió el pedido tras el pago.', 'Cerrar', { duration: 5000 });
          return;
        }

        if (this.guestThanksAfterOnlinePayment) {
          this.guestThanksAfterOnlinePayment.orderNumber = String(order.order_number ?? '');
        }

        this.pendingOpenPayCheckout = null;
        this.showOpenPayPayment = false;
        this.snackBar.open(`Pedido ${order.order_number} pagado correctamente`, 'Cerrar', { duration: 4000 });

        if (!this.isLoggedIn) {
          this.cartService.clearLocal();
        }

        this.onPaymentSuccess(order.uuid);
      },
      error: (err) => {
        this.creatingOrder = false;
        this.openPayModal?.resetProcessing();
        const msg =
          err?.error?.data?.openpay_error ||
          err?.error?.message ||
          'No se pudo completar el pago.';
        this.snackBar.open(
          typeof msg === 'string' ? msg.replace(/^Hubo un problema con su solicitud:\s*/i, '').trim() : 'No se pudo completar el pago.',
          'Cerrar',
          { duration: 7000 },
        );
      },
    });
    this.subs.push(sub);
  }

  private buildGuestOrderItems(): { product_uuid: string; quantity: number; variant_uuid?: string }[] {
    return (this.cart?.items || [])
      .map((i) => {
        const row = i as {
          product?: { uuid?: string };
          product_uuid?: string;
          quantity: number;
          variant_uuid?: string;
        };
        const productUuid = String(row.product?.uuid || row.product_uuid || '').trim();
        if (!productUuid) {
          return null;
        }
        return {
          product_uuid: productUuid,
          quantity: i.quantity,
          ...(row.variant_uuid ? { variant_uuid: String(row.variant_uuid) } : {}),
        };
      })
      .filter(
        (x): x is { product_uuid: string; quantity: number; variant_uuid?: string } => x !== null
      );
  }

  private formatCreateOrderError(err: any): string {
    const e = err?.error;
    if (!e) {
      return 'Error al crear el pedido';
    }
    if (e.error_code === 'INSUFFICIENT_STOCK' && Array.isArray(e.data?.items) && e.data.items.length) {
      const parts = (e.data.items as { product: string; available: number; requested: number }[]).map(
        (r) => `${r.product}: pides ${r.requested}, disponible ${r.available}`,
      );
      return 'Stock insuficiente — ' + parts.join(' · ');
    }
    if (e.error_code === 'PAYMENT_METHOD_DISABLED') {
      return 'Ese método de pago no está habilitado. Elige otro o revisa Métodos de pago (checkout) en el panel tienda.';
    }
    if (e.error_code === 'PRODUCT_VARIANT_NOT_FOUND') {
      return 'La variante del producto ya no es válida. Actualiza el carrito y vuelve a elegir color/talla.';
    }
    if (e.error_code === 'INSUFFICIENT_STOCK_AT_CHECKOUT' && Array.isArray(e.data?.items) && e.data.items.length) {
      const parts = (e.data.items as { product: string; available: number; requested: number }[]).map(
        (r) => `${r.product}: pides ${r.requested}, disponible ${r.available}`,
      );
      return 'Stock insuficiente al confirmar pago — ' + parts.join(' · ');
    }
    if (e.errors && typeof e.errors === 'object') {
      const vals = Object.values(e.errors) as string[][];
      if (vals[0]?.[0]) {
        return String(vals[0][0]);
      }
    }
    if (e.message) {
      return String(e.message);
    }
    return 'Error al crear el pedido';
  }

  getItemImage(item: any): string {
    if (item.product?.images && item.product.images.length > 0) {
      return item.product.images[0].image_path;
    }
    return 'assets/images/placeholder-product.svg';
  }

  onPaymentSuccess(orderUuid: string): void {
    this.showOpenPayPayment = false;
    this.pendingOpenPayCheckout = null;
    this.createdOrderUuid = null;
    if (this.guestThanksAfterOnlinePayment) {
      const st = this.guestThanksAfterOnlinePayment;
      this.guestThanksAfterOnlinePayment = null;
      this.router.navigate(['/boutique/gracias', orderUuid], {
        state: {
          orderNumber: st.orderNumber,
          guestEmail: st.guestEmail,
          paymentMethod: st.paymentMethod,
          transferBank: st.transferBank,
        },
      });
    } else {
      this.router.navigate(['/boutique/orders', orderUuid]);
    }
  }

  /** Cerrar modal sin pagar: no se crea pedido. */
  closeOpenPayModal(): void {
    this.showOpenPayPayment = false;
    this.pendingOpenPayCheckout = null;
    this.createdOrderUuid = null;
    this.guestThanksAfterOnlinePayment = null;
  }
}
