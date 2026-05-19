import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';

import { BoutiqueOrderService } from '../../services/boutique-order.service';
import { BoutiqueShippingService } from '../../services/boutique-shipping.service';
import {
  BoutiqueOrder,
  BoutiqueTransferBankDetails,
  TrackingInfo,
  TrackingEvent,
} from '../../interfaces/boutique.interfaces';

interface StatusStep {
  key: string;
  label: string;
  icon: string;
}

@Component({
  selector: 'app-order-detail',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    CurrencyPipe,
    DatePipe,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatSnackBarModule,
  ],
  templateUrl: './order-detail.component.html',
  styleUrls: ['./order-detail.component.css'],
})
export class OrderDetailComponent implements OnInit, OnDestroy {
  order: BoutiqueOrder | null = null;
  transferBank: BoutiqueTransferBankDetails | null = null;
  isLoading = true;
  errorMessage = '';

  // Tracking
  trackingInfo: TrackingInfo | null = null;
  loadingTracking = false;
  trackingError = '';

  // Status progress steps
  statusSteps: StatusStep[] = [
    { key: 'pendiente', label: 'Pendiente', icon: 'hourglass_empty' },
    { key: 'pagado', label: 'Pagado', icon: 'payment' },
    { key: 'en_preparacion', label: 'En preparación', icon: 'inventory_2' },
    { key: 'enviado', label: 'Enviado', icon: 'local_shipping' },
    { key: 'entregado', label: 'Entregado', icon: 'check_circle' },
  ];

  private subs: Subscription[] = [];

  constructor(
    private route: ActivatedRoute,
    private orderService: BoutiqueOrderService,
    private shippingService: BoutiqueShippingService,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    const uuid = this.route.snapshot.paramMap.get('uuid');
    if (uuid) {
      this.loadOrder(uuid);
    }
  }

  ngOnDestroy(): void {
    this.subs.forEach(s => s.unsubscribe());
  }

  loadOrder(uuid: string): void {
    this.isLoading = true;
    const sub = this.orderService.detail(uuid).subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        this.order = wrapper.order || wrapper;
        this.transferBank = wrapper.transfer_bank ?? null;
        this.isLoading = false;
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = err?.error?.message || 'Error al cargar el pedido';
        this.snackBar.open(this.errorMessage, 'Cerrar', { duration: 3000 });
      },
    });
    this.subs.push(sub);
  }

  trackShipment(): void {
    if (!this.order) return;
    this.loadingTracking = true;
    this.trackingError = '';
    this.trackingInfo = null;

    const sub = this.shippingService.track(this.order.uuid).subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        this.trackingInfo = wrapper.tracking || wrapper;
        this.loadingTracking = false;
      },
      error: (err) => {
        this.loadingTracking = false;
        this.trackingError = err?.error?.message || 'No se pudo obtener la información de rastreo';
        this.snackBar.open(this.trackingError, 'Cerrar', { duration: 3000 });
      },
    });
    this.subs.push(sub);
  }

  get isCancelled(): boolean {
    return this.order?.status === 'cancelado';
  }

  get showTransferBank(): boolean {
    return (
      this.order?.status === 'pendiente' &&
      this.order?.payment?.method === 'transferencia'
    );
  }

  get currentStepIndex(): number {
    if (!this.order) return -1;
    return this.statusSteps.findIndex(s => s.key === this.order!.status);
  }

  isStepCompleted(index: number): boolean {
    return index <= this.currentStepIndex;
  }

  isStepActive(index: number): boolean {
    return index === this.currentStepIndex;
  }

  getStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      pendiente: 'Pendiente',
      pagado: 'Pagado',
      en_preparacion: 'En preparación',
      enviado: 'Enviado',
      entregado: 'Entregado',
      cancelado: 'Cancelado',
    };
    return labels[status] || status;
  }

  getStatusClass(status: string): string {
    const classes: Record<string, string> = {
      pendiente: 'status-pendiente',
      pagado: 'status-pagado',
      en_preparacion: 'status-en-preparacion',
      enviado: 'status-enviado',
      entregado: 'status-entregado',
      cancelado: 'status-cancelado',
    };
    return classes[status] || '';
  }

  getPaymentMethodLabel(method: string): string {
    const labels: Record<string, string> = {
      stripe: 'Tarjeta de crédito/débito',
      transferencia: 'Transferencia bancaria',
      sucursal: 'Pago en sucursal',
    };
    return labels[method] || method;
  }

  getPaymentStatusLabel(status: string): string {
    const labels: Record<string, string> = {
      pendiente: 'Pendiente',
      completado: 'Completado',
      fallido: 'Fallido',
      reembolsado: 'Reembolsado',
    };
    return labels[status] || status;
  }

  getDeliveryMethodLabel(method: string): string {
    return method === 'envio_domicilio' ? 'Envío a domicilio' : 'Recolección en sucursal';
  }

  get canTrack(): boolean {
    if (!this.order || !this.order.shipment) return false;
    return !!this.order.shipment.tracking_number;
  }
}
