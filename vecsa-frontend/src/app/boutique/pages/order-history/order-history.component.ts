import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { RouterModule } from '@angular/router';
import { Subscription } from 'rxjs';

import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';

import { BoutiqueOrderService } from '../../services/boutique-order.service';
import { BoutiqueOrder } from '../../interfaces/boutique.interfaces';

@Component({
  selector: 'app-order-history',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    CurrencyPipe,
    DatePipe,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatPaginatorModule,
    MatSnackBarModule,
  ],
  templateUrl: './order-history.component.html',
  styleUrls: ['./order-history.component.css'],
})
export class OrderHistoryComponent implements OnInit, OnDestroy {
  orders: BoutiqueOrder[] = [];
  isLoading = true;

  // Pagination
  totalOrders = 0;
  pageSize = 10;
  currentPage = 1;

  private sub?: Subscription;

  constructor(
    private orderService: BoutiqueOrderService,
    private snackBar: MatSnackBar
  ) {}

  ngOnInit(): void {
    this.loadOrders();
  }

  ngOnDestroy(): void {
    this.sub?.unsubscribe();
  }

  loadOrders(): void {
    this.isLoading = true;
    this.sub?.unsubscribe();
    this.sub = this.orderService.search({
      page: this.currentPage,
      per_page: this.pageSize,
    }).subscribe({
      next: (res) => {
        const wrapper = res.data as any;
        // Backend returns { orders: [...] } (flat array, not paginated)
        const orders = wrapper.orders || wrapper.data || wrapper;
        this.orders = Array.isArray(orders) ? orders : (orders.data || []);
        this.totalOrders = wrapper.total ?? (Array.isArray(orders) ? orders.length : 0);
        this.isLoading = false;
      },
      error: () => {
        this.isLoading = false;
        this.snackBar.open('Error al cargar los pedidos', 'Cerrar', { duration: 3000 });
      },
    });
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadOrders();
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
}
