import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, OnDestroy } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { ImageAiDialogComponent } from 'src/app/shared/components/image-ai-dialog/image-ai-dialog.component';
import { Router } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { StoreService } from '../../services/store.service';
import { environment } from '@environments/environment';
import { adminDashboardUrl } from 'src/app/admin/utils/admin-route.util';
import { DashboardStat, CouponCreate, PointAdjustment } from '../../interfaces/store.interfaces';
import * as echarts from 'echarts';

@Component({
  selector: 'app-store-layout',
  templateUrl: './store-layout.component.html',
  styleUrls: ['./store-layout.component.css'],
  standalone: false,
})
export class StoreLayoutComponent implements OnInit, AfterViewInit, OnDestroy {
  user: any = null;
  role = '';
  activeSection = 'dashboard';

  // Dashboard
  stats: DashboardStat[] = [
    { label: 'Total Pedidos', value: '—', icon: 'receipt_long', color: '#1c69d4', loading: true },
    { label: 'Ingresos', value: '—', icon: 'attach_money', color: '#059669', loading: true },
    { label: 'Pedidos Pendientes', value: '—', icon: 'pending_actions', color: '#f59e0b', loading: true },
    { label: 'Clientes', value: '—', icon: 'people', color: '#7c3aed', loading: true },
    { label: 'Productos Activos', value: '—', icon: 'inventory_2', color: '#dc2626', loading: true },
  ];

