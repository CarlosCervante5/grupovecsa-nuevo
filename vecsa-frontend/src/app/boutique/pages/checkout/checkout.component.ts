import { ChangeDetectorRef, Component, OnInit, OnDestroy } from '@angular/core';
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
  ShippingQuote,
} from '../../interfaces/boutique.interfaces';
import { StripePaymentComponent } from '../../components/stripe-payment/stripe-payment.component';
import {
  OpenpayPaymentComponent,
  OpenPayBillingContext,
} from '../../components/openpay-payment/openpay-payment.component';

interface Dealership {
  id?: number;
  uuid?: string;
  name: string;
  location: string;
  description: string | null;
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
    StripePaymentComponent,
    OpenpayPaymentComponent,
  ],
  templateUrl: './checkout.component.html',
  styleUrls: ['./checkout.component.css'],
})
export class CheckoutComponent implements OnInit, OnDestroy {
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
  paymentMethod: 'stripe' | 'transferencia' | 'sucursal' | 'openpay' = 'stripe';
  stripeAvailable = true;
  transferenciaAvailable = true;
  sucursalAvailable = true;
  /** OpenPay visible solo si el backend expone merchant + llave pública para el modo actual. */
  openPayAvailable = false;
  openPayPublicConfig: BoutiqueOpenPayPublicConfig | null = null;

  // Order creation
  creatingOrder = false;

  // Stripe payment
  showStripePayment = false;
  createdOrderUuid: string | null = null;

  // OpenPay (tarjeta)
  showOpenPayPayment = false;
  openPayBilling: OpenPayBillingContext | null = null;
  openPayOrderTotal = 0;

  /** Si el pedido con tarjeta en línea (Stripe/OpenPay) fue como invitado, al terminar pago ir a /boutique/gracias. */
  private guestThanksAfterOnlinePayment: { orderNumber: string; guestEmail: string } | null = null;

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
        this.stripeAvailable = !!d.methods.stripe;
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
    this.stripeAvailable = true;
    this.transferenciaAvailable = true;
    this.sucursalAvailable = true;
    this.openPayAvailable = false;
    this.openPayPublicConfig = null;
    this.ensureValidSelectedPaymentMethod();
  }

  private ensureValidSelectedPaymentMethod(): void {
    const order: Array<{ id: 'stripe' | 'openpay' | 'transferencia' | 'sucursal'; on: boolean }> = [
      { id: 'stripe', on: this.stripeAvailable },
      { id: 'openpay', on: this.openPayAvailable },
      { id: 'transferencia', on: this.transferenciaAvailable },
      { id: 'sucursal', on: this.sucursalAvailable },
    ];
    if (order.some(o => o.id === this.paymentMethod && o.on)) {
      return;
    }
    const first = order.find(o => o.on);
    this.paymentMethod = first ? first.id : 'stripe';
  }

  private selectedPaymentMethodAllowed(): boolean {
    switch (this.paymentMethod) {
      case 'stripe':
        return this.stripeAvailable;
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
    return this.stripeAvailable || this.openPayAvailable || this.transferenciaAvailable || this.sucursalAvailable;
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
        this.snackBar.open(`Pedido ${order.order_number} creado exitosamente`, 'Cerrar', { duration: 4000 });

        const asGuest = !this.isLoggedIn;

        if (this.paymentMethod === 'stripe') {
          if (asGuest) {
            this.guestThanksAfterOnlinePayment = {
              orderNumber: String(order.order_number ?? ''),
              guestEmail: String(this.guestForm.value.guest_email ?? ''),
            };
          } else {
            this.guestThanksAfterOnlinePayment = null;
          }
          this.createdOrderUuid = order.uuid;
          this.showStripePayment = true;
        } else if (this.paymentMethod === 'openpay') {
          this.openPayOrderTotal = Number(order.total ?? this.total);
          this.openPayBilling = {
            holder_name: String(order.shipping_name || order.guest_name || 'Cliente'),
            line1: String(order.shipping_address || 'N/A').slice(0, 200),
            line2: '',
            city: String(order.shipping_city || '').trim() || 'Ciudad',
            state: String(order.shipping_state || '').trim() || 'MX',
            postal_code:
              String(order.shipping_zip || '00000')
                .replace(/\D/g, '')
                .slice(0, 5) || '00000',
            country_code: 'MX',
          };
          if (asGuest) {
            this.guestThanksAfterOnlinePayment = {
              orderNumber: String(order.order_number ?? ''),
              guestEmail: String(this.guestForm.value.guest_email ?? ''),
            };
          } else {
            this.guestThanksAfterOnlinePayment = null;
          }
          this.createdOrderUuid = order.uuid;
          this.showOpenPayPayment = true;
        } else if (asGuest) {
          this.router.navigate(['/boutique/gracias', order.uuid], {
            state: {
              orderNumber: order.order_number,
              guestEmail: this.guestForm.value.guest_email,
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

  private buildGuestOrderItems(): { product_uuid: string; quantity: number }[] {
    return (this.cart?.items || [])
      .map((i) => {
        const row = i as { product?: { uuid?: string }; product_uuid?: string; quantity: number };
        const productUuid = String(row.product?.uuid || row.product_uuid || '').trim();
        if (!productUuid) {
          return null;
        }
        return { product_uuid: productUuid, quantity: i.quantity };
      })
      .filter((x): x is { product_uuid: string; quantity: number } => x !== null);
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
    this.snackBar.open('Pago realizado exitosamente', 'Cerrar', { duration: 4000 });
    this.showStripePayment = false;
    this.showOpenPayPayment = false;
    this.createdOrderUuid = null;
    if (this.guestThanksAfterOnlinePayment) {
      const st = this.guestThanksAfterOnlinePayment;
      this.guestThanksAfterOnlinePayment = null;
      this.router.navigate(['/boutique/gracias', orderUuid], {
        state: { orderNumber: st.orderNumber, guestEmail: st.guestEmail },
      });
    } else {
      this.router.navigate(['/boutique/orders', orderUuid]);
    }
  }
}
