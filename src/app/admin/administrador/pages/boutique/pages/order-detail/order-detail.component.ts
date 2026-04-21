import { Component, OnInit, Inject } from '@angular/core';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatDialogModule, MatDialog, MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDividerModule } from '@angular/material/divider';

import { BoutiqueAdminOrderService } from '../../services/boutique-admin-order.service';
import { BoutiqueOrder } from '../../../../../../boutique/interfaces/boutique.interfaces';
import { OrderStatusBadgeComponent, ORDER_STATUS_MAP } from '../../components/order-status-badge/order-status-badge.component';
import { reload } from '@helpers/session.helper';

@Component({
  selector: 'app-order-detail',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CurrencyPipe,
    DatePipe,
    MatButtonModule,
    MatIconModule,
    MatTableModule,
    MatDialogModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    MatTooltipModule,
    MatDividerModule,
    OrderStatusBadgeComponent,
  ],
  templateUrl: './order-detail.component.html',
  styleUrls: ['./order-detail.component.css']
})
export class OrderDetailComponent implements OnInit {

  order: BoutiqueOrder | null = null;
  loading = true;
  actionLoading = false;
  itemColumns: string[] = ['product', 'sku', 'quantity', 'unit_price', 'subtotal'];

  private validTransitions: Record<string, string[]> = {
    pendiente: ['pagado', 'cancelado'],
    pagado: ['en_preparacion', 'cancelado'],
    en_preparacion: ['enviado', 'cancelado'],
    enviado: ['entregado'],
    entregado: [],
    cancelado: [],
  };

  constructor(
    private _route: ActivatedRoute,
    private _router: Router,
    private _orderService: BoutiqueAdminOrderService,
    private _dialog: MatDialog,
    private _snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    const uuid = this._route.snapshot.paramMap.get('uuid');
    if (uuid) {
      this.loadOrder(uuid);
    }
  }

  loadOrder(uuid: string): void {
    this.loading = true;
    this._orderService.detail(uuid).subscribe({
      next: (response) => {
        this.order = (response.data as any).order || response.data;
        this.loading = false;
      },
      error: (error) => {
        this.loading = false;
        reload(error, this._router);
      }
    });
  }

  goBack(): void {
    this._router.navigate(['/admin/administrador/boutique/orders']);
  }

  getAvailableTransitions(): string[] {
    if (!this.order) return [];
    return this.validTransitions[this.order.status] || [];
  }

  getStatusLabel(status: string): string {
    return ORDER_STATUS_MAP[status]?.label || status;
  }

  getDeliveryMethodLabel(): string {
    if (!this.order) return '';
    return this.order.delivery_method === 'envio_domicilio' ? 'Envío a domicilio' : 'Recolección en sucursal';
  }

  getPaymentMethodLabel(): string {
    if (!this.order?.payment) return '—';
    const methods: Record<string, string> = {
      stripe: 'Tarjeta (Stripe)',
      openpay: 'Tarjeta (OpenPay)',
      transferencia: 'Transferencia bancaria',
      sucursal: 'Pago en sucursal'
    };
    return methods[this.order.payment.method] || this.order.payment.method;
  }

  canConfirmPayment(): boolean {
    if (!this.order?.payment) return false;
    return this.order.payment.status === 'pendiente' &&
           (this.order.payment.method === 'transferencia' ||
            this.order.payment.method === 'sucursal' ||
            this.order.payment.method === 'openpay');
  }

  canGenerateLabel(): boolean {
    if (!this.order?.shipment) return false;
    return this.order.delivery_method === 'envio_domicilio' &&
           (this.order.status === 'en_preparacion' || this.order.status === 'pagado') &&
           !this.order.shipment.tracking_number;
  }

  changeStatus(newStatus: string): void {
    if (!this.order) return;

    const dialogRef = this._dialog.open(ConfirmStatusDialogComponent, {
      width: '400px',
      data: {
        orderNumber: this.order.order_number,
        currentStatus: this.getStatusLabel(this.order.status),
        newStatus: this.getStatusLabel(newStatus),
        isCancellation: newStatus === 'cancelado'
      }
    });

    dialogRef.afterClosed().subscribe((confirmed) => {
      if (confirmed && this.order) {
        this.actionLoading = true;
        this._orderService.updateStatus({ uuid: this.order.uuid, status: newStatus }).subscribe({
          next: (response) => {
            this.order = (response.data as any).order || response.data;
            this.actionLoading = false;
            this.showSnackBar('Estado actualizado correctamente');
          },
          error: (error) => {
            this.actionLoading = false;
            this.showSnackBar(error.error?.message || 'Error al actualizar estado', true);
          }
        });
      }
    });
  }