  @ViewChild('ordersMonthChart') ordersMonthChartEl!: ElementRef;
  @ViewChild('ordersStatusChart') ordersStatusChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];

  // Generic list state
  loading = false;
  rows: any[] = [];
  currentPage = 1;
  lastPage = 1;
  total = 0;
  searchTerm = '';

  // Orders filters
  orderStatusFilter = '';
  orderDateFrom = '';
  orderDateTo = '';

  // Order detail
  selectedOrder: any = null;
  orderDetailLoading = false;
  statusChangeValue = '';
  statusChanging = false;
  labelGenerating = false;

  // Shipments filters
  shipmentStatusFilter = '';
  shipmentCarrierFilter = '';

  // Customer detail
  selectedCustomer: any = null;
  customerDetailLoading = false;
  customerOrders: any[] = [];
  customerPoints: any[] = [];

  // Points
  pointsAdjustModal = false;
  adjustData: PointAdjustment = { customer_reward_uuid: '', points: 0, reason: '', type: 'add' };
  adjustSaving = false;
  adjustError = '';
  adjustSuccess = '';

  // Coupons
  couponModal = false;
  couponMode: 'create' | 'edit' = 'create';
  couponData: any = {};
  couponSaving = false;
  couponError = '';
  deleteConfirmOpen = false;
  deleteTarget: any = null;
  deleteLoading = false;

  // Redemptions
  redemptionStatusFilter = '';

  // Products
  productRows: any[] = [];
  productLoading = false;
  productSearch = '';
  productPage = 1;
  productLastPage = 1;
  productTotal = 0;
  /** Tamaño de página en la tabla de productos (store). */
  productPageSize = 50;
  selectedProduct: any = null;
  productDetailLoading = false;
  productEditing = false;
  productEditData: any = {};
  productSaving = false;
  productSaveError = '';
  productSaveSuccess = '';
  categoryOptions: { uuid: string; name: string }[] = [];
  /** Página actual de la tabla de categorías (sección Categorías). */
  categoryAdminRows: any[] = [];
  categoryPage = 1;
  categoryLastPage = 1;
  categoryTotal = 0;
  readonly categoryPageSize = 15;
  categorySectionLoading = false;
  /** Hasta 500 categorías para selects (padre en modal, filtro productos). */
  categorySelectorRows: any[] = [];
  categoryModalOpen = false;
  categoryModalMode: 'create' | 'edit' = 'create';
  categoryModalSaving = false;
  categoryModalError = '';
  categoryForm: { uuid: string; name: string; description: string; active: boolean; parent_uuid: string } = {
    uuid: '',
    name: '',
    description: '',
    active: true,
    parent_uuid: '',
  };
  /** Filtro de listado de productos por categoría (uuid vacío = todas). */
  productCategoryFilter = '';
  editVariants: any[] = [];
  productMode: 'edit' | 'create' = 'edit';
  hasVariants = false;

  // Attributes & Variants
  availableAttributes: any[] = [];
  selectedProductAttributes: { attribute_uuid: string; attribute_name: string; values: any[] }[] = [];
  newAttributeName = '';
  newValueInputs: Record<string, string> = {};
  selectedAttrUuid = '';
  generatingVariants = false;
  variantsSaving = false;
  modifiedVariantUuids: Set<string> = new Set();

  openpayConfig: Record<string, string> = {};
  openpayPasarelaEnabled = true;
  openpayKeysConfigured = false;
  openpayLoading = false;
  openpaySaving = false;
  openpayError = '';
  openpaySuccess = '';

  /** Toggles y estado efectivo (checkout boutique). */
  checkoutPayLoading = false;
  checkoutPaySaving = false;
  checkoutPayError = '';
  checkoutPaySuccess = '';
  /** Lo que el cliente ve en el checkout (tras llaves + flags). */
  checkoutPayShowOpenpay = false;
  checkoutPayShowTransferencia = false;
  checkoutPayShowSucursal = false;
  /** Si existen credenciales OpenPay (puede activar el switch). */
  checkoutPayKeysOpenpay = false;
  /** Preferencia guardada (flag en BD). */
  checkoutPayFlagOpenpay = true;
  checkoutPayTransferencia = true;
  checkoutPaySucursal = true;

  /** Formulario admin: rutas/URLs de avisos legales del checkout (vacío = usar defecto del sistema). */
  checkoutLegalFormTermsUrl = '';
  checkoutLegalFormPrivacyUrl = '';
  checkoutLegalFormReturnsUrl = '';
  /** URLs efectivas mostradas al cliente (solo referencia en el panel). */
  checkoutLegalEffectiveTerms = '';
  checkoutLegalEffectivePrivacy = '';
  checkoutLegalEffectiveReturns = '';
  checkoutLegalPagesSaving = false;
  checkoutLegalPagesSuccess = '';
  checkoutLegalPagesError = '';

  boutiqueDealershipRows: {
    id: number;
    name: string;
    location: string;
    state?: string | null;
    whatsapp_phone?: string;
    editPhone: string;
  }[] = [];
  boutiqueDealershipLoading = false;
  boutiqueDealershipSavingId: number | null = null;
  boutiqueDealershipError = '';
  boutiqueDealershipSuccess = '';

  transferBankSaving = false;
  transferBankSuccess = '';
  transferBankError = '';
  transferBankName = '';
  transferAccountHolder = '';
  transferClabe = '';
  transferAccountNumber = '';
  transferInstructions = '';

  readonly navItems = [
    { key: 'dashboard', label: 'Dashboard', icon: 'dashboard' },
    { key: 'categories', label: 'Categorías', icon: 'category' },
    { key: 'attributes', label: 'Atributos', icon: 'tune' },
    { key: 'products', label: 'Productos', icon: 'inventory_2' },
    { key: 'orders', label: 'Pedidos', icon: 'receipt_long' },
    { key: 'shipping', label: 'Envíos', icon: 'local_shipping' },
    { key: 'customers', label: 'Clientes', icon: 'people' },
    { key: 'points', label: 'Puntos', icon: 'stars' },
    { key: 'coupons', label: 'Cupones', icon: 'confirmation_number' },
    { key: 'redemptions', label: 'Redenciones', icon: 'redeem' },
    { key: 'checkout_payments', label: 'Métodos de pago (checkout)', icon: 'payments' },
    { key: 'boutique_dealerships', label: 'Sucursales (WhatsApp)', icon: 'store' },
    { key: 'openpay', label: 'Pagos OpenPay', icon: 'account_balance' },
    { key: 'incadea', label: 'Sync Incadea', icon: 'sync' },
    { key: 'wc_import', label: 'WC Import', icon: 'upload_file' },
  ];

  readonly orderStatuses = ['pendiente', 'pagado', 'en_preparacion', 'enviado', 'entregado', 'cancelado'];

  get panelHomeUrl(): string {
    return adminDashboardUrl(this.role);
  }

  constructor(
    private router: Router,
    private storeService: StoreService,
    private http: HttpClient,
    private auth: AuthService,
    private dialog: MatDialog,
  ) {}

  ngOnInit(): void {
    const raw = localStorage.getItem('user');
    if (raw) { try { this.user = JSON.parse(raw); } catch {} }
    this.role = localStorage.getItem('role') || '';
    this.loadDashboard();
  }

  ngAfterViewInit(): void {
    setTimeout(() => this.initCharts(), 400);
  }

  ngOnDestroy(): void {
    this.chartInstances.forEach(c => c.dispose());
  }

  // ── Navigation ──
  selectSection(key: string): void {
    this.activeSection = key;
    this.rows = [];
    this.searchTerm = '';
    this.currentPage = 1;
    this.selectedOrder = null;
    this.selectedCustomer = null;
    this.couponModal = false;
    this.pointsAdjustModal = false;
    this.deleteConfirmOpen = false;
    this.categoryModalOpen = false;

    if (key === 'dashboard') {
      this.loadDashboard();
      this.chartInstances.forEach(c => c.dispose());
      this.chartInstances = [];
      setTimeout(() => this.initCharts(), 400);
    } else if (key === 'orders') {
      this.loadOrders();
    } else if (key === 'categories') {
      this.categoryPage = 1;
      this.loadCategorySelectorsFromApi();
      this.loadCategoryTableFromApi();
    } else if (key === 'attributes') {
      this.storePanelDebug('selectSection:attributes');
      this.loadCatalogAttributesList();
    } else if (key === 'products') {
      if (this.categorySelectorRows.length === 0) {
        this.loadCategorySelectorsFromApi();
      }
      this.loadProducts();
    } else if (key === 'shipping') {
      this.loadShipments();
    } else if (key === 'customers') {
      this.loadCustomers();
    } else if (key === 'points') {
      this.loadPoints();
    } else if (key === 'coupons') {
      this.loadCoupons();
    } else if (key === 'redemptions') {
      this.loadRedemptions();
    } else if (key === 'checkout_payments') {
      this.loadCheckoutPaymentMethodsConfig();
    } else if (key === 'boutique_dealerships') {
      this.loadBoutiqueDealerships();
    } else if (key === 'openpay') {
      this.loadOpenpayConfig();
    } else if (key === 'incadea') {
      this.loadIncadea();
    }
  }

  logout(): void {
    this.auth.signOut(this.router);
  }

  // ── Dashboard ──
  private loadDashboard(): void {
    this.stats.forEach(s => { s.loading = true; s.value = '—'; });
    this.storeService.getDashboardMetrics().subscribe({
      next: (res: any) => {
        const d = res?.data || res;
        this.stats[0].value = d.total_orders ?? 0;
        this.stats[1].value = '$' + Number(d.revenue || 0).toLocaleString();
        this.stats[2].value = d.pending_orders ?? 0;
        this.stats[3].value = d.total_customers ?? 0;
        this.stats[4].value = d.total_products ?? 0;
        this.stats.forEach(s => s.loading = false);
      },
      error: () => this.stats.forEach(s => { s.loading = false; s.value = 'Error'; }),
    });
  }

  private initCharts(): void {
    this.loadOrdersMonthChart();
    this.loadOrdersStatusChart();
    window.addEventListener('resize', () => this.chartInstances.forEach(c => c.resize()));
  }

  private loadOrdersMonthChart(): void {
    if (!this.ordersMonthChartEl) return;
    const chart = echarts.init(this.ordersMonthChartEl.nativeElement);
    this.chartInstances.push(chart);
    this.storeService.getDashboardMetrics().subscribe({
      next: (res: any) => {
        const d = res?.data || res;
        const months = d.orders_by_month || [];
        chart.setOption({
          tooltip: { trigger: 'axis' },
          grid: { left: 40, right: 16, top: 20, bottom: 30 },
          xAxis: { type: 'category', data: months.map((m: any) => m.month), axisLabel: { fontSize: 11 } },
          yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
          series: [{ data: months.map((m: any) => m.count), type: 'bar', itemStyle: { color: '#1c69d4', borderRadius: [4, 4, 0, 0] } }],
        });
      },
      error: () => chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }),
    });
  }

  private loadOrdersStatusChart(): void {
    if (!this.ordersStatusChartEl) return;
    const chart = echarts.init(this.ordersStatusChartEl.nativeElement);
    this.chartInstances.push(chart);
    const colors: Record<string, string> = {
      pendiente: '#f59e0b', pagado: '#059669', en_preparacion: '#3b82f6',
      enviado: '#1c69d4', entregado: '#10b981', cancelado: '#ef4444',
    };
    this.storeService.getDashboardMetrics().subscribe({
      next: (res: any) => {
        const d = res?.data || res;
        const statuses = d.orders_by_status || [];
        chart.setOption({
          tooltip: { trigger: 'item' },
          series: [{
            type: 'pie', radius: ['40%', '70%'], label: { fontSize: 11 },
            data: statuses.map((s: any) => ({ name: s.status, value: s.count, itemStyle: { color: colors[s.status] || '#94a3b8' } })),
            emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.2)' } },
          }],
        });
      },
      error: () => chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }),
    });
  }

  // ── Orders ──
  loadOrders(): void {
    this.loading = true;
    this.storeService.searchOrders({
      page: this.currentPage,
      search: this.searchTerm || undefined,
      status: this.orderStatusFilter || undefined,
      date_from: this.orderDateFrom || undefined,
      date_to: this.orderDateTo || undefined,
    }).subscribe({
      next: (res: any) => {
        const d = res?.data?.orders || res?.data || {};
        this.rows = d.data || [];
        this.lastPage = d.last_page || 1;
        this.total = d.total || 0;
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onOrderSearch(): void { this.currentPage = 1; this.loadOrders(); }

  viewOrderDetail(order: any): void {
    this.orderDetailLoading = true;
    this.selectedOrder = null;
    this.storeService.getOrderDetail(order.uuid).subscribe({
      next: (res: any) => {
        this.selectedOrder = res?.data?.order || res?.data || res;
        this.statusChangeValue = this.selectedOrder?.status || '';
        this.orderDetailLoading = false;
      },
      error: () => { this.orderDetailLoading = false; },
    });
  }

  backToOrders(): void { this.selectedOrder = null; }

  changeOrderStatus(): void {
    if (!this.selectedOrder || !this.statusChangeValue) return;
    this.statusChanging = true;
    this.storeService.updateOrderStatus(this.selectedOrder.uuid, this.statusChangeValue).subscribe({
      next: (res: any) => {
        this.selectedOrder = res?.data?.order || this.selectedOrder;
        this.selectedOrder.status = this.statusChangeValue;
        this.statusChanging = false;
        this.loadOrders();
      },
      error: (err: any) => {
        alert(err?.error?.message || 'Error al cambiar estado');
        this.statusChanging = false;
      },
    });
  }

  generateLabel(): void {
    if (!this.selectedOrder) return;
    this.labelGenerating = true;
    this.storeService.generateShippingLabel(this.selectedOrder.uuid).subscribe({
      next: (res: any) => {
        this.labelGenerating = false;
        this.viewOrderDetail(this.selectedOrder);
        alert('Guía generada correctamente');
      },
      error: (err: any) => {
        this.labelGenerating = false;
        alert(err?.error?.message || 'Error al generar guía');
      },
    });
  }

  getStatusBadgeClass(status: string): string {
    return 'badge badge-' + (status || '').replace(/ /g, '_');
  }

  // ── Shipments ──
  loadShipments(): void {
    this.loading = true;
    this.storeService.searchShipments({
      page: this.currentPage,
      search: this.searchTerm || undefined,
      status: this.shipmentStatusFilter || undefined,
      carrier: this.shipmentCarrierFilter || undefined,
    }).subscribe({
      next: (res: any) => {
        const d = res?.data?.shipments || res?.data || {};
        this.rows = d.data || d || [];
        this.lastPage = d.last_page || 1;
        this.total = d.total || (Array.isArray(this.rows) ? this.rows.length : 0);
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onShipmentSearch(): void { this.currentPage = 1; this.loadShipments(); }

  // ── Customers ──
  loadCustomers(): void {
    this.loading = true;
    this.storeService.searchCustomers({
      page: this.currentPage,
      search: this.searchTerm || undefined,
    }).subscribe({
      next: (res: any) => {
        const d = res?.data?.customers || res?.data || {};
        this.rows = d.data || d || [];
        this.lastPage = d.last_page || 1;
        this.total = d.total || (Array.isArray(this.rows) ? this.rows.length : 0);
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onCustomerSearch(): void { this.currentPage = 1; this.loadCustomers(); }

  viewCustomerDetail(customer: any): void {
    this.customerDetailLoading = true;
    this.selectedCustomer = null;
    this.customerOrders = [];
    this.customerPoints = [];
    const uuid = customer.uuid || customer.customer_uuid;
    this.storeService.getCustomerDetail(uuid).subscribe({
      next: (res: any) => {
        this.selectedCustomer = res?.data?.customer || res?.data || res;
        this.customerOrders = this.selectedCustomer?.orders || [];
        this.customerPoints = this.selectedCustomer?.rewards || [];
        this.customerDetailLoading = false;
      },
      error: () => { this.customerDetailLoading = false; },
    });
  }

  backToCustomers(): void { this.selectedCustomer = null; }

  // ── Points ──
  loadPoints(): void {
    this.loading = true;
    this.storeService.searchPoints({
      page: this.currentPage,
      search: this.searchTerm || undefined,
    }).subscribe({
      next: (res: any) => {
        const d = res?.data?.customers || res?.data || {};
        this.rows = d.data || d || [];
        this.lastPage = d.last_page || 1;
        this.total = d.total || (Array.isArray(this.rows) ? this.rows.length : 0);
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onPointsSearch(): void { this.currentPage = 1; this.loadPoints(); }

  openAdjustModal(row: any): void {
    this.adjustData = {
      customer_reward_uuid: row.customer_reward_uuid || row.uuid || '',
      points: 0,
      reason: '',
      type: 'add',
    };
    this.adjustError = '';
    this.adjustSuccess = '';
    this.pointsAdjustModal = true;
  }

  closeAdjustModal(): void { this.pointsAdjustModal = false; }

  saveAdjust(): void {
    if (this.adjustData.points <= 0) { this.adjustError = 'Los puntos deben ser mayor a 0'; return; }
    if ((this.adjustData.reason || '').length < 5) { this.adjustError = 'El motivo debe tener al menos 5 caracteres'; return; }
    this.adjustSaving = true;
    this.adjustError = '';
    this.adjustSuccess = '';
    this.storeService.adjustPoints(this.adjustData).subscribe({
      next: (res: any) => {
        this.adjustSuccess = 'Ajuste realizado. Nuevo balance: ' + (res?.data?.new_balance ?? '');
        this.adjustSaving = false;
        this.loadPoints();
      },
      error: (err: any) => {
        this.adjustError = err?.error?.message || 'Error al ajustar puntos';
        this.adjustSaving = false;
      },
    });
  }

  // ── Coupons ──
  loadCoupons(): void {
    this.loading = true;
    this.storeService.searchCoupons({
      page: this.currentPage,
      search: this.searchTerm || undefined,
    }).subscribe({
      next: (res: any) => {
        const d = res?.data?.coupons || res?.data || {};
        this.rows = d.data || d || [];
        this.lastPage = d.last_page || 1;
        this.total = d.total || (Array.isArray(this.rows) ? this.rows.length : 0);
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onCouponSearch(): void { this.currentPage = 1; this.loadCoupons(); }

  openCouponCreate(): void {
    this.couponMode = 'create';
    this.couponData = { code: '', amount: 0, discount_type: 'fixed', description: '', usage_limit: null, minimum_amount: null, maximum_amount: null, individual_use: false };
    this.couponError = '';
    this.couponModal = true;
  }

  openCouponEdit(row: any): void {
    this.couponMode = 'edit';
    this.couponData = { ...row };
    this.couponError = '';
    this.couponModal = true;
  }

  closeCouponModal(): void { this.couponModal = false; }

  saveCoupon(): void {
    if (!this.couponData.code || this.couponData.code.length < 4 || this.couponData.code.length > 20) {
      this.couponError = 'El código debe tener entre 4 y 20 caracteres'; return;
    }
    if (this.couponData.amount <= 0) { this.couponError = 'El monto debe ser mayor a 0'; return; }
    if (this.couponData.discount_type === 'percentage' && this.couponData.amount > 100) {
      this.couponError = 'El porcentaje no puede ser mayor a 100'; return;
    }
    if (this.couponData.minimum_amount && this.couponData.maximum_amount && this.couponData.maximum_amount < this.couponData.minimum_amount) {
      this.couponError = 'El monto máximo debe ser mayor o igual al mínimo'; return;
    }
    this.couponSaving = true;
    this.couponError = '';

    const payload: CouponCreate = {
      code: this.couponData.code,
      amount: Number(this.couponData.amount),
      discount_type: this.couponData.discount_type,
      description: this.couponData.description || undefined,
      usage_limit: this.couponData.usage_limit ? Number(this.couponData.usage_limit) : undefined,
      minimum_amount: this.couponData.minimum_amount ? Number(this.couponData.minimum_amount) : undefined,
      maximum_amount: this.couponData.maximum_amount ? Number(this.couponData.maximum_amount) : undefined,
      individual_use: this.couponData.individual_use || false,
    };

    const obs = this.couponMode === 'create'
      ? this.storeService.createCoupon(payload)
      : this.storeService.updateCoupon(this.couponData.uuid, payload);

    obs.subscribe({
      next: () => { this.couponModal = false; this.couponSaving = false; this.loadCoupons(); },
      error: (err: any) => { this.couponError = err?.error?.message || 'Error al guardar cupón'; this.couponSaving = false; },
    });
  }

  confirmDeleteCoupon(row: any): void {
    this.deleteTarget = row;
    this.deleteConfirmOpen = true;
  }

  cancelDelete(): void { this.deleteConfirmOpen = false; this.deleteTarget = null; }

  executeDeleteCoupon(): void {
    if (!this.deleteTarget) return;
    this.deleteLoading = true;
    this.storeService.deleteCoupon(this.deleteTarget.uuid).subscribe({
      next: () => { this.deleteConfirmOpen = false; this.deleteTarget = null; this.deleteLoading = false; this.loadCoupons(); },
      error: () => { this.deleteLoading = false; },
    });
  }

  // ── Redemptions ──
  loadRedemptions(): void {
    this.loading = true;
    this.storeService.searchRedemptions({
      page: this.currentPage,
      status: this.redemptionStatusFilter || undefined,
    }).subscribe({
      next: (res: any) => {
        const d = res?.data?.redemptions || res?.data || {};
        this.rows = d.data || d || [];
        this.lastPage = d.last_page || 1;
        this.total = d.total || (Array.isArray(this.rows) ? this.rows.length : 0);
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onRedemptionFilter(): void { this.currentPage = 1; this.loadRedemptions(); }

  approveRedemption(row: any): void {
    this.storeService.updateRedemptionStatus(row.uuid, 'aprobada').subscribe({
      next: () => this.loadRedemptions(),
      error: (err: any) => alert(err?.error?.message || 'Error'),
    });
  }

  rejectRedemption(row: any): void {
    this.storeService.updateRedemptionStatus(row.uuid, 'rechazada').subscribe({
      next: () => this.loadRedemptions(),
      error: (err: any) => alert(err?.error?.message || 'Error'),
    });
  }

  // ── Pagination ──
  prevPage(): void {
    if (this.currentPage > 1) {
      this.currentPage--;
      this.reloadCurrentSection();
    }
  }

  nextPage(): void {
    if (this.currentPage < this.lastPage) {
      this.currentPage++;
      this.reloadCurrentSection();
    }
  }

  private reloadCurrentSection(): void {
    if (this.activeSection === 'orders') this.loadOrders();
    else if (this.activeSection === 'products') this.loadProducts();
    else if (this.activeSection === 'shipping') this.loadShipments();
    else if (this.activeSection === 'customers') this.loadCustomers();
    else if (this.activeSection === 'points') this.loadPoints();
    else if (this.activeSection === 'coupons') this.loadCoupons();
    else if (this.activeSection === 'redemptions') this.loadRedemptions();
    else if (this.activeSection === 'checkout_payments') this.loadCheckoutPaymentMethodsConfig();
    else if (this.activeSection === 'boutique_dealerships') this.loadBoutiqueDealerships();
    else if (this.activeSection === 'openpay') this.loadOpenpayConfig();
  }

  // ── Products ──
  loadProducts(): void {
    this.productLoading = true;
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    const body: Record<string, unknown> = {
      page: this.productPage,
      per_page: this.productPageSize,
    };
    if (this.productSearch?.trim()) {
      body['search'] = this.productSearch.trim();
    }
    if (this.productCategoryFilter?.trim()) {
      body['category_uuid'] = this.productCategoryFilter.trim();
    }
    this.http.post(`${environment.baseUrl}/api/boutique/admin/products/search`, body, { headers }).subscribe({
      next: (res: any) => {
        const d = res?.data?.products || res?.data || {};
        const rows = Array.isArray(d) ? d : (d.data || []);
        const total = Number(Array.isArray(d) ? d.length : (d.total ?? 0)) || 0;
        const per = this.productPageSize;
        let last = Number(Array.isArray(d) ? 1 : (d.last_page ?? 0));
        if (!last || last < 1) {
          last = total > 0 ? Math.max(1, Math.ceil(total / per)) : 1;
        }
        if (rows.length === 0 && this.productPage > 1 && total > 0) {
          this.productPage = last;
          this.loadProducts();
          return;
        }
        this.productRows = rows;
        this.productTotal = total;
        this.productLastPage = last;
        this.productLoading = false;
      },
      error: () => { this.productRows = []; this.productLoading = false; },
    });
  }

  onProductSearch(): void { this.productPage = 1; this.loadProducts(); }

  onProductCategoryFilter(): void {
    this.productPage = 1;
    this.loadProducts();
  }

  onProductPageSizeChange(): void {
    this.productPage = 1;
    this.loadProducts();
  }
  prevProductPage(): void { if (this.productPage > 1) { this.productPage--; this.loadProducts(); } }
  nextProductPage(): void { if (this.productPage < this.productLastPage) { this.productPage++; this.loadProducts(); } }

  viewProductDetail(product: any): void {
    this.productSaveError = '';
    this.productSaveSuccess = '';
    // Load full product detail with attributes from backend
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    this.http.post(`${environment.baseUrl}/api/boutique/admin/products/detail`, { uuid: product.uuid }, { headers }).subscribe({
      next: (res: any) => {
        const p = res?.data?.product || res?.data || product;
        this.selectedProduct = { ...p };
        this.startEditProduct();
      },
      error: () => {
        this.selectedProduct = { ...product };
        this.startEditProduct();
      },
    });
  }

  backToProducts(): void { this.selectedProduct = null; this.productEditing = false; this.productMode = 'edit'; }

  createNewProduct(): void {
    this.selectedProduct = { images: [], variants: [] };
    this.productMode = 'create';
    this.productEditing = true;
    this.productEditData = { name: '', description: '', sku: '', price: 0, stock: 0, active: true, category_uuid: '' };
    this.editVariants = [];
    this.hasVariants = false;
    this.selectedProductAttributes = [];
    this.newAttributeName = '';
    this.newValueInputs = {};
    this.selectedAttrUuid = '';
    this.modifiedVariantUuids = new Set();
    this.productSaveError = '';
    this.productSaveSuccess = '';
    if (this.categorySelectorRows.length === 0) this.loadCategorySelectorsFromApi();
  }

  startEditProduct(): void {
    this.productEditing = true;
    this.productMode = 'edit';
    this.productEditData = {
      uuid: this.selectedProduct.uuid,
      name: this.selectedProduct.name || '',
      description: this.selectedProduct.description || '',
      sku: this.selectedProduct.sku || '',
      price: this.selectedProduct.price || 0,
      stock: this.selectedProduct.stock || 0,
      active: this.selectedProduct.active ?? true,
      category_uuid: this.selectedProduct.category?.uuid || '',
    };
    this.editVariants = (this.selectedProduct.variants || []).map((v: any) => ({
      ...v,
      description: v.description || this.buildVariantDescription(v),
    }));
    this.hasVariants = this.editVariants.length > 0;
    this.modifiedVariantUuids = new Set();
    this.productSaveError = '';
    this.productSaveSuccess = '';
    if (this.categorySelectorRows.length === 0) this.loadCategorySelectorsFromApi();

    // Load attributes and set selected from product
    this.loadAttributes();
    this.selectedProductAttributes = (this.selectedProduct.attributes || []).map((attr: any) => ({
      attribute_uuid: attr.uuid,
      attribute_name: attr.name,
      values: (attr.values || []).map((v: any) => ({ ...v, selected: true })),
    }));
    if (this.selectedProductAttributes.length > 0) {
      this.hasVariants = true;
    }
  }

  /** Etiqueta jerárquica para selects y tablas de categorías. */
  categoryTreeLabel(c: any): string {
    if (c?.parent?.name) {
      return `${c.parent.name} › ${c.name}`;
    }
    return c?.name || '—';
  }

  /** Opciones de categoría padre al editar (excluye la propia rama). */
  categoryParentSelectOptions(): { uuid: string; label: string }[] {
    const editing = this.categoryForm.uuid;
    const exclude = new Set<string>();
    if (editing) {
      exclude.add(editing);
      const addDescendants = (parentUuid: string) => {
        for (const row of this.categorySelectorRows) {
          const puuid = row.parent?.uuid || '';
          if (puuid === parentUuid) {
            exclude.add(row.uuid);
            addDescendants(row.uuid);
          }
        }
      };
      addDescendants(editing);
    }
    return this.categorySelectorRows
      .filter((c: any) => !exclude.has(c.uuid))
      .map((c: any) => ({ uuid: c.uuid, label: this.categoryTreeLabel(c) }));
  }

  /** Tabla paginada en la sección Categorías. */
  loadCategoryTableFromApi(): void {
    this.categorySectionLoading = true;
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    const body = { page: this.categoryPage, per_page: this.categoryPageSize };
    this.http.post(`${environment.baseUrl}/api/boutique/admin/categories/search`, body, { headers }).subscribe({
      next: (res: any) => {
        const d = res?.data?.categories || res?.data || {};
        const rows = d.data || [];
        const last = d.last_page || 1;
        const total = d.total || 0;
        if (rows.length === 0 && this.categoryPage > last && last >= 1) {
          this.categoryPage = last;
          this.loadCategoryTableFromApi();
          return;
        }
        this.categoryAdminRows = rows;
        this.categoryLastPage = last;
        this.categoryTotal = total;
        this.categorySectionLoading = false;
      },
      error: () => {
        this.categoryAdminRows = [];
        this.categorySectionLoading = false;
      },
    });
  }

  /** Opciones de categoría para filtro de productos y modal (hasta 500). */
  private loadCategorySelectorsFromApi(): void {
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    this.http
      .post(`${environment.baseUrl}/api/boutique/admin/categories/search`, { page: 1, per_page: 500 }, { headers })
      .subscribe({
        next: (res: any) => {
          const d = res?.data?.categories || res?.data || {};
          const list = d.data || [];
          this.categorySelectorRows = list;
          this.categoryOptions = list.map((c: any) => ({ uuid: c.uuid, name: this.categoryTreeLabel(c) }));
        },
        error: () => {
          this.categorySelectorRows = [];
          this.categoryOptions = [];
        },
      });
  }

  prevCategoryPage(): void {
    if (this.categoryPage > 1) {
      this.categoryPage--;
      this.loadCategoryTableFromApi();
    }
  }

  nextCategoryPage(): void {
    if (this.categoryPage < this.categoryLastPage) {
      this.categoryPage++;
      this.loadCategoryTableFromApi();
    }
  }

  openCategoryCreateModal(): void {
    this.categoryModalMode = 'create';
    this.categoryModalError = '';
    this.categoryForm = { uuid: '', name: '', description: '', active: true, parent_uuid: '' };
    this.categoryModalOpen = true;
  }

  openCategoryEditModal(row: any): void {
    this.categoryModalMode = 'edit';
    this.categoryModalError = '';
    this.categoryForm = {
      uuid: row.uuid,
      name: row.name || '',
      description: row.description || '',
      active: !!row.active,
      parent_uuid: row.parent?.uuid || '',
    };
    this.categoryModalOpen = true;
  }

  closeCategoryModal(): void {
    if (this.categoryModalSaving) {
      return;
    }
    this.categoryModalOpen = false;
  }

  submitCategoryModal(): void {
    const name = (this.categoryForm.name || '').trim();
    if (!name) {
      this.categoryModalError = 'El nombre es obligatorio';
      return;
    }
    this.categoryModalSaving = true;
    this.categoryModalError = '';
    const headers = this.getHeaders();
    const body: any = {
      name,
      description: (this.categoryForm.description || '').trim() || null,
      active: this.categoryForm.active,
    };
    if (this.categoryForm.parent_uuid) {
      body.parent_uuid = this.categoryForm.parent_uuid;
    } else if (this.categoryModalMode === 'edit') {
      body.parent_uuid = null;
    }
    if (this.categoryModalMode === 'edit') {
      body.uuid = this.categoryForm.uuid;
    }
    const url =
      this.categoryModalMode === 'create'
        ? `${environment.baseUrl}/api/boutique/admin/categories/store`
        : `${environment.baseUrl}/api/boutique/admin/categories/update`;
    this.http.post(url, body, { headers }).subscribe({
      next: () => {
        this.categoryModalSaving = false;
        this.categoryModalOpen = false;
        this.loadCategorySelectorsFromApi();
        this.loadCategoryTableFromApi();
      },
      error: (err: any) => {
        this.categoryModalSaving = false;
        this.categoryModalError = err?.error?.message || 'Error al guardar';
      },
    });
  }

  deleteCategoryRow(row: any): void {
    if (!confirm(`¿Eliminar la categoría «${row.name}»?`)) {
      return;
    }
    this.http
      .post(
        `${environment.baseUrl}/api/boutique/admin/categories/delete`,
        { uuid: row.uuid },
        { headers: this.getHeaders() }
      )
      .subscribe({
        next: () => {
          this.loadCategorySelectorsFromApi();
          this.loadCategoryTableFromApi();
        },
        error: (err: any) => {
          alert(err?.error?.message || 'No se pudo eliminar');
        },
      });
  }

  // ── Catálogo de atributos (tienda) ──
  catalogAdminAttributes: any[] = [];
  catalogAttributesLoading = false;
  catalogAttrMessage = '';
  catalogAttrError = '';
  /** Evita doble envío al crear atributo desde la sección Atributos. */
  catalogAttrCreating = false;
  attrCatalogModal = false;
  attrCatalogSaving = false;
  attrCatalogName = '';
  attrCatalogEditUuid = '';
  newCatalogAttrName = '';
  valueCatalogModal = false;
  valueCatalogSaving = false;
  valueCatalogAttrUuid = '';
  valueCatalogEditUuid = '';
  valueCatalogText = '';
  valueCatalogColor = '';
  valueCatalogSort = 0;

  loadCatalogAttributesList(): void {
    this.catalogAttributesLoading = true;
    this.catalogAttrMessage = '';
    this.catalogAttrError = '';
    const listUrl = `${environment.baseUrl}/api/boutique/admin/attributes/list`;
    this.storePanelDebug('loadCatalogAttributesList:start', {
      url: listUrl,
      hasToken: !!localStorage.getItem('user_token'),
    });
    this.http.post(listUrl, {}, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        this.catalogAdminAttributes = this.normalizeAttributesListResponse(res);
        this.catalogAttributesLoading = false;
        this.storePanelDebug('loadCatalogAttributesList:success', {
          count: this.catalogAdminAttributes.length,
          dataKeys: res?.data && typeof res.data === 'object' ? Object.keys(res.data) : [],
          sample: this.catalogAdminAttributes[0] ? { uuid: this.catalogAdminAttributes[0].uuid, name: this.catalogAdminAttributes[0].name } : null,
        });
      },
      error: (err: any) => {
        this.catalogAttrError = err?.error?.message || 'Error al cargar atributos';
        this.catalogAdminAttributes = [];
        this.catalogAttributesLoading = false;
        this.storePanelDebug('loadCatalogAttributesList:error', {
          status: err?.status,
          statusText: err?.statusText,
          body: err?.error,
        });
      },
    });
  }

  openAttrCatalogEdit(attr: any): void {
    this.attrCatalogEditUuid = attr.uuid;
    this.attrCatalogName = attr.name || '';
    this.attrCatalogModal = true;
  }

  closeAttrCatalogModal(): void {
    if (this.attrCatalogSaving) {
      return;
    }
    this.attrCatalogModal = false;
  }

  submitAttrCatalogModal(): void {
    const name = (this.attrCatalogName || '').trim();
    if (!name) {
      return;
    }
    this.attrCatalogSaving = true;
    const headers = this.getHeaders();
    const req = this.http.post(`${environment.baseUrl}/api/boutique/admin/attributes/update`, { uuid: this.attrCatalogEditUuid, name }, { headers });
    req.subscribe({
      next: () => {
        this.attrCatalogSaving = false;
        this.attrCatalogModal = false;
        this.catalogAttrMessage = 'Atributo guardado';
        this.loadCatalogAttributesList();
        this.loadAttributes();
        setTimeout(() => (this.catalogAttrMessage = ''), 3000);
      },
      error: (err: any) => {
        this.attrCatalogSaving = false;
        this.catalogAttrError = err?.error?.message || 'Error al guardar';
        setTimeout(() => (this.catalogAttrError = ''), 4000);
      },
    });
  }

  deleteAttrCatalog(attr: any): void {
    if (!confirm(`¿Eliminar el atributo «${attr.name}»? No debe estar asignado a productos.`)) {
      return;
    }
    this.http.post(`${environment.baseUrl}/api/boutique/admin/attributes/delete`, { uuid: attr.uuid }, { headers: this.getHeaders() }).subscribe({
      next: () => {
        this.catalogAttrMessage = 'Atributo eliminado';
        this.loadCatalogAttributesList();
        this.loadAttributes();
        setTimeout(() => (this.catalogAttrMessage = ''), 3000);
      },
      error: (err: any) => {
        this.catalogAttrError = err?.error?.message || 'Error al eliminar';
        setTimeout(() => (this.catalogAttrError = ''), 4000);
      },
    });
  }

  openValueCatalogCreate(attributeUuid: string): void {
    this.valueCatalogModal = true;
    this.valueCatalogAttrUuid = attributeUuid;
    this.valueCatalogEditUuid = '';
    this.valueCatalogText = '';
    this.valueCatalogColor = '';
    this.valueCatalogSort = 0;
  }

  openValueCatalogEdit(val: any, attributeUuid: string): void {
    this.valueCatalogModal = true;
    this.valueCatalogAttrUuid = attributeUuid;
    this.valueCatalogEditUuid = val.uuid;
    this.valueCatalogText = val.value || '';
    this.valueCatalogColor = val.color_hex || '';
    this.valueCatalogSort = val.sort_order ?? 0;
  }

  closeValueCatalogModal(): void {
    if (this.valueCatalogSaving) {
      return;
    }
    this.valueCatalogModal = false;
  }

  submitValueCatalogModal(): void {
    const v = (this.valueCatalogText || '').trim();
    if (!v) {
      return;
    }
    this.valueCatalogSaving = true;
    const headers = this.getHeaders();
    const base = `${environment.baseUrl}/api/boutique/admin/attribute-values`;
    const req = this.valueCatalogEditUuid
      ? this.http.post(`${base}/update`, {
          uuid: this.valueCatalogEditUuid,
          value: v,
          color_hex: this.valueCatalogColor.trim() || null,
          sort_order: Number(this.valueCatalogSort) || 0,
        }, { headers })
      : this.http.post(`${base}/store`, {
          attribute_uuid: this.valueCatalogAttrUuid,
          value: v,
          color_hex: this.valueCatalogColor.trim() || null,
          sort_order: Number(this.valueCatalogSort) || 0,
        }, { headers });
    req.subscribe({
      next: () => {
        this.valueCatalogSaving = false;
        this.valueCatalogModal = false;
        this.catalogAttrMessage = 'Valor guardado';
        this.loadCatalogAttributesList();
        this.loadAttributes();
        setTimeout(() => (this.catalogAttrMessage = ''), 3000);
      },
      error: (err: any) => {
        this.valueCatalogSaving = false;
        this.catalogAttrError = err?.error?.message || 'Error al guardar valor';
        setTimeout(() => (this.catalogAttrError = ''), 4000);
      },
    });
  }

  deleteValueCatalog(val: any): void {
    if (!confirm(`¿Eliminar el valor «${val.value}»?`)) {
      return;
    }
    this.http.post(`${environment.baseUrl}/api/boutique/admin/attribute-values/delete`, { uuid: val.uuid }, { headers: this.getHeaders() }).subscribe({
      next: () => {
        this.catalogAttrMessage = 'Valor eliminado';
        this.loadCatalogAttributesList();
        this.loadAttributes();
        setTimeout(() => (this.catalogAttrMessage = ''), 3000);
      },
      error: (err: any) => {
        this.catalogAttrError = err?.error?.message || 'Error al eliminar';
        setTimeout(() => (this.catalogAttrError = ''), 4000);
      },
    });
  }

  createCatalogAttributeInline(): void {
    const rawInput = this.newCatalogAttrName;
    const name = (rawInput || '').trim();
    this.storePanelDebug('createCatalogAttributeInline:enter', {
      rawInputLength: rawInput == null ? null : String(rawInput).length,
      trimmedName: name,
      catalogAttrCreating: this.catalogAttrCreating,
      activeSection: this.activeSection,
    });
    if (!name) {
      this.storePanelDebug('createCatalogAttributeInline:abort-empty-name');
      this.catalogAttrError = 'Escribe un nombre para el atributo';
      setTimeout(() => (this.catalogAttrError = ''), 4000);
      return;
    }
    if (this.catalogAttrCreating) {
      this.storePanelDebug('createCatalogAttributeInline:abort-already-creating');
      return;
    }
    this.catalogAttrCreating = true;
    this.catalogAttrError = '';
    const storeUrl = `${environment.baseUrl}/api/boutique/admin/attributes/store`;
    this.storePanelDebug('createCatalogAttributeInline:request', { url: storeUrl, body: { name } });
    this.http.post(storeUrl, { name }, { headers: this.getHeaders() }).subscribe({
      next: (res: unknown) => {
        this.storePanelDebug('createCatalogAttributeInline:success', res);
        this.catalogAttrCreating = false;
        this.newCatalogAttrName = '';
        this.catalogAttrMessage = 'Atributo creado';
        this.loadCatalogAttributesList();
        this.loadAttributes();
        setTimeout(() => (this.catalogAttrMessage = ''), 3000);
      },
      error: (err: any) => {
        this.storePanelDebug('createCatalogAttributeInline:error', {
          status: err?.status,
          statusText: err?.statusText,
          url: err?.url,
          errorBody: err?.error,
          formatted: this.formatBoutiqueAdminHttpError(err),
        });
        this.catalogAttrCreating = false;
        this.catalogAttrError = this.formatBoutiqueAdminHttpError(err);
        setTimeout(() => (this.catalogAttrError = ''), 8000);
      },
    });
  }

  /** Al activar «Producto con variantes», carga el catálogo (el evento native `change` corre antes que ngModel). */
  onProductVariantsToggle(enabled: boolean): void {
    this.storePanelDebug('onProductVariantsToggle', { enabled });
    if (enabled) {
      this.loadAttributes();
    }
  }

  /**
   * Logs de depuración del panel tienda. En DevTools → Consola filtra por `StorePanel`.
   * Quitar o reducir cuando ya no haga falta diagnosticar.
   */
  private storePanelDebug(phase: string, detail?: unknown): void {
    try {
      console.log(`[StorePanel] ${phase}`, detail === undefined ? '' : detail);
    } catch {
      /* noop */
    }
  }

  /** Lista de atributos desde distintas formas de respuesta de la API. */
  private normalizeAttributesListResponse(res: any): any[] {
    const d = res?.data;
    if (!d) {
      return [];
    }
    if (Array.isArray(d.attributes)) {
      return d.attributes;
    }
    if (Array.isArray(d)) {
      return d;
    }
    if (d.attributes && typeof d.attributes === 'object' && !Array.isArray(d.attributes)) {
      return Object.values(d.attributes);
    }
    return [];
  }

  private formatBoutiqueAdminHttpError(err: any): string {
    const e = err?.error;
    if (!e) {
      return err?.message || 'Error de red o del servidor';
    }
    if (e.errors && typeof e.errors === 'object') {
      const parts: string[] = [];
      for (const v of Object.values(e.errors) as unknown[]) {
        if (Array.isArray(v)) {
          parts.push(...v.map(String));
        }
      }
      if (parts.length) {
        return parts.join(' ');
      }
    }
    return e.message || 'Error al procesar la solicitud';
  }

  cancelEditProduct(): void { this.productEditing = false; }

  saveProduct(): void {
    this.productSaving = true;
    this.productSaveError = '';
    this.productSaveSuccess = '';
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    const endpoint = this.productMode === 'create' ? 'products/store' : 'products/update';
    this.http.post(`${environment.baseUrl}/api/boutique/admin/${endpoint}`, this.productEditData, { headers }).subscribe({
      next: (res: any) => {
        this.productSaving = false;
        this.productSaveSuccess = this.productMode === 'create' ? 'Producto creado correctamente' : 'Producto actualizado correctamente';
        const saved = res?.data?.product || res?.data || this.productEditData;
        if (this.productMode === 'create') {
          this.selectedProduct = saved;
          this.productMode = 'edit';
        } else {
          Object.assign(this.selectedProduct, saved);
        }
        this.productEditing = false;
        this.loadProducts();
      },
      error: (err: any) => {
        this.productSaving = false;
        this.productSaveError = err?.error?.message || 'Error al guardar producto';
      },
    });
  }

  openProductImageAi(img: { uuid?: string; image_path?: string }): void {
    const uuid = String(img?.uuid ?? '').trim();
    const sourceUrl = String(img?.image_path ?? '').trim();
    if (!uuid || !sourceUrl) {
      this.productSaveError = 'La imagen debe estar subida antes de usar IA.';
      return;
    }
    const ref = this.dialog.open(ImageAiDialogComponent, {
      width: '640px',
      maxWidth: '95vw',
      data: {
        sourceUrl,
        targetType: 'boutique_product_image',
        targetUuid: uuid,
        title: 'Mejorar foto del producto',
      },
    });
    ref.afterClosed().subscribe((result) => {
      if (result?.saved && result.imageUrl && this.selectedProduct?.images) {
        const idx = this.selectedProduct.images.findIndex((i: { uuid?: string }) => i.uuid === uuid);
        if (idx >= 0) {
          this.selectedProduct.images[idx].image_path = result.imageUrl;
        }
        this.productSaveSuccess = 'Imagen del producto actualizada con IA';
        setTimeout(() => (this.productSaveSuccess = ''), 4000);
      }
    });
  }

  uploadProductImage(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length || !this.selectedProduct?.uuid) return;
    const file = input.files[0];
    const token = localStorage.getItem('user_token') || '';
    const fd = new FormData();
    fd.append('product_uuid', this.selectedProduct.uuid);
    fd.append('image', file);
    const headers = new HttpHeaders({ 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    this.http.post(`${environment.baseUrl}/api/boutique/admin/product_images/store`, fd, { headers }).subscribe({
      next: () => { this.productSaveSuccess = 'Imagen subida correctamente'; input.value = ''; },
      error: (err: any) => { this.productSaveError = err?.error?.message || 'Error al subir imagen'; },
    });
  }

  deleteProductImage(img: any): void {
    if (!confirm('¿Eliminar esta imagen?')) return;
    const token = localStorage.getItem('user_token') || '';
    const headers = new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
    this.http.post(`${environment.baseUrl}/api/boutique/admin/product_images/delete`, { uuid: img.uuid }, { headers }).subscribe({
      next: () => {
        this.selectedProduct.images = (this.selectedProduct.images || []).filter((i: any) => i.uuid !== img.uuid);
        this.productSaveSuccess = 'Imagen eliminada';
      },
      error: (err: any) => { this.productSaveError = err?.error?.message || 'Error al eliminar imagen'; },
    });
  }

  addVariant(): void {
    this.editVariants.push({ color: '', color_hex: '#000000', size: '', sku: '', stock: 0, active: true });
  }

  removeVariant(index: number): void {
    this.editVariants.splice(index, 1);
    if (this.editVariants.length === 0) this.hasVariants = false;
  }

  // ── Attributes & Variants ──
  private getHeaders(): HttpHeaders {
    const token = localStorage.getItem('user_token') || '';
    return new HttpHeaders({ 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', Authorization: `Bearer ${token}` });
  }

  loadAttributes(): void {
    const listUrl = `${environment.baseUrl}/api/boutique/admin/attributes/list`;
    this.storePanelDebug('loadAttributes:start', { url: listUrl, hasToken: !!localStorage.getItem('user_token') });
    this.http.post(listUrl, {}, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        this.availableAttributes = this.normalizeAttributesListResponse(res);
        this.storePanelDebug('loadAttributes:success', {
          count: this.availableAttributes.length,
          dataKeys: res?.data && typeof res.data === 'object' ? Object.keys(res.data) : [],
        });
      },
      error: (err: any) => {
        this.availableAttributes = [];
        this.storePanelDebug('loadAttributes:error', { status: err?.status, body: err?.error });
      },
    });
  }

  get filteredAttributes(): any[] {
    const selectedUuids = this.selectedProductAttributes.map(a => a.attribute_uuid);
    return this.availableAttributes.filter(a => !selectedUuids.includes(a.uuid));
  }

  addAttributeToProduct(attrUuid: string): void {
    if (!attrUuid) return;
    const attr = this.availableAttributes.find(a => a.uuid === attrUuid);
    if (!attr) return;
    const alreadyAdded = this.selectedProductAttributes.some(a => a.attribute_uuid === attrUuid);
    if (alreadyAdded) return;
    this.selectedProductAttributes.push({
      attribute_uuid: attr.uuid,
      attribute_name: attr.name,
      values: (attr.values || []).map((v: any) => ({ ...v, selected: false })),
    });
    this.selectedAttrUuid = '';
  }

  removeAttributeFromProduct(index: number): void {
    this.selectedProductAttributes.splice(index, 1);
  }

  toggleAttributeValue(attrIndex: number, valueUuid: string): void {
    const attr = this.selectedProductAttributes[attrIndex];
    if (!attr) return;
    const val = attr.values.find((v: any) => v.uuid === valueUuid);
    if (val) val.selected = !val.selected;
  }

  createAttributeInline(): void {
    const name = (this.newAttributeName || '').trim();
    this.storePanelDebug('createAttributeInline:enter', { nameLength: name.length, hasVariants: this.hasVariants });
    if (!name) {
      this.storePanelDebug('createAttributeInline:abort-empty-name');
      this.productSaveError = 'Escribe un nombre para el nuevo atributo';
      return;
    }
    const storeUrl = `${environment.baseUrl}/api/boutique/admin/attributes/store`;
    this.storePanelDebug('createAttributeInline:request', { url: storeUrl, body: { name } });
    this.http.post(storeUrl, { name }, { headers: this.getHeaders() }).subscribe({
      next: (res: unknown) => {
        this.storePanelDebug('createAttributeInline:success', res);
        this.newAttributeName = '';
        this.productSaveError = '';
        this.loadAttributes();
      },
      error: (err: any) => {
        this.storePanelDebug('createAttributeInline:error', {
          status: err?.status,
          errorBody: err?.error,
          formatted: this.formatBoutiqueAdminHttpError(err),
        });
        this.productSaveError = this.formatBoutiqueAdminHttpError(err);
      },
    });
  }

  createValueInline(attrIndex: number): void {
    const attr = this.selectedProductAttributes[attrIndex];
    if (!attr) return;
    const newVal = (this.newValueInputs[attr.attribute_uuid] || '').trim();
    if (!newVal) return;
    this.http.post(`${environment.baseUrl}/api/boutique/admin/attribute-values/store`, {
      attribute_uuid: attr.attribute_uuid,
      value: newVal,
    }, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        const created = res?.data?.value || res?.data;
        if (created) {
          attr.values.push({ ...created, selected: true });
        }
        this.newValueInputs[attr.attribute_uuid] = '';
        this.loadAttributes();
      },
      error: (err: any) => {
        this.productSaveError = err?.error?.message || 'Error al crear valor';
      },
    });
  }

  get canGenerateVariants(): boolean {
    return this.selectedProductAttributes.some(a => a.values.some((v: any) => v.selected));
  }

  get totalVariantsCount(): number {
    return this.editVariants.length;
  }

  get totalStockCount(): number {
    return this.editVariants.filter((v: any) => v.active).reduce((sum: number, v: any) => sum + (Number(v.stock) || 0), 0);
  }

  generateVariants(): void {
    if (!this.selectedProduct?.uuid) {
      this.productSaveError = 'Guarda el producto primero antes de generar variantes';
      return;
    }
    this.generatingVariants = true;
    this.productSaveError = '';
    const attributes = this.selectedProductAttributes
      .filter(a => a.values.some((v: any) => v.selected))
      .map(a => ({
        attribute_uuid: a.attribute_uuid,
        value_uuids: a.values.filter((v: any) => v.selected).map((v: any) => v.uuid),
      }));
    this.http.post(`${environment.baseUrl}/api/boutique/admin/products/generate_variants`, {
      product_uuid: this.selectedProduct.uuid,
      attributes,
    }, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        const variants = res?.data?.variants || res?.data || [];
        this.editVariants = variants.map((v: any) => ({
          ...v,
          description: v.description || this.buildVariantDescription(v),
        }));
        this.modifiedVariantUuids = new Set();
        this.generatingVariants = false;
        this.productSaveSuccess = `${this.editVariants.length} variantes generadas`;
      },
      error: (err: any) => {
        this.generatingVariants = false;
        this.productSaveError = err?.error?.message || 'Error al generar variantes';
      },
    });
  }

  buildVariantDescription(v: any): string {
    if (v.attribute_values?.length) {
      return v.attribute_values.map((av: any) => av.value || av.attribute_value_name).join(' / ');
    }
    const parts: string[] = [];
    if (v.color) parts.push(v.color);
    if (v.size) parts.push(v.size);
    return parts.join(' / ') || '—';
  }

  markVariantModified(uuid: string): void {
    if (uuid) this.modifiedVariantUuids.add(uuid);
  }

  saveVariantChanges(): void {
    const toSave = this.editVariants.filter((v: any) => this.modifiedVariantUuids.has(v.uuid));
    if (toSave.length === 0) return;
    this.variantsSaving = true;
    this.productSaveError = '';
    let completed = 0;
    let errors = 0;
    toSave.forEach((v: any) => {
      this.http.post(`${environment.baseUrl}/api/boutique/admin/products/update_variant`, {
        variant_uuid: v.uuid,
        sku: v.sku,
        price: v.price,
        stock: v.stock,
        active: v.active,
      }, { headers: this.getHeaders() }).subscribe({
        next: () => {
          completed++;
          if (completed + errors === toSave.length) {
            this.variantsSaving = false;
            this.modifiedVariantUuids.clear();
            this.productSaveSuccess = `${completed} variante(s) guardada(s)`;
          }
        },
        error: (err: any) => {
          errors++;
          this.productSaveError = err?.error?.message || 'Error al guardar variante';
          if (completed + errors === toSave.length) {
            this.variantsSaving = false;
          }
        },
      });
    });
  }

  deleteVariantItem(variantUuid: string): void {
    if (!confirm('¿Eliminar esta variante?')) return;
    this.http.post(`${environment.baseUrl}/api/boutique/admin/products/delete_variant`, {
      variant_uuid: variantUuid,
    }, { headers: this.getHeaders() }).subscribe({
      next: () => {
        this.editVariants = this.editVariants.filter((v: any) => v.uuid !== variantUuid);
        this.modifiedVariantUuids.delete(variantUuid);
        this.productSaveSuccess = 'Variante eliminada';
      },
      error: (err: any) => {
        this.productSaveError = err?.error?.message || 'Error al eliminar variante';
      },
    });
  }

  // ── Helpers ──
  formatCurrency(val: any): string {
    if (val === null || val === undefined) return '$0';
    return '$' + Number(val).toLocaleString();
  }

  formatDate(val: any): string {
    if (!val) return '—';
    return new Date(val).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' });
  }

  loadBoutiqueDealerships(): void {
    this.boutiqueDealershipLoading = true;
    this.boutiqueDealershipError = '';
    this.boutiqueDealershipSuccess = '';
    this.storeService.listBoutiqueDealerships().subscribe({
      next: (res: any) => {
        const rows = res?.data?.dealerships || [];
        this.boutiqueDealershipRows = (Array.isArray(rows) ? rows : []).map((d: any) => ({
          id: d.id,
          name: d.name || '—',
          location: d.location || '',
          state: d.state,
          whatsapp_phone: d.whatsapp_phone || '',
          editPhone: d.whatsapp_phone || '',
        }));
        this.boutiqueDealershipLoading = false;
      },
      error: (err: any) => {
        this.boutiqueDealershipError = err?.error?.message || 'Error al cargar sucursales';
        this.boutiqueDealershipRows = [];
        this.boutiqueDealershipLoading = false;
      },
    });
  }

  saveBoutiqueDealershipWhatsapp(row: { id: number; editPhone: string }): void {
    this.boutiqueDealershipSavingId = row.id;
    this.boutiqueDealershipError = '';
    this.boutiqueDealershipSuccess = '';
    this.storeService.updateBoutiqueDealershipWhatsapp(row.id, row.editPhone.trim()).subscribe({
      next: (res: any) => {
        const updated = res?.data?.dealership;
        if (updated) {
          const idx = this.boutiqueDealershipRows.findIndex(r => r.id === row.id);
          if (idx >= 0) {
            this.boutiqueDealershipRows[idx].whatsapp_phone = updated.whatsapp_phone || '';
            this.boutiqueDealershipRows[idx].editPhone = updated.whatsapp_phone || '';
          }
        }
        this.boutiqueDealershipSavingId = null;
        this.boutiqueDealershipSuccess = 'WhatsApp actualizado';
        setTimeout(() => (this.boutiqueDealershipSuccess = ''), 4000);
      },
      error: (err: any) => {
        this.boutiqueDealershipSavingId = null;
        this.boutiqueDealershipError = err?.error?.message || 'Error al guardar WhatsApp';
      },
    });
  }

  loadCheckoutPaymentMethodsConfig(): void {
    this.checkoutPayLoading = true;
    this.checkoutPayError = '';
    this.checkoutPaySuccess = '';
    this.storeService.getCheckoutPaymentMethodsConfig().subscribe({
      next: (res: any) => {
        const d = res?.data || {};
        const m = d.methods || {};
        this.checkoutPayShowOpenpay = !!m.openpay;
        this.checkoutPayShowTransferencia = !!m.transferencia;
        this.checkoutPayShowSucursal = !!m.sucursal;
        const admin = d.admin;
        if (admin?.flags && admin?.keys_configured) {
          this.checkoutPayKeysOpenpay = !!admin.keys_configured.openpay;
          const f = admin.flags;
          this.checkoutPayFlagOpenpay = !!f.boutique_checkout_openpay;
          this.checkoutPayTransferencia = !!f.boutique_checkout_transferencia;
          this.checkoutPaySucursal = !!f.boutique_checkout_sucursal;
        } else {
          this.checkoutPayKeysOpenpay = !!d.openpay?.available || !!m.openpay;
          this.checkoutPayFlagOpenpay = !!m.openpay;
          this.checkoutPayTransferencia = !!m.transferencia;
          this.checkoutPaySucursal = !!m.sucursal;
        }
        const tb = d.transfer_bank;
        if (tb) {
          this.transferBankName = tb.bank_name || '';
          this.transferAccountHolder = tb.account_holder || '';
          this.transferClabe = tb.clabe || '';
          this.transferAccountNumber = tb.account_number || '';
          this.transferInstructions = tb.instructions || '';
        }
        const inputs = d.legal_pages_inputs as Record<string, string> | undefined;
        if (inputs && typeof inputs === 'object') {
          this.checkoutLegalFormTermsUrl = String(inputs.boutique_checkout_legal_terms_url ?? '');
          this.checkoutLegalFormPrivacyUrl = String(inputs.boutique_checkout_legal_privacy_url ?? '');
          this.checkoutLegalFormReturnsUrl = String(inputs.boutique_checkout_legal_returns_url ?? '');
        }
        const lp = d.legal_pages as { terms_url?: string; privacy_url?: string; returns_url?: string } | undefined;
        if (lp && typeof lp === 'object') {
          this.checkoutLegalEffectiveTerms = String(lp.terms_url ?? '');
          this.checkoutLegalEffectivePrivacy = String(lp.privacy_url ?? '');
          this.checkoutLegalEffectiveReturns = String(lp.returns_url ?? '');
        }
        this.checkoutPayLoading = false;
      },
      error: (err: any) => {
        this.checkoutPayError = err?.error?.message || 'Error al cargar métodos de pago';
        this.checkoutPayLoading = false;
      },
    });
  }

  toggleCheckoutPayFlagOpenpay(): void {
    this.checkoutPayFlagOpenpay = !this.checkoutPayFlagOpenpay;
  }

  toggleCheckoutPayTransferencia(): void {
    this.checkoutPayTransferencia = !this.checkoutPayTransferencia;
  }

  toggleCheckoutPaySucursal(): void {
    this.checkoutPaySucursal = !this.checkoutPaySucursal;
  }

  saveTransferBankDetails(): void {
    this.transferBankSaving = true;
    this.transferBankError = '';
    this.transferBankSuccess = '';
    this.storeService
      .updateTransferBankDetails({
        boutique_transfer_bank_name: this.transferBankName.trim(),
        boutique_transfer_account_holder: this.transferAccountHolder.trim(),
        boutique_transfer_clabe: this.transferClabe.trim(),
        boutique_transfer_account_number: this.transferAccountNumber.trim(),
        boutique_transfer_instructions: this.transferInstructions.trim(),
      })
      .subscribe({
        next: () => {
          this.transferBankSuccess = 'Datos bancarios guardados';
          this.transferBankSaving = false;
          this.loadCheckoutPaymentMethodsConfig();
          setTimeout(() => (this.transferBankSuccess = ''), 4000);
        },
        error: (err: any) => {
          this.transferBankError = err?.error?.message || 'Error al guardar datos bancarios';
          this.transferBankSaving = false;
        },
      });
  }

  saveCheckoutLegalPages(): void {
    this.checkoutLegalPagesSaving = true;
    this.checkoutLegalPagesError = '';
    this.checkoutLegalPagesSuccess = '';
    this.storeService
      .updateCheckoutLegalPages({
        boutique_checkout_legal_terms_url: this.checkoutLegalFormTermsUrl.trim(),
        boutique_checkout_legal_privacy_url: this.checkoutLegalFormPrivacyUrl.trim(),
        boutique_checkout_legal_returns_url: this.checkoutLegalFormReturnsUrl.trim(),
      })
      .subscribe({
        next: () => {
          this.checkoutLegalPagesSuccess = 'Enlaces legales del checkout guardados';
          this.checkoutLegalPagesSaving = false;
          this.loadCheckoutPaymentMethodsConfig();
          setTimeout(() => (this.checkoutLegalPagesSuccess = ''), 4000);
        },
        error: (err: any) => {
          const e = err?.error;
          const msg =
            e?.error_code === 'INVALID_LEGAL_PAGE_URL'
              ? String(e?.message || 'URL o ruta no válida. Usa una ruta que empiece con / o una URL https completa.')
              : String(e?.message || 'Error al guardar enlaces legales');
          this.checkoutLegalPagesError = msg;
          this.checkoutLegalPagesSaving = false;
          this.loadCheckoutPaymentMethodsConfig();
        },
      });
  }

  saveCheckoutPaymentMethods(): void {
    this.checkoutPaySaving = true;
    this.checkoutPayError = '';
    this.checkoutPaySuccess = '';
    this.storeService
      .updateCheckoutPaymentMethods({
        boutique_checkout_openpay: this.checkoutPayFlagOpenpay,
        boutique_checkout_transferencia: this.checkoutPayTransferencia,
        boutique_checkout_sucursal: this.checkoutPaySucursal,
      })
      .subscribe({
        next: () => {
          this.checkoutPaySuccess = 'Preferencias guardadas';
          this.checkoutPaySaving = false;
          this.loadCheckoutPaymentMethodsConfig();
          setTimeout(() => (this.checkoutPaySuccess = ''), 4000);
        },
        error: (err: any) => {
          const e = err?.error;
          this.checkoutPayError =
            (e?.errors
              ? ([] as string[]).concat(...(Object.values(e.errors) as string[][])).join(' ')
              : '') || e?.message || 'Error al guardar';
          this.checkoutPaySaving = false;
          this.loadCheckoutPaymentMethodsConfig();
        },
      });
  }

  loadOpenpayConfig(): void {
    this.openpayLoading = true;
    this.openpayError = '';
    this.openpaySuccess = '';
    this.storeService.getOpenpayConfig().subscribe({
      next: (res: any) => {
        const d = res?.data || {};
        this.openpayConfig = { ...d };
        this.openpayPasarelaEnabled = d.boutique_checkout_openpay !== false;
        this.openpayKeysConfigured = !!d.keys_configured;
        this.openpayLoading = false;
      },
      error: (err: any) => {
        this.openpayError = err?.error?.message || 'Error al cargar OpenPay';
        this.openpayLoading = false;
      },
    });
  }

  toggleOpenpayMode(): void {
    this.openpayConfig['openpay_mode'] =
      this.openpayConfig['openpay_mode'] === 'production' ? 'sandbox' : 'production';
  }

  toggleOpenpayPasarela(): void {
    this.openpayPasarelaEnabled = !this.openpayPasarelaEnabled;
  }

  saveOpenpayConfig(): void {
    this.openpaySaving = true;
    this.openpayError = '';
    this.openpaySuccess = '';
    const payload: Record<string, unknown> = {
      openpay_mode: this.openpayConfig['openpay_mode'] || 'sandbox',
      boutique_checkout_openpay: this.openpayPasarelaEnabled,
    };
    const fields = [
      'openpay_sandbox_merchant_id',
      'openpay_sandbox_public_key',
      'openpay_sandbox_private_key',
      'openpay_production_merchant_id',
      'openpay_production_public_key',
      'openpay_production_private_key',
      'openpay_webhook_user',
      'openpay_webhook_password',
    ];
    for (const f of fields) {
      const v = this.openpayConfig[f];
      if (v === undefined || v === null || String(v).trim() === '') {
        continue;
      }
      if (String(v).startsWith('••••••••')) {
        continue;
      }
      payload[f] = v;
    }
    this.storeService.updateOpenpayConfig(payload).subscribe({
      next: () => {
        this.openpaySuccess = 'Configuración guardada';
        this.openpaySaving = false;
        this.loadOpenpayConfig();
      },
      error: (err: any) => {
        const e = err?.error;
        this.openpayError =
          (e?.errors
            ? ([] as string[]).concat(...(Object.values(e.errors) as string[][])).join(' ')
            : '') || e?.message || 'Error al guardar';
        this.openpaySaving = false;
      },
    });
  }

  // ── Incadea Sync ──
  incadeaSyncing = false;
  incadeaResult: any = null;
  incadeaLogs: any[] = [];
  incadeaLogsLoading = false;
  incadeaError = '';
  incadeaConfig: any = { excluded_brands: [], excluded_categories: [] };
  incadeaConfigLoading = false;
  incadeaConfigSaving = false;
  incadeaConfigSuccess = '';
  incadeaConfigError = '';
  incadeaNewBrand = '';
  incadeaNewCategory = '';

  loadIncadea(): void {
    this.loadIncadeaLogs();
    this.loadIncadeaConfig();
  }

  triggerIncadeaSync(): void {
    this.incadeaSyncing = true;
    this.incadeaError = '';
    this.incadeaResult = null;
    this.http.post(`${environment.baseUrl}/api/boutique/admin/incadea/sync`, {
      excluded_brands: this.incadeaConfig.excluded_brands,
      excluded_categories: this.incadeaConfig.excluded_categories,
    }, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        this.incadeaResult = res.data;
        this.incadeaSyncing = false;
        this.loadIncadeaLogs();
      },
      error: (err: any) => {
        this.incadeaError = err?.error?.message || 'Error al sincronizar';
        this.incadeaSyncing = false;
      },
    });
  }

  loadIncadeaLogs(): void {
    this.incadeaLogsLoading = true;
    this.http.post(`${environment.baseUrl}/api/boutique/admin/incadea/logs`, {}, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        const paginated = res.data?.logs || {};
        this.incadeaLogs = paginated.data || paginated || [];
        this.incadeaLogsLoading = false;
      },
      error: () => { this.incadeaLogsLoading = false; },
    });
  }

  loadIncadeaConfig(): void {
    this.incadeaConfigLoading = true;
    this.http.post(`${environment.baseUrl}/api/boutique/admin/incadea/config`, {}, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => {
        const cfg = res.data?.config || {};
        this.incadeaConfig = {
          excluded_brands: cfg.excluded_brands || [],
          excluded_categories: cfg.excluded_categories || [],
        };
        this.incadeaConfigLoading = false;
      },
      error: () => { this.incadeaConfigLoading = false; },
    });
  }

  saveIncadeaConfig(): void {
    this.incadeaConfigSaving = true;
    this.incadeaConfigSuccess = '';
    this.incadeaConfigError = '';
    this.http.post(`${environment.baseUrl}/api/boutique/admin/incadea/update_config`, this.incadeaConfig, { headers: this.getHeaders() }).subscribe({
      next: () => {
        this.incadeaConfigSuccess = 'Configuración guardada';
        this.incadeaConfigSaving = false;
        setTimeout(() => { this.incadeaConfigSuccess = ''; }, 3000);
      },
      error: (err: any) => {
        this.incadeaConfigError = err?.error?.message || 'Error al guardar';
        this.incadeaConfigSaving = false;
      },
    });
  }

  addIncadeaBrand(): void {
    const val = this.incadeaNewBrand.trim();
    if (val && this.incadeaConfig.excluded_brands.indexOf(val) === -1) {
      this.incadeaConfig.excluded_brands = ([] as string[]).concat(this.incadeaConfig.excluded_brands, [val]);
      this.incadeaNewBrand = '';
    }
  }

  removeIncadeaBrand(brand: string): void {
    this.incadeaConfig.excluded_brands = this.incadeaConfig.excluded_brands.filter((b: string) => b !== brand);
  }

  addIncadeaCategory(): void {
    const val = this.incadeaNewCategory.trim();
    if (val && this.incadeaConfig.excluded_categories.indexOf(val) === -1) {
      this.incadeaConfig.excluded_categories = ([] as string[]).concat(this.incadeaConfig.excluded_categories, [val]);
      this.incadeaNewCategory = '';
    }
  }

  removeIncadeaCategory(cat: string): void {
    this.incadeaConfig.excluded_categories = this.incadeaConfig.excluded_categories.filter((c: string) => c !== cat);
  }

  getIncadeaStatusClass(status: string): string {
    switch (status) {
      case 'completed': return 'badge-success';
      case 'failed': return 'badge-danger';
      case 'running': return 'badge-warning';
      default: return '';
    }
  }

  getIncadeaStatusLabel(status: string): string {
    switch (status) {
      case 'completed': return 'Completado';
      case 'failed': return 'Fallido';
      case 'running': return 'En progreso';
      default: return status;
    }
  }

  // ── WC Import ──
  wcImporting = false;
  wcSyncingImages = false;
  wcResult: any = null;
  wcImageResult: any = null;
  wcError = '';
  wcImageError = '';
  wcMode: 'full' | 'images' = 'full';

  /** Sincronizar catálogo Color/Talla desde variantes (POST wc-import/sync-variant-attributes). */
  wcAttrSyncing = false;
  wcAttrError = '';
  wcAttrResult: any = null;

  // Cleanup
  wcCleaning = false;
  wcCleanResult: any = null;
  wcCleanError = '';

  onWcFileSelected(event: Event, mode: 'full' | 'images'): void {
    const input = event.target as HTMLInputElement;
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const formData = new FormData();
    formData.append('csv', file);

    const headers = new HttpHeaders({
      'Authorization': `Bearer ${localStorage.getItem('user_token') || ''}`,
      'Accept': 'application/json',
    });

    if (mode === 'full') {
      this.wcImporting = true;
      this.wcError = '';
      this.wcResult = null;
      this.http.post(`${environment.baseUrl}/api/boutique/admin/wc-import/upload`, formData, { headers }).subscribe({
        next: (res: any) => {
          this.wcResult = res.data;
          this.wcImporting = false;
        },
        error: (err: any) => { this.wcError = err?.error?.message || 'Error en importación'; this.wcImporting = false; },
      });
    } else {
      this.wcSyncingImages = true;
      this.wcImageError = '';
      this.wcImageResult = null;
      this.http.post(`${environment.baseUrl}/api/boutique/admin/wc-import/sync-images`, formData, { headers }).subscribe({
        next: (res: any) => { this.wcImageResult = res.data; this.wcSyncingImages = false; },
        error: (err: any) => { this.wcImageError = err?.error?.message || 'Error en sync de imágenes'; this.wcSyncingImages = false; },
      });
    }

    input.value = '';
  }

  runWcAttributeCatalogSync(): void {
    this.wcAttrSyncing = true;
    this.wcAttrError = '';
    this.wcAttrResult = null;
    this.http
      .post(`${environment.baseUrl}/api/boutique/admin/wc-import/sync-variant-attributes`, {}, { headers: this.getHeaders() })
      .subscribe({
        next: (res: any) => {
          this.wcAttrResult = res.data;
          this.wcAttrSyncing = false;
        },
        error: (err: any) => {
          this.wcAttrError = err?.error?.message || 'Error al sincronizar atributos';
          this.wcAttrSyncing = false;
        },
      });
  }

  runCleanup(): void {
    this.wcCleaning = true;
    this.wcCleanError = '';
    this.wcCleanResult = null;
    this.http.post(`${environment.baseUrl}/api/boutique/admin/wc-import/cleanup`, {}, { headers: this.getHeaders() }).subscribe({
      next: (res: any) => { this.wcCleanResult = res.data; this.wcCleaning = false; },
      error: (err: any) => { this.wcCleanError = err?.error?.message || 'Error en limpieza'; this.wcCleaning = false; },
    });
  }
}
