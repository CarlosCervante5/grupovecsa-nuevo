import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';

export interface OrderStatusConfig {
  label: string;
  cssClass: string;
  icon: string;
}

export const ORDER_STATUS_MAP: Record<string, OrderStatusConfig> = {
  pendiente: { label: 'Pendiente', cssClass: 'status-pendiente', icon: 'schedule' },
  pagado: { label: 'Pagado', cssClass: 'status-pagado', icon: 'paid' },
  en_preparacion: { label: 'En preparación', cssClass: 'status-preparacion', icon: 'inventory_2' },
  enviado: { label: 'Enviado', cssClass: 'status-enviado', icon: 'local_shipping' },
  entregado: { label: 'Entregado', cssClass: 'status-entregado', icon: 'check_circle' },
  cancelado: { label: 'Cancelado', cssClass: 'status-cancelado', icon: 'cancel' },
};

export const PAYMENT_STATUS_MAP: Record<string, OrderStatusConfig> = {
  pendiente: { label: 'Pendiente', cssClass: 'status-pendiente', icon: 'schedule' },
  completado: { label: 'Completado', cssClass: 'status-entregado', icon: 'check_circle' },
  fallido: { label: 'Fallido', cssClass: 'status-cancelado', icon: 'error' },
  reembolsado: { label: 'Reembolsado', cssClass: 'status-preparacion', icon: 'undo' },
};

@Component({
  selector: 'app-order-status-badge',
  standalone: true,
  imports: [CommonModule],
  template: `
    <span class="status-badge" [ngClass]="config.cssClass">
      {{ config.label }}
    </span>
  `,
  styles: [`
    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 500;
      white-space: nowrap;
    }
    .status-pendiente { background-color: #78350f; color: #fde68a; }
    .status-pagado { background-color: #1e3a5f; color: #93c5fd; }
    .status-preparacion { background-color: #4c1d95; color: #c4b5fd; }
    .status-enviado { background-color: #164e63; color: #67e8f9; }
    .status-entregado { background-color: #065f46; color: #6ee7b7; }
    .status-cancelado { background-color: #7f1d1d; color: #fca5a5; }
  `]
})
export class OrderStatusBadgeComponent {
  @Input() status: string = '';
  @Input() type: 'order' | 'payment' = 'order';

  get config(): OrderStatusConfig {
    const map = this.type === 'payment' ? PAYMENT_STATUS_MAP : ORDER_STATUS_MAP;
    return map[this.status] || { label: this.status, cssClass: 'status-pendiente', icon: 'help' };
  }
}
