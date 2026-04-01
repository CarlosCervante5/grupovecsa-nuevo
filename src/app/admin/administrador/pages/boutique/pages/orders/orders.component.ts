import { Component, OnInit } from '@angular/core';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatCardModule } from '@angular/material/card';

import { BoutiqueAdminOrderService, OrderMetrics } from '../../services/boutique-admin-order.service';
import { BoutiqueOrder } from '../../../../../../boutique/interfaces/boutique.interfaces';
import { OrderStatusBadgeComponent } from '../../components/order-status-badge/order-status-badge.component';
import { reload } from '@helpers/session.helper';

@Component({
  selector: 'app-orders',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    CurrencyPipe,
    DatePipe,
    MatTableModule,
    MatPaginatorModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatSnackBarModule,
    MatProgressSpinnerModule,
    MatTooltipModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatCardModule,
    OrderStatusBadgeComponent,
  ],
  templateUrl: './orders.component.html',
  styleUrls: ['./orders.component.css']
})
export class OrdersComponent implements OnInit {

  displayedColumns: string[] = ['order_number', 'customer', 'status', 'payment', 'total', 'date', 'actions'];
  orders: BoutiqueOrder[] = [];
  loading = true;

  // Filters
  search = '';
  selectedStatus = '';
  dateFrom: Date | null = null;
  dateTo: Date | null = null;

  // Pagination
  totalItems = 0;
  pageSize = 15;
  currentPage = 1;

  // Metrics
  metrics: OrderMetrics = { total_orders: 0, pending_orders: 0, revenue: 0 };
  metricsLoading = true;

  orderStatuses = [
    { value: '', label: 'Todos' },
    { value: 'pendiente', label: 'Pendiente' },
    { value: 'pagado', label: 'Pagado' },
    { value: 'en_preparacion', label: 'En preparación' },
    { value: 'enviado', label: 'Enviado' },
    { value: 'entregado', label: 'Entregado' },
    { value: 'cancelado', label: 'Cancelado' },
  ];

  constructor(
    private _orderService: BoutiqueAdminOrderService,
    private _snackBar: MatSnackBar,
    private _router: Router
  ) {}

  ngOnInit(): void {
    this.loadOrders();
    this.loadMetrics();
  }

  loadOrders(): void {
    this.loading = true;
    this._orderService.search({
      status: this.selectedStatus || undefined,
      search: this.search || undefined,
      date_from: this.dateFrom ? this.formatDate(this.dateFrom) : undefined,
      date_to: this.dateTo ? this.formatDate(this.dateTo) : undefined,
      page: this.currentPage,
      per_page: this.pageSize
    }).subscribe({
      next: (response) => {
        const paginated = (response.data as any).orders || response.data;
        this.orders = paginated.data;
        this.totalItems = paginated.total;
        this.loading = false;
      },
      error: (error) => {
        this.loading = false;
        reload(error, this._router);
      }
    });
  }

  loadMetrics(): void {
    this.metricsLoading = true;
    this._orderService.metrics({
      date_from: this.dateFrom ? this.formatDate(this.dateFrom) : undefined,
      date_to: this.dateTo ? this.formatDate(this.dateTo) : undefined,
    }).subscribe({
      next: (response) => {
        this.metrics = response.data as any;
        this.metricsLoading = false;
      },
      error: () => {
        this.metricsLoading = false;
      }
    });
  }

  onSearch(): void {
    this.currentPage = 1;
    this.loadOrders();
  }

  onStatusFilter(): void {
    this.currentPage = 1;
    this.loadOrders();
  }

  onDateFilter(): void {
    this.currentPage = 1;
    this.loadOrders();
    this.loadMetrics();
  }

  clearDateFilters(): void {
    this.dateFrom = null;
    this.dateTo = null;
    this.onDateFilter();
  }

  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex + 1;
    this.pageSize = event.pageSize;
    this.loadOrders();
  }

  navigateToDetail(order: BoutiqueOrder): void {
    this._router.navigate(['/admin/administrador/boutique/orders', order.uuid]);
  }

  getCustomerName(order: BoutiqueOrder): string {
    return (order as any).user?.name || order.shipping_name || '—';
  }

  getPaymentMethod(order: BoutiqueOrder): string {
    if (!order.payment) return '—';
    const methods: Record<string, string> = {
      stripe: 'Tarjeta',
      transferencia: 'Transferencia',
      sucursal: 'Sucursal'
    };
    return methods[order.payment.method] || order.payment.method;
  }

  private formatDate(date: Date): string {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }
}
