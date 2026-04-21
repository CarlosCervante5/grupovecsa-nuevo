import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, CurrencyPipe } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { ReactiveFormsModule, FormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient } from '@angular/common/http';
import { Subscription } from 'rxjs';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';

import { environment } from '@environments/environment';
import { BoutiqueCartService } from '../../services/boutique-cart.service';
import { BoutiqueCheckoutService } from '../../services/boutique-checkout.service';
import { BoutiqueCart, ShippingQuote } from '../../interfaces/boutique.interfaces';
import { StripePaymentComponent } from '../../components/stripe-payment/stripe-payment.component';

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
  /** OpenPay visible solo si el backend expone merchant + llave pública para el modo actual. */
  openPayAvailable = false;

  // Order creation
  creatingOrder = false;

  // Stripe payment
  showStripePayment = false;
  createdOrderUuid: string | null = null;

  /** Si el pedido con Stripe fue como invitado, al terminar pago ir a /boutique/gracias (no /orders con guard). */
  private guestThanksAfterStripe: { orderNumber: string; guestEmail: string } | null = null;

  private subs: Subscription[] = [];

  constructor(
    private fb: FormBuilder,
    private http: HttpClient,
    private cartService: BoutiqueCartService,
    private checkoutService: BoutiqueCheckoutService,
    private snackBar: MatSnackBar,
    private router: Router
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
    this.loadCart();
    this.loadOpenPayAvailability();
  }

  private loadOpenPayAvailability(): void {
    const sub = this.checkoutService.getOpenPayPublicConfig().subscribe({
      next: (res) => {
        const d = res.data as any;
        if (!d) {
          this.openPayAvailable = false;
          return;
        }
        if (typeof d.available === 'boolean') {
          this.openPayAvailable = d.available;
        } else {
          this.openPayAvailable = !!(String(d.merchant_id || '').trim() && String(d.public_key || '').trim());
        }
      },
      error: () => {
        this.openPayAvailable = false;
      },
    });
    this.subs.push(sub);
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
  }

  onDeliveryMethodChange(): void {
    this.shippingQuotes = [];
    this.selectedQuote = null;
    this.quotesLoaded = false;
    this.selectedDealership = null;

    if (this.deliveryMethod === 'recoleccion_sucursal' && this.dealerships.length === 0) {
      this.loadDealerships();
    }
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

  getShippingQuotes(): void {
    if (this.shippingForm.invalid) {
      this.shippingForm.markAllAsTouched();
      return;
    }
    this.loadingQuotes = true;
    this.shippingQuotes = [];
    this.selectedQuote = null;
    this.quotesLoaded = false;

    const formVal = this.shippingForm.value;
    const sub = this.checkoutService.shippingQuote({
      shipping_address: formVal.shipping_address,
      shipping_city: formVal.shipping_city,
      shipping_state: formVal.shipping_state,
      shipping_zip: formVal.shipping_zip,
    }).subscribe({
      next: (res: any) => {
        // Backend returns { data: { shipping_options: [...] } }
        const data = res.data;
        this.shippingQuotes = Array.isArray(data) ? data : (data?.shipping_options || []);
        this.quotesLoaded = true;
        this.loadingQuotes = false;
      },
      error: () => {
        this.loadingQuotes = false;
        this.snackBar.open('Error al obtener cotizaciones de envío', 'Cerrar', { duration: 3000 });
      },
    });
    this.subs.push(sub);
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
    return true;
  }

  confirmOrder(): void {
    if (!this.canConfirm || this.creatingOrder) return;
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

    const order$ = this.isLoggedIn
      ? this.checkoutService.createOrder(baseParams)
      : this.checkoutService.createGuestOrder({
          ...baseParams,
          guest_name: this.guestForm.value.guest_name,
          guest_email: this.guestForm.value.guest_email,
          items: (this.cart!.items || []).map(i => ({
            product_uuid: i.product.uuid,
            quantity: i.quantity,
          })),
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
            this.guestThanksAfterStripe = {
              orderNumber: String(order.order_number ?? ''),
              guestEmail: String(this.guestForm.value.guest_email ?? ''),
            };
          } else {
            this.guestThanksAfterStripe = null;
          }
          this.createdOrderUuid = order.uuid;
          this.showStripePayment = true;
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
        const msg = err?.error?.message || 'Error al crear el pedido';
        this.snackBar.open(msg, 'Cerrar', { duration: 4000 });
      },
    });
    this.subs.push(sub);
  }

  getItemImage(item: any): string {
    if (item.product?.images && item.product.images.length > 0) {
      return item.product.images[0].image_path;
    }
    return 'assets/images/placeholder-product.svg';
  }

  onPaymentSuccess(orderUuid: string): void {
    this.snackBar.open('Pago realizado exitosamente', 'Cerrar', { duration: 4000 });
    if (this.guestThanksAfterStripe) {
      const st = this.guestThanksAfterStripe;
      this.guestThanksAfterStripe = null;
      this.router.navigate(['/boutique/gracias', orderUuid], {
        state: { orderNumber: st.orderNumber, guestEmail: st.guestEmail },
      });
    } else {
      this.router.navigate(['/boutique/orders', orderUuid]);
    }
  }
}