  confirmManualPayment(): void {
    if (!this.order) return;

    const dialogRef = this._dialog.open(ConfirmPaymentDialogComponent, {
      width: '450px',
      data: { orderNumber: this.order.order_number }
    });

    dialogRef.afterClosed().subscribe((result) => {
      if (result && this.order) {
        this.actionLoading = true;
        this._orderService.confirmManualPayment({
          order_uuid: this.order.uuid,
          transaction_reference: result.reference || undefined
        }).subscribe({
          next: (response) => {
            this.order = (response.data as any).order || response.data;
            this.actionLoading = false;
            this.showSnackBar('Pago confirmado exitosamente');
          },
          error: (error) => {
            this.actionLoading = false;
            this.showSnackBar(error.error?.message || 'Error al confirmar pago', true);
          }
        });
      }
    });
  }

  generateShippingLabel(): void {
    if (!this.order) return;

    const dialogRef = this._dialog.open(ConfirmStatusDialogComponent, {
      width: '400px',
      data: {
        orderNumber: this.order.order_number,
        currentStatus: 'Generar guía',
        newStatus: 'Se generará la guía de envío vía Envia.com',
        isCancellation: false,
        isLabel: true
      }
    });

    dialogRef.afterClosed().subscribe((confirmed) => {
      if (confirmed && this.order) {
        this.actionLoading = true;
        this._orderService.generateLabel(this.order.uuid).subscribe({
          next: () => {
            this.loadOrder(this.order!.uuid);
            this.actionLoading = false;
            this.showSnackBar('Guía de envío generada exitosamente');
          },
          error: (error) => {
            this.actionLoading = false;
            this.showSnackBar(error.error?.message || 'Error al generar guía', true);
          }
        });
      }
    });
  }

  openLabelUrl(): void {
    if (this.order?.shipment?.envia_label_url) {
      window.open(this.order.shipment.envia_label_url, '_blank');
    }
  }

  private showSnackBar(message: string, isError = false): void {
    this._snackBar.open(message, 'Cerrar', {
      duration: 3000,
      horizontalPosition: 'end',
      verticalPosition: 'top',
      panelClass: isError ? ['snack-error'] : ['snack-success']
    });
  }
}

// ─── Confirm Status Change Dialog ─────────────────────────────────────

@Component({
  selector: 'app-confirm-status-dialog',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule, MatIconModule],
  template: `
    <h2 mat-dialog-title>{{ data.isLabel ? 'Generar guía de envío' : 'Cambiar estado del pedido' }}</h2>
    <mat-dialog-content>
      <p *ngIf="!data.isLabel">
        ¿Cambiar el estado del pedido <strong>{{ data.orderNumber }}</strong>
        de <strong>{{ data.currentStatus }}</strong> a <strong>{{ data.newStatus }}</strong>?
      </p>
      <p *ngIf="data.isLabel">
        ¿Generar guía de envío para el pedido <strong>{{ data.orderNumber }}</strong>?
      </p>
      <p *ngIf="data.isCancellation" class="warning-text">
        <mat-icon class="warning-icon">warning</mat-icon>
        Al cancelar el pedido se restaurará el inventario de los productos.
      </p>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="onCancel()">Cancelar</button>
      <button mat-flat-button [color]="data.isCancellation ? 'warn' : 'primary'" (click)="onConfirm()">
        Confirmar
      </button>
    </mat-dialog-actions>
  `,
  styles: [`
    .warning-text {
      display: flex;
      align-items: center;
      gap: 8px;
      color: #ef4444;
      font-size: 0.85rem;
      margin-top: 8px;
    }
    .warning-icon { font-size: 18px; width: 18px; height: 18px; }
  `]
})
export class ConfirmStatusDialogComponent {
  constructor(
    private _dialogRef: MatDialogRef<ConfirmStatusDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: {
      orderNumber: string;
      currentStatus: string;
      newStatus: string;
      isCancellation: boolean;
      isLabel?: boolean;
    }
  ) {}

  onCancel(): void { this._dialogRef.close(false); }
  onConfirm(): void { this._dialogRef.close(true); }
}

// ─── Confirm Manual Payment Dialog ────────────────────────────────────

@Component({
  selector: 'app-confirm-payment-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatDialogModule,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
  ],
  template: `
    <h2 mat-dialog-title>Confirmar pago manual</h2>
    <mat-dialog-content>
      <p>Confirmar el pago del pedido <strong>{{ data.orderNumber }}</strong>.</p>
      <mat-form-field appearance="outline" class="full-width">
        <mat-label>Referencia de transacción (opcional)</mat-label>
        <input matInput [(ngModel)]="reference" placeholder="Ej: Número de transferencia">
      </mat-form-field>
    </mat-dialog-content>
    <mat-dialog-actions align="end">
      <button mat-button (click)="onCancel()">Cancelar</button>
      <button mat-flat-button color="primary" (click)="onConfirm()">Confirmar pago</button>
    </mat-dialog-actions>
  `,
  styles: [`
    .full-width { width: 100%; margin-top: 12px; }
  `]
})
export class ConfirmPaymentDialogComponent {
  reference = '';

  constructor(
    private _dialogRef: MatDialogRef<ConfirmPaymentDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { orderNumber: string }
  ) {}

  onCancel(): void { this._dialogRef.close(null); }
  onConfirm(): void { this._dialogRef.close({ reference: this.reference }); }
}
