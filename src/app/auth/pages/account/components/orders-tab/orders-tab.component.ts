import { Component, OnInit } from '@angular/core';
import { OrdersService } from '../../services/orders.service';
import { Order, OrderDetail } from '../../interfaces/orders.interface';

@Component({
  selector: 'app-orders-tab',
  templateUrl: './orders-tab.component.html',
  styleUrls: ['./orders-tab.component.css'],
  standalone: false
})
export class OrdersTabComponent implements OnInit {

  orders: Order[] = [];
  loading = true;
  error = false;
  loaded = false;

  selectedOrder: OrderDetail | null = null;
  loadingDetail = false;

  constructor(private ordersService: OrdersService) {}

  ngOnInit(): void {
    this.loadOrders();
  }

  loadOrders(): void {
    if (this.loaded) { return; }
    this.loading = true;
    this.error = false;
    this.ordersService.search().subscribe({
      next: (res) => {
        this.orders = (res.data.orders || []).sort((a, b) =>
          new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        );
        this.loading = false;
        this.loaded = true;
      },
      error: () => {
        this.loading = false;
        this.error = true;
      }
    });
  }

  retry(): void {
    this.loaded = false;
    this.loadOrders();
  }

  viewDetail(uuid: string): void {
    this.loadingDetail = true;
    this.ordersService.detail(uuid).subscribe({
      next: (res) => {
        this.selectedOrder = res.data.order;
        this.loadingDetail = false;
      },
      error: () => {
        this.loadingDetail = false;
      }
    });
  }

  backToList(): void {
    this.selectedOrder = null;
  }

  getStatusColor(status: string): string {
    const map: { [key: string]: string } = {
      pendiente: '#94a3b8',
      pagado: '#1c69d4',
      en_preparacion: '#eab308',
      enviado: '#f97316',
      entregado: '#22c55e',
      cancelado: '#ef4444'
    };
    return map[status] || '#94a3b8';
  }

  getStatusLabel(status: string): string {
    const map: { [key: string]: string } = {
      pendiente: 'Pendiente',
      pagado: 'Pagado',
      en_preparacion: 'En preparación',
      enviado: 'Enviado',
      entregado: 'Entregado',
      cancelado: 'Cancelado'
    };
    return map[status] || status;
  }
}
