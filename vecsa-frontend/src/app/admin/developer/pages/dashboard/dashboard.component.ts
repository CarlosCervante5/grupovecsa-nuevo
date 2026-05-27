import { Component, OnInit, AfterViewInit, ElementRef, ViewChild, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from 'src/app/auth/services/auth.service';
import { adminBenchmarkUrl, adminPrimaryPanelUrl } from 'src/app/admin/utils/admin-route.util';
import { DevCrudService, CrudSection } from '../../services/dev-crud.service';
import * as echarts from 'echarts';

@Component({
  selector: 'app-developer-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  standalone: false,
})
export class DeveloperDashboardComponent implements OnInit, AfterViewInit, OnDestroy {
  user: any = null;
  role = '';
  activeSection = 'home';
  loading = false;
  rows: any[] = [];
  searchTerm = '';
  currentPage = 1;
  lastPage = 1;
  total = 0;

  // Modal
  modalOpen = false;
  modalMode: 'create' | 'edit' = 'create';
  modalData: Record<string, any> = {};
  modalSaving = false;
  modalError = '';
  deleteConfirmOpen = false;
  deleteTarget: any = null;
  deleteLoading = false;
  roleFilter = '';
  dynamicOptions: Record<string, { value: string; label: string }[]> = {};
  dealershipFilter = '';
  dealershipOptions: { value: string; label: string }[] = [];

  @ViewChild('ordersChart') ordersChartEl!: ElementRef;
  @ViewChild('productsChart') productsChartEl!: ElementRef;
  @ViewChild('vehiclesChart') vehiclesChartEl!: ElementRef;
  @ViewChild('statusChart') statusChartEl!: ElementRef;
  private chartInstances: echarts.ECharts[] = [];
  chartsReady = false;

  // Matrix state
  matrixRoles: any[] = [];
  matrixPermissions: any[] = [];
  matrixLoading = false;
  matrixError = '';
  matrixSaving: Record<string, boolean> = {};
  readonly adminModules = [
    'marketing', 'gestor', 'manager', 'staff', 'receptionist', 'valuator',
    'appointment_manager', 'administrator', 'technician', 'bodywork_paint_technician',
    'spare_parts', 'developer', 'gerente', 'seller', 'strega-seller', 'strega-manager',
    'strega-administrator', 'benchmark', 'store_management',
  ];

  // Benchmark state
  benchCompetitors: string[] = [];
  benchHistory: any[] = [];
  benchReports: string[] = [];
  benchScanResults: any = null;
  benchScanDetail: any[] = [];
  benchLoading = false;
  benchScanning = false;
  benchError = '';
  benchMethod: 'scraper' | 'api' = 'scraper';
  benchNewCompetitor = '';
  benchShowCompetitors = false;
  benchTab: 'scan' | 'history' | 'reports' = 'scan';

  // Stripe config state
  stripeConfig: any = {};
  stripeLoading = false;
  stripeSaving = false;
  stripeError = '';
  stripeSuccess = '';

  openpayConfig: Record<string, string> = {};
  openpayPasarelaEnabled = true;
  openpayLoading = false;
  openpaySaving = false;
  openpayError = '';
  openpaySuccess = '';

  /** Gemini API (Google AI Studio) — edición de fotos con IA. */
  geminiAiConfig: Record<string, unknown> = {};
  geminiAiFeatureEnabled = true;
  geminiAiDefaultModelHint = 'gemini-3.1-flash-image-preview';
  geminiAiLoading = false;
  geminiAiSaving = false;
  geminiAiError = '';
  geminiAiSuccess = '';

  stats: { label: string; value: string | number; icon: string; color: string; loading: boolean }[] = [
    { label: 'Vehículos publicados', value: '—', icon: 'directions_car', color: '#1c69d4', loading: true },
    { label: 'Productos Boutique', value: '—', icon: 'inventory_2', color: '#7c3aed', loading: true },
    { label: 'Pedidos Boutique', value: '—', icon: 'receipt_long', color: '#059669', loading: true },
    { label: 'Usuarios', value: '—', icon: 'people', color: '#d97706', loading: true },
    { label: 'Clientes', value: '—', icon: 'person', color: '#dc2626', loading: true },
    { label: 'Sucursales', value: '—', icon: 'store', color: '#0891b2', loading: true },
    { label: 'Valuaciones', value: '—', icon: 'price_check', color: '#4f46e5', loading: true },
    { label: 'Citas', value: '—', icon: 'event', color: '#be185d', loading: true },
  ];

  readonly sections: CrudSection[] = [
    {
      key: 'users', label: 'Usuarios', icon: 'people',
      endpoint: 'users', method: 'GET', dataKey: 'users',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'nickname', label: 'Nickname' },
        { key: 'email', label: 'Email' }, { key: 'role', label: 'Rol' },
        { key: 'dealership_names', label: 'Sucursales' }, { key: 'created_at', label: 'Creado' },
      ],
      paginated: true,
      storeEndpoint: 'users', updateEndpoint: 'users/update', deleteEndpoint: 'users/delete', idKey: 'uuid',
      formFields: [
        { key: 'nickname', label: 'Nickname', type: 'text', required: true },
        { key: 'email', label: 'Email', type: 'email', required: true },
        { key: 'password', label: 'Contraseña', type: 'password', required: true },
        { key: 'name', label: 'Nombre', type: 'text', required: true },
        { key: 'last_name', label: 'Apellido', type: 'text', required: true },
        { key: 'phone_1', label: 'Teléfono', type: 'text' },
        { key: 'gender', label: 'Género', type: 'select', options: [
          { value: 'male', label: 'Masculino' }, { value: 'female', label: 'Femenino' },
        ]},
        { key: 'role_name', label: 'Rol', type: 'select', options: [
          { value: 'administrator', label: 'Administrador' }, { value: 'marketing', label: 'Marketing' },
          { value: 'staff', label: 'Staff' }, { value: 'gestor', label: 'Gestor' },
          { value: 'receptionist', label: 'Recepcionista' }, { value: 'valuator', label: 'Valuador' },
          { value: 'appointment_manager', label: 'Gestor de Citas' },
          { value: 'technician', label: 'Técnico HyP' },
          { value: 'bodywork_paint_technician', label: 'Hojalatería y Pintura' },
          { value: 'spare_parts', label: 'Refacciones' },
          { value: 'manager', label: 'Manager' },
          { value: 'seller', label: 'Vendedor' },
          { value: 'gerente', label: 'Gerente' },
          { value: 'strega-seller', label: 'Strega vendedor' },
          { value: 'strega-manager', label: 'Strega gerente' },
          { value: 'strega-administrator', label: 'Strega administrador' },
          { value: 'client', label: 'Cliente' },
          { value: 'developer', label: 'Developer' },
        ]},
        { key: 'dealership_ids', label: 'Sucursales', type: 'multi-select',
          optionsEndpoint: 'dealerships/search', optionsMethod: 'POST',
          optionsDataKey: 'data', optionsValueKey: 'id', optionsLabelKey: 'name',
        },
      ],
    },
    {
      key: 'products', label: 'Productos Boutique', icon: 'inventory_2',
      endpoint: 'boutique/admin/products/search', method: 'POST', dataKey: 'products',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'name', label: 'Nombre' },
        { key: 'sku', label: 'SKU' }, { key: 'price', label: 'Precio' },
        { key: 'stock', label: 'Stock' }, { key: 'active', label: 'Activo' },
      ],
      searchable: true, paginated: true,
      storeEndpoint: 'boutique/admin/products/store', updateEndpoint: 'boutique/admin/products/update',
      deleteEndpoint: 'boutique/admin/products/delete', idKey: 'uuid',
      formFields: [
        { key: 'name', label: 'Nombre', type: 'text', required: true },
        { key: 'description', label: 'Descripción', type: 'textarea' },
        { key: 'sku', label: 'SKU', type: 'text', required: true },
        { key: 'price', label: 'Precio', type: 'number', required: true },
        { key: 'stock', label: 'Stock', type: 'number', required: true },
        { key: 'category_uuid', label: 'UUID Categoría', type: 'text', required: true },
        { key: 'active', label: 'Activo', type: 'checkbox' },
      ],
    },
    {
      key: 'categories', label: 'Categorías Boutique', icon: 'category',
      endpoint: 'boutique/admin/categories/search', method: 'POST', dataKey: 'categories',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'name', label: 'Nombre' }, { key: 'active', label: 'Activo' },
      ],
      searchable: true, paginated: true,
      storeEndpoint: 'boutique/admin/categories/store', updateEndpoint: 'boutique/admin/categories/update',
      deleteEndpoint: 'boutique/admin/categories/delete', idKey: 'uuid',
      formFields: [
        { key: 'name', label: 'Nombre', type: 'text', required: true },
        { key: 'description', label: 'Descripción', type: 'textarea' },
        { key: 'active', label: 'Activo', type: 'checkbox' },
      ],
    },
    {
      key: 'orders', label: 'Pedidos Boutique', icon: 'receipt_long',
      endpoint: 'boutique/admin/orders/search', method: 'POST', dataKey: 'orders',
      columns: [
        { key: 'order_number', label: '# Pedido' }, { key: 'status', label: 'Estatus' },
        { key: 'total', label: 'Total' }, { key: 'shipping_name', label: 'Cliente' },
        { key: 'created_at', label: 'Fecha' },
      ],
      searchable: true, paginated: true,
      updateEndpoint: 'boutique/admin/orders/update_status', idKey: 'uuid',
      formFields: [
        { key: 'status', label: 'Estatus', type: 'select', required: true, options: [
          { value: 'pending', label: 'Pendiente' }, { value: 'paid', label: 'Pagado' },
          { value: 'shipped', label: 'Enviado' }, { value: 'delivered', label: 'Entregado' },
          { value: 'cancelled', label: 'Cancelado' },
        ]},
      ],
    },
    {
      key: 'brands', label: 'Marcas', icon: 'branding_watermark',
      endpoint: 'vehicle_brands', method: 'GET', dataKey: 'vehicle_brands',
      columns: [{ key: 'uuid', label: 'UUID' }, { key: 'name', label: 'Nombre' }],
      storeEndpoint: 'vehicle_brands', idKey: 'uuid',
      formFields: [{ key: 'name', label: 'Nombre', type: 'text', required: true }],
    },
    {
      key: 'vehicles', label: 'Vehículos', icon: 'directions_car',
      endpoint: 'vehicles/search', method: 'GET', dataKey: 'vehicles',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'name', label: 'Nombre' },
        { key: 'vin', label: 'VIN' }, { key: 'sale_price', label: 'Precio' },
        { key: 'status', label: 'Estatus' }, { key: 'mileage', label: 'Km' },
      ],
      searchable: true, paginated: true,
      storeEndpoint: 'vehicles', updateEndpoint: 'vehicles/update',
      deleteEndpoint: 'vehicles/delete', idKey: 'uuid',
      formFields: [
        { key: 'name', label: 'Nombre', type: 'text', required: true },
        { key: 'description', label: 'Descripción', type: 'textarea' },
        { key: 'vin', label: 'VIN', type: 'text' },
        { key: 'sale_price', label: 'Precio venta', type: 'number' },
        { key: 'list_price', label: 'Precio lista', type: 'number' },
        { key: 'mileage', label: 'Kilometraje', type: 'number' },
        { key: 'status', label: 'Estatus', type: 'select', options: [
          { value: 'available', label: 'Disponible' }, { value: 'sold', label: 'Vendido' },
          { value: 'reserved', label: 'Reservado' },
        ]},
        { key: 'fuel_type', label: 'Combustible', type: 'select', options: [
          { value: 'gasoline', label: 'Gasolina' }, { value: 'diesel', label: 'Diésel' },
          { value: 'electric', label: 'Eléctrico' }, { value: 'hybrid', label: 'Híbrido' },
        ]},
        { key: 'transmission', label: 'Transmisión', type: 'select', options: [
          { value: 'automatic', label: 'Automática' }, { value: 'manual', label: 'Manual' },
        ]},
      ],
    },
    {
      key: 'roles', label: 'Roles', icon: 'admin_panel_settings',
      endpoint: 'roles', method: 'GET', dataKey: 'roles',
      columns: [{ key: 'id', label: 'ID' }, { key: 'name', label: 'Nombre' }],
      storeEndpoint: 'roles', idKey: 'id',
      formFields: [{ key: 'name', label: 'Nombre', type: 'text', required: true }],
    },
    {
      key: 'permissions', label: 'Permisos', icon: 'vpn_key',
      endpoint: 'permissions', method: 'GET', dataKey: 'permissions',
      columns: [
        { key: 'id', label: 'ID' }, { key: 'name', label: 'Nombre' },
        { key: 'guard_name', label: 'Guard' },
      ],
      storeEndpoint: 'permissions', deleteEndpoint: 'permissions',
      useApiResourceDelete: true, idKey: 'id',
      formFields: [{ key: 'name', label: 'Nombre del permiso', type: 'text', required: true }],
    },
    {
      key: 'dealerships', label: 'Sucursales', icon: 'store',
      endpoint: 'dealerships/search', method: 'POST', dataKey: 'dealerships',
      columns: [
        { key: 'name', label: 'Nombre' },
        { key: 'location', label: 'Ubicación' },
        { key: 'description', label: 'Descripción' },
      ],
    },
    {
      key: 'customers', label: 'Clientes', icon: 'person',
      endpoint: 'riders/search_customers', method: 'GET', dataKey: 'clientes',
      columns: [
        { key: 'customer_uuid', label: 'UUID' }, { key: 'customer_name', label: 'Nombre' },
        { key: 'customer_last_name', label: 'Apellido' }, { key: 'customer_email', label: 'Email' },
        { key: 'customer_phone', label: 'Teléfono' }, { key: 'total_points', label: 'Puntos' },
      ],
      searchable: false, paginated: true,
      storeEndpoint: 'riders', updateEndpoint: 'customers/update', idKey: 'customer_uuid',
      formFields: [
        { key: 'name', label: 'Nombre', type: 'text', required: true },
        { key: 'last_name', label: 'Apellido', type: 'text', required: true },
        { key: 'email_1', label: 'Email', type: 'email' },
        { key: 'cellphone', label: 'Celular', type: 'text' },
        { key: 'phone_1', label: 'Teléfono', type: 'text' },
        { key: 'gender', label: 'Género', type: 'select', options: [
          { value: 'M', label: 'Masculino' }, { value: 'F', label: 'Femenino' },
        ]},
        { key: 'origin_agency', label: 'Agencia origen', type: 'text' },
      ],
    },
    {
      key: 'rewards', label: 'Rewards', icon: 'emoji_events',
      endpoint: 'rewards/search', method: 'POST', dataKey: 'rewards',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'name', label: 'Nombre' },
        { key: 'type', label: 'Tipo' }, { key: 'begin_date', label: 'Inicio' }, { key: 'end_date', label: 'Fin' },
      ],
      storeEndpoint: 'rewards', updateEndpoint: 'rewards/update',
      deleteEndpoint: 'rewards/delete', idKey: 'uuid',
      formFields: [
        { key: 'name', label: 'Nombre', type: 'text', required: true },
        { key: 'description', label: 'Descripción', type: 'textarea' },
        { key: 'type', label: 'Tipo', type: 'text', required: true },
        { key: 'begin_date', label: 'Fecha inicio', type: 'text' },
        { key: 'end_date', label: 'Fecha fin', type: 'text' },
      ],
    },
    {
      key: 'slides', label: 'Home Slides', icon: 'slideshow',
      endpoint: 'home_slides/search', method: 'POST', dataKey: 'slides',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'title', label: 'Título' },
        { key: 'active', label: 'Activo' }, { key: 'sort_order', label: 'Orden' },
      ],
      deleteEndpoint: 'home_slides/delete', idKey: 'uuid',
    },
    {
      key: 'testimonials', label: 'Testimonios', icon: 'format_quote',
      endpoint: 'home_testimonials/search', method: 'POST', dataKey: 'testimonials',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'alt', label: 'Descripción' },
        { key: 'active', label: 'Activo' }, { key: 'sort_id', label: 'Orden' },
      ],
      deleteEndpoint: 'home_testimonials/delete', idKey: 'uuid',
      formFields: [
        { key: 'alt', label: 'Descripción / Alt', type: 'text' },
      ],
    },
    {
      key: 'valuations', label: 'Valuaciones', icon: 'price_check',
      endpoint: 'valuations/search', method: 'GET', dataKey: 'data',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'status', label: 'Estatus' },
        { key: 'status_repairs', label: 'Reparaciones' }, { key: 'status_parts', label: 'Refacciones' },
        { key: 'status_acquisition', label: 'Adquisición' }, { key: 'created_at', label: 'Fecha' },
      ],
      paginated: true,
      updateEndpoint: 'valuations/update', idKey: 'uuid',
      formFields: [
        { key: 'status', label: 'Estatus', type: 'select', required: true, options: [
          { value: 'pending', label: 'Pendiente' }, { value: 'in_progress', label: 'En progreso' },
          { value: 'completed', label: 'Completada' }, { value: 'cancelled', label: 'Cancelada' },
        ]},
        { key: 'status_repairs', label: 'Estatus Reparaciones', type: 'select', options: [
          { value: 'pending', label: 'Pendiente' }, { value: 'in_progress', label: 'En progreso' },
          { value: 'completed', label: 'Completado' },
        ]},
        { key: 'status_parts', label: 'Estatus Refacciones', type: 'select', options: [
          { value: 'pending', label: 'Pendiente' }, { value: 'pending_review', label: 'En revisión' },
          { value: 'parts_done', label: 'Completado' },
        ]},
        { key: 'status_acquisition', label: 'Estatus Adquisición', type: 'select', options: [
          { value: 'pending', label: 'Pendiente' }, { value: 'approved', label: 'Aprobada' },
          { value: 'rejected', label: 'Rechazada' },
        ]},
        { key: 'book_trade_in_offer', label: 'Oferta libro (toma)', type: 'number' },
        { key: 'book_sale_price', label: 'Precio libro (venta)', type: 'number' },
        { key: 'labor_cost', label: 'Costo mano de obra', type: 'number' },
        { key: 'spare_parts_cost', label: 'Costo refacciones', type: 'number' },
        { key: 'body_work_painting_cost', label: 'Costo hojalatería', type: 'number' },
        { key: 'final_offer', label: 'Oferta final', type: 'number' },
        { key: 'take_type', label: 'Tipo de toma', type: 'select', options: [
          { value: 'trade_in', label: 'Toma a cuenta' }, { value: 'direct_purchase', label: 'Compra directa' },
        ]},
        { key: 'comments', label: 'Comentarios', type: 'textarea' },
      ],
    },
    {
      key: 'appointments', label: 'Citas', icon: 'event',
      endpoint: 'appointment/search', method: 'POST', dataKey: 'appointments',
      columns: [
        { key: 'appointment_uuid', label: 'UUID' }, { key: 'customer_name', label: 'Cliente' },
        { key: 'appointment_type', label: 'Tipo' }, { key: 'dealership_name', label: 'Sucursal' },
        { key: 'appointment_scheduled_date', label: 'Fecha' },
      ],
      paginated: true,
      storeEndpoint: 'appointment', idKey: 'appointment_uuid',
      formFields: [
        { key: 'customer_name', label: 'Nombre cliente', type: 'text', required: true },
        { key: 'customer_last_name', label: 'Apellido cliente', type: 'text', required: true },
        { key: 'customer_phone', label: 'Teléfono', type: 'text', required: true },
        { key: 'customer_email', label: 'Email', type: 'email' },
        { key: 'type', label: 'Tipo', type: 'select', required: true, options: [
          { value: 'valuation', label: 'Valuación' }, { value: 'service', label: 'Servicio' },
        ]},
        { key: 'dealership_name', label: 'Sucursal', type: 'select', options: [
          { value: 'BMW Puebla Angelópolis', label: 'BMW Puebla Angelópolis' },
          { value: 'BMW Pachuca', label: 'BMW Pachuca' },
          { value: 'BMW Oaxaca', label: 'BMW Oaxaca' },
          { value: 'BMW Veracruz', label: 'BMW Veracruz' },
        ]},
        { key: 'scheduled_date', label: 'Fecha programada', type: 'text', required: true },
        { key: 'brand_name', label: 'Marca vehículo', type: 'text' },
        { key: 'model_name', label: 'Modelo vehículo', type: 'text' },
        { key: 'year', label: 'Año vehículo', type: 'number' },
        { key: 'mileage', label: 'Kilometraje', type: 'number' },
      ],
    },
    {
      key: 'spare_parts', label: 'Refacciones (Valuación)', icon: 'build',
      endpoint: 'valuations/search_parts', method: 'GET', dataKey: 'data',
      columns: [
        { key: 'uuid', label: 'UUID' }, { key: 'status', label: 'Estatus' },
        { key: 'status_parts', label: 'Estatus Refacciones' }, { key: 'created_at', label: 'Fecha' },
      ],
      paginated: true,
    },
  ];

  readonly quickLinks = [
    { label: 'Boutique', route: '/boutique', icon: 'storefront' },
    { label: 'Login', route: '/auth/iniciar-sesion', icon: 'lock_outline' },
    { label: 'Home', route: '/', icon: 'home' },
  ];

  constructor(
    private router: Router,
    private crud: DevCrudService,
    private auth: AuthService,
  ) {}

  ngOnInit(): void {
    const raw = localStorage.getItem('user');
    if (raw) this.user = JSON.parse(raw);
    this.role = localStorage.getItem('role') || '';
    this.loadStats();
  }

  ngAfterViewInit(): void {
    if (this.activeSection === 'home') setTimeout(() => this.initCharts(), 300);
  }

  ngOnDestroy(): void {
    this.chartInstances.forEach(c => c.dispose());
  }

  // ── Charts ──
  private initCharts(): void {
    this.loadOrdersChart();
    this.loadProductsChart();
    this.loadVehiclesChart();
    this.loadStatusChart();
    this.chartsReady = true;
    window.addEventListener('resize', () => this.chartInstances.forEach(c => c.resize()));
  }

  private loadOrdersChart(): void {
    if (!this.ordersChartEl) return;
    const chart = echarts.init(this.ordersChartEl.nativeElement);
    this.chartInstances.push(chart);
    this.crud.fetch('boutique/admin/orders/search', 'POST', { per_page: 100 }).subscribe({
      next: (res: any) => {
        const orders = res?.data?.orders?.data || [];
        const m: Record<string, number> = {};
        orders.forEach((o: any) => { if (o.created_at) { const k = o.created_at.substring(0, 7); m[k] = (m[k] || 0) + 1; } });
        const s = Object.entries(m).sort((a, b) => a[0].localeCompare(b[0])).slice(-6);
        chart.setOption({ tooltip: { trigger: 'axis' }, grid: { left: 40, right: 16, top: 20, bottom: 30 },
          xAxis: { type: 'category', data: s.map(x => x[0]), axisLabel: { fontSize: 11 } },
          yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
          series: [{ data: s.map(x => x[1]), type: 'bar', itemStyle: { color: '#1c69d4', borderRadius: [4, 4, 0, 0] } }] });
      }, error: () => chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }),
    });
  }

  private loadProductsChart(): void {
    if (!this.productsChartEl) return;
    const chart = echarts.init(this.productsChartEl.nativeElement);
    this.chartInstances.push(chart);
    this.crud.fetch('boutique/admin/products/search', 'POST', { per_page: 50 }).subscribe({
      next: (res: any) => {
        const p = (res?.data?.products?.data || []).filter((x: any) => x.stock !== undefined).sort((a: any, b: any) => (a.stock || 0) - (b.stock || 0)).slice(0, 8);
        chart.setOption({ tooltip: { trigger: 'axis' }, grid: { left: 40, right: 16, top: 20, bottom: 60 },
          xAxis: { type: 'category', data: p.map((x: any) => x.name?.substring(0, 15) || 'N/A'), axisLabel: { fontSize: 10, rotate: 30 } },
          yAxis: { type: 'value', minInterval: 1, axisLabel: { fontSize: 11 } },
          series: [{ data: p.map((x: any) => x.stock || 0), type: 'bar', itemStyle: { color: '#7c3aed', borderRadius: [4, 4, 0, 0] } }] });
      }, error: () => chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }),
    });
  }

  private loadVehiclesChart(): void {
    if (!this.vehiclesChartEl) return;
    const chart = echarts.init(this.vehiclesChartEl.nativeElement);
    this.chartInstances.push(chart);
    this.crud.fetch('vehicle_brands', 'GET', {}).subscribe({
      next: (res: any) => {
        const b = (Array.isArray(res?.data?.brands || res?.data) ? (res?.data?.brands || res?.data) : []).slice(0, 10);
        chart.setOption({ tooltip: { trigger: 'item' }, series: [{ type: 'pie', radius: ['40%', '70%'], label: { fontSize: 11 },
          data: b.map((x: any, i: number) => ({ name: x.name || `Marca ${i + 1}`, value: x.vehicles_count || x.total || Math.floor(Math.random() * 50 + 5) })),
          emphasis: { itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: 'rgba(0,0,0,0.2)' } } }] });
      }, error: () => chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }),
    });
  }

  private loadStatusChart(): void {
    if (!this.statusChartEl) return;
    const chart = echarts.init(this.statusChartEl.nativeElement);
    this.chartInstances.push(chart);
    this.crud.fetch('boutique/admin/orders/search', 'POST', { per_page: 200 }).subscribe({
      next: (res: any) => {
        const orders = res?.data?.orders?.data || [];
        const sm: Record<string, number> = {};
        orders.forEach((o: any) => { const s = o.status || 'desconocido'; sm[s] = (sm[s] || 0) + 1; });
        const colors: Record<string, string> = { pending: '#f59e0b', paid: '#059669', shipped: '#1c69d4', delivered: '#10b981', cancelled: '#ef4444', refunded: '#6b7280' };
        chart.setOption({ tooltip: { trigger: 'item' }, series: [{ type: 'pie', radius: '65%', label: { fontSize: 11 },
          data: Object.entries(sm).map(([n, v]) => ({ name: n, value: v, itemStyle: { color: colors[n] || '#94a3b8' } })) }] });
      }, error: () => chart.setOption({ title: { text: 'Sin datos', left: 'center', top: 'center', textStyle: { color: '#94a3b8', fontSize: 13 } } }),
    });
  }

  // ── Stats ──
  private loadStats(): void {
    const eps: { endpoint: string; method: 'GET' | 'POST'; dataKey: string; index: number; params?: any }[] = [
      { endpoint: 'vehicles/search', method: 'GET', dataKey: 'vehicles', index: 0 },
      { endpoint: 'boutique/admin/products/search', method: 'POST', dataKey: 'products', index: 1 },
      { endpoint: 'boutique/admin/orders/search', method: 'POST', dataKey: 'orders', index: 2 },
      { endpoint: 'users', method: 'GET', dataKey: 'users', index: 3 },
      { endpoint: 'riders/search_customers', method: 'GET', dataKey: 'clientes', index: 4, params: { type: 'valuation', paginate: 1 } },
      { endpoint: 'dealerships/search', method: 'POST', dataKey: 'dealerships', index: 5 },
      { endpoint: 'valuations/search', method: 'GET', dataKey: 'valuations', index: 6 },
      { endpoint: 'appointment/search', method: 'POST', dataKey: 'appointments', index: 7 },
    ];
    eps.forEach(ep => {
      const body = ep.params || { per_page: 1 };
      this.crud.fetch(ep.endpoint, ep.method, body).subscribe({
        next: (res: any) => {
          const d = res?.data?.[ep.dataKey] || res?.[ep.dataKey] || res?.data;
          let c: number | string = '—';
          if (d?.total !== undefined) c = d.total;
          else if (Array.isArray(d)) c = d.length;
          this.stats[ep.index].value = c;
          this.stats[ep.index].loading = false;
        },
        error: () => { this.stats[ep.index].value = 'Error'; this.stats[ep.index].loading = false; },
      });
    });
  }

  // ── CRUD Table ──
  get currentSection(): CrudSection | null {
    return this.sections.find(s => s.key === this.activeSection) || null;
  }

  selectSection(key: string): void {
    this.activeSection = key;
    this.rows = [];
    this.searchTerm = '';
    this.currentPage = 1;
    this.roleFilter = '';
    this.dealershipFilter = '';
    this.closeModal();
    if (key === 'home') {
      this.chartInstances.forEach(c => c.dispose());
      this.chartInstances = [];
      this.chartsReady = false;
      setTimeout(() => this.initCharts(), 300);
    } else if (key === 'role_matrix') {
      this.loadMatrix();
    } else if (key === 'benchmark') {
      this.benchTab = 'scan';
      this.loadBenchmark();
    } else if (key === 'stripe_config') {
      this.loadStripeConfig();
    } else if (key === 'openpay_config') {
      this.loadOpenpayConfig();
    } else if (key === 'gemini_ai_config') {
      this.loadGeminiAiConfig();
    } else {
      this.loadData();
      if (key === 'users' && this.dealershipOptions.length === 0) {
        this.crud.fetch('dealerships/search', 'POST', {}).subscribe({
          next: (res: any) => {
            const items = res?.data || [];
            this.dealershipOptions = (Array.isArray(items) ? items : []).map((d: any) => ({ value: String(d.id), label: d.name }));
          },
        });
      }
    }
  }

  loadData(): void {
    const sec = this.currentSection;
    if (!sec) return;
    this.loading = true;
    const body: any = {};
    if (sec.searchable && this.searchTerm) body.search = this.searchTerm;
    if (sec.paginated) body.page = this.currentPage;
    // Customers endpoint requires type param
    if (sec.key === 'customers') { body.type = 'valuation'; body.paginate = 15; }
    // Appointments endpoint requires type param
    if (sec.key === 'appointments') { body.type = 'valuation'; body.paginate = 15; }
    this.crud.fetch(sec.endpoint, sec.method, body).subscribe({
      next: (res: any) => {
        const data = res?.data?.[sec.dataKey] || res?.[sec.dataKey] || res?.data;
        if (data?.data) { this.rows = data.data; this.lastPage = data.last_page || 1; this.total = data.total || 0; }
        else if (Array.isArray(data)) { this.rows = data; this.total = data.length; }
        else if (Array.isArray(res)) { this.rows = res; this.total = res.length; }
        else { this.rows = []; }
        this.loading = false;
      },
      error: () => { this.rows = []; this.loading = false; },
    });
  }

  onSearch(): void { this.currentPage = 1; this.loadData(); }
  prevPage(): void { if (this.currentPage > 1) { this.currentPage--; this.loadData(); } }
  nextPage(): void { if (this.currentPage < this.lastPage) { this.currentPage++; this.loadData(); } }

  getCellValue(row: any, key: string): string {
    const val = row[key];
    if (val === null || val === undefined) return '—';
    if (typeof val === 'boolean') return val ? 'Sí' : 'No';
    if (key === 'active') return val ? 'Sí' : 'No';
    if (key === 'price' || key === 'sale_price' || key === 'total') return '$' + Number(val).toLocaleString();
    return String(val);
  }

  /** Identidad estable para filas CRUD (sucursales no envían uuid ni id en JSON). */
  trackCrudRow(row: any, index: number): string {
    return (
      row?.uuid ||
      row?.customer_uuid ||
      (row?.id != null ? String(row.id) : '') ||
      (row?.name != null && row?.location != null ? `${row.name}|${row.location}` : '') ||
      `row-${index}`
    );
  }

  get filteredRows(): any[] {
    let result = this.rows;
    if (this.activeSection === 'users') {
      if (this.roleFilter) {
        result = result.filter((r: any) => r.role === this.roleFilter);
      }
      if (this.dealershipFilter === '__none__') {
        result = result.filter((r: any) => !r.dealership_ids || r.dealership_ids.length === 0);
      } else if (this.dealershipFilter) {
        result = result.filter((r: any) =>
          Array.isArray(r.dealership_ids) && r.dealership_ids.includes(Number(this.dealershipFilter))
        );
      }
    }
    return result;
  }

  // ── Modal CRUD ──
  openCreate(): void {
    const sec = this.currentSection;
    if (!sec?.formFields || !sec.storeEndpoint) return;
    this.modalMode = 'create';
    this.modalData = {};
    sec.formFields.forEach(f => {
      if (f.type === 'checkbox') this.modalData[f.key] = false;
      else if (f.type === 'multi-select') this.modalData[f.key] = [];
      else this.modalData[f.key] = '';
      if (f.type === 'multi-select') this.loadDynamicOptions(f);
    });
    this.modalError = '';
    this.modalOpen = true;
  }

  openEdit(row: any): void {
    const sec = this.currentSection;
    if (!sec?.formFields || !sec.updateEndpoint) return;
    this.modalMode = 'edit';
    // Map aliased fields back to form field keys for customers
    if (sec.key === 'customers') {
      this.modalData = {
        uuid: row.customer_uuid,
        name: row.customer_name,
        last_name: row.customer_last_name,
        email_1: row.customer_email,
        phone_1: row.customer_phone,
        cellphone: row.customer_phone,
        gender: row.gender || '',
        origin_agency: row.origin_agency || '',
      };
    } else {
      this.modalData = { ...row };
    }
    // Map role to role_name for users edit
    if (sec.key === 'users' && row.role && !this.modalData.role_name) {
      this.modalData['role_name'] = row.role;
    }
    // Preselect multi-select fields
    sec.formFields.forEach(f => {
      if (f.type === 'multi-select') {
        this.loadDynamicOptions(f);
        if (Array.isArray(row[f.key])) {
          this.modalData[f.key] = row[f.key].map((v: any) => String(v));
        } else if (row.dealership_ids && f.key === 'dealership_ids') {
          this.modalData[f.key] = row.dealership_ids.map((v: any) => String(v));
        }
      }
    });
    this.modalError = '';
    this.modalOpen = true;
  }

  closeModal(): void {
    this.modalOpen = false;
    this.modalError = '';
    this.modalSaving = false;
  }

  saveModal(): void {
    const sec = this.currentSection;
    if (!sec) return;
    this.modalSaving = true;
    this.modalError = '';

    if (this.modalMode === 'create' && sec.storeEndpoint) {
      this.crud.store(sec.storeEndpoint, this.modalData).subscribe({
        next: (res: any) => {
          // After creating user, assign dealerships if present
          if (sec.key === 'users' && Array.isArray(this.modalData.dealership_ids) && this.modalData.dealership_ids.length > 0) {
            const userUuid = res?.data?.user?.uuid;
            if (userUuid) {
              this.crud.store('users/assign_dealerships', {
                user_uuid: userUuid,
                dealership_ids: this.modalData.dealership_ids.map((id: string) => Number(id)),
              }).subscribe({ next: () => {}, error: () => {} });
            }
          }
          this.modalSaving = false; this.closeModal(); this.loadData();
        },
        error: (err: any) => {
          this.modalSaving = false;
          this.modalError = err?.error?.message || err?.error?.error || 'Error al guardar';
        },
      });
    } else if (this.modalMode === 'edit' && sec.updateEndpoint) {
      const body = { ...this.modalData };
      if (sec.idKey) body.uuid = this.modalData[sec.idKey] || this.modalData.uuid;
      // Valuations endpoint expects valuation_uuid
      if (sec.key === 'valuations') { body.valuation_uuid = body.uuid; }
      // Users endpoint expects user_uuid
      if (sec.key === 'users') { body.user_uuid = body.uuid; }
      this.crud.update(sec.updateEndpoint, body).subscribe({
        next: () => {
          // After updating user, sync dealerships if present
          if (sec.key === 'users' && Array.isArray(this.modalData.dealership_ids)) {
            this.crud.store('users/assign_dealerships', {
              user_uuid: body.uuid,
              dealership_ids: this.modalData.dealership_ids.map((id: string) => Number(id)),
            }).subscribe({ next: () => {}, error: () => {} });
          }
          this.modalSaving = false; this.closeModal(); this.loadData();
        },
        error: (err: any) => {
          this.modalSaving = false;
          this.modalError = err?.error?.message || err?.error?.error || 'Error al actualizar';
        },
      });
    }
  }

  confirmDelete(row: any): void {
    this.deleteTarget = row;
    this.deleteConfirmOpen = true;
  }

  cancelDelete(): void {
    this.deleteConfirmOpen = false;
    this.deleteTarget = null;
  }

  executeDelete(): void {
    const sec = this.currentSection;
    if (!sec?.deleteEndpoint || !this.deleteTarget) return;
    this.deleteLoading = true;
    const idKey = sec.idKey || 'uuid';

    if (sec.useApiResourceDelete) {
      const id = this.deleteTarget[idKey] || this.deleteTarget.id;
      this.crud.deleteById(sec.deleteEndpoint, id).subscribe({
        next: () => { this.deleteLoading = false; this.cancelDelete(); this.loadData(); },
        error: () => { this.deleteLoading = false; this.cancelDelete(); },
      });
    } else {
      this.crud.delete(sec.deleteEndpoint, { uuid: this.deleteTarget[idKey], id: this.deleteTarget.id }).subscribe({
        next: () => { this.deleteLoading = false; this.cancelDelete(); this.loadData(); },
        error: () => { this.deleteLoading = false; this.cancelDelete(); },
      });
    }
  }

  onFieldChange(key: string, event: Event): void {
    const el = event.target as HTMLInputElement;
    if (el.type === 'checkbox') { this.modalData[key] = el.checked; }
    else if (el.type === 'number') { this.modalData[key] = el.value ? Number(el.value) : ''; }
    else { this.modalData[key] = el.value; }
  }

  isMultiSelected(key: string, value: string): boolean {
    return Array.isArray(this.modalData[key]) && this.modalData[key].includes(value);
  }

  toggleMultiSelect(key: string, value: string): void {
    if (!Array.isArray(this.modalData[key])) this.modalData[key] = [];
    const idx = this.modalData[key].indexOf(value);
    if (idx >= 0) this.modalData[key].splice(idx, 1);
    else this.modalData[key].push(value);
  }

  loadDynamicOptions(field: any): void {
    if (field.optionsEndpoint && !this.dynamicOptions[field.key]) {
      this.crud.fetch(field.optionsEndpoint, field.optionsMethod || 'POST', {}).subscribe({
        next: (res: any) => {
          const raw = res?.data?.[field.optionsDataKey || 'data'] || res?.data || [];
          const items = Array.isArray(raw) ? raw : [];
          this.dynamicOptions[field.key] = items.map((item: any) => ({
            value: String(item[field.optionsValueKey || 'id']),
            label: item[field.optionsLabelKey || 'name'],
          }));
        },
      });
    }
  }

  // ── Matrix Methods ──
  loadMatrix(): void {
    this.matrixLoading = true;
    this.matrixError = '';
    // Load permissions
    this.crud.fetch('permissions', 'GET', {}).subscribe({
      next: (perms: any) => {
        this.matrixPermissions = Array.isArray(perms) ? perms : (perms?.data || []);
        // Load roles
        this.crud.fetch('roles', 'GET', {}).subscribe({
          next: (roles: any) => {
            const roleList = Array.isArray(roles) ? roles : (roles?.data || []);
            let loaded = 0;
            this.matrixRoles = [];
            if (roleList.length === 0) { this.matrixLoading = false; return; }
            roleList.forEach((r: any) => {
              this.crud.fetch('roles/' + r.id, 'GET', {}).subscribe({
                next: (detail: any) => {
                  const roleData = detail?.data || detail;
                  this.matrixRoles.push({
                    id: roleData.id,
                    name: roleData.name,
                    permissions: (roleData.permissions || []).map((p: any) => p.name),
                  });
                  loaded++;
                  if (loaded === roleList.length) {
                    this.matrixRoles.sort((a: any, b: any) => a.id - b.id);
                    this.matrixLoading = false;
                  }
                },
                error: () => { loaded++; if (loaded === roleList.length) this.matrixLoading = false; },
              });
            });
          },
          error: () => { this.matrixError = 'Error al cargar roles'; this.matrixLoading = false; },
        });
      },
      error: () => { this.matrixError = 'Error al cargar permisos'; this.matrixLoading = false; },
    });
  }

  isPermissionActive(role: any, permName: string): boolean {
    return Array.isArray(role.permissions) && role.permissions.includes(permName);
  }

  togglePermission(role: any, perm: any): void {
    const key = role.id + '-' + perm.id;
    this.matrixSaving[key] = true;
    const has = role.permissions.includes(perm.name);
    const newPerms = has ? role.permissions.filter((p: string) => p !== perm.name) : [...role.permissions, perm.name];
    this.crud.put('roles/' + role.id, { permissions: newPerms }).subscribe({
      next: () => { role.permissions = newPerms; this.matrixSaving[key] = false; },
      error: () => { this.matrixSaving[key] = false; },
    });
  }

  toggleModuleAccess(role: any, mod: string): void {
    const permName = 'access ' + mod;
    const existing = this.matrixPermissions.find((p: any) => p.name === permName);
    if (existing) {
      this.togglePermission(role, existing);
    } else {
      // Create the permission first
      this.crud.store('permissions', { name: permName }).subscribe({
        next: (res: any) => {
          const newPerm = res?.data || res;
          if (newPerm?.id) this.matrixPermissions.push(newPerm);
          const fakePerm = { id: newPerm?.id || 'new', name: permName };
          this.togglePermission(role, fakePerm);
        },
        error: () => {},
      });
    }
  }

  // ── Benchmark Methods ──
  get benchTotalAds(): number {
    if (!this.benchScanResults?.summary) return 0;
    return this.benchScanResults.summary.reduce((s: number, r: any) => s + (r.adsCount || 0), 0);
  }

  loadBenchmark(): void {
    this.benchLoading = true;
    this.benchError = '';
    this.crud.fetch('benchmark/competitors', 'GET', {}).subscribe({
      next: (res: any) => {
        this.benchCompetitors = Array.isArray(res) ? res : [];
        this.benchLoading = false;
      },
      error: (err: any) => {
        this.benchError = err?.error?.error || 'No se pudo conectar al servicio de benchmark. Asegúrate de que el servidor reportADS esté corriendo.';
        this.benchLoading = false;
      },
    });
  }

  benchStartScan(): void {
    this.benchScanning = true;
    this.benchError = '';
    this.benchScanResults = null;
    this.benchScanDetail = [];
    this.crud.store('benchmark/scan', { method: this.benchMethod }).subscribe({
      next: (res: any) => {
        this.benchScanResults = res;
        this.benchScanning = false;
        // Load detail from latest history
        this.crud.fetch('benchmark/history', 'GET', {}).subscribe({
          next: (hist: any) => {
            const list = Array.isArray(hist) ? hist : [];
            if (list.length > 0) {
              this.crud.fetch('benchmark/history/' + list[0].file, 'GET', {}).subscribe({
                next: (detail: any) => { this.benchScanDetail = Array.isArray(detail) ? detail : []; },
              });
            }
          },
        });
      },
      error: (err: any) => {
        this.benchError = err?.error?.error || 'Error al escanear';
        this.benchScanning = false;
      },
    });
  }

  benchLoadHistory(): void {
    this.benchTab = 'history';
    this.benchLoading = true;
    this.crud.fetch('benchmark/history', 'GET', {}).subscribe({
      next: (res: any) => { this.benchHistory = Array.isArray(res) ? res : []; this.benchLoading = false; },
      error: () => { this.benchHistory = []; this.benchLoading = false; },
    });
  }

  benchLoadReports(): void {
    this.benchTab = 'reports';
    this.benchLoading = true;
    this.crud.fetch('benchmark/reports', 'GET', {}).subscribe({
      next: (res: any) => { this.benchReports = Array.isArray(res) ? res : []; this.benchLoading = false; },
      error: () => { this.benchReports = []; this.benchLoading = false; },
    });
  }

  benchAddCompetitor(): void {
    const name = this.benchNewCompetitor.trim();
    if (!name) return;
    this.crud.store('benchmark/competitors', { name }).subscribe({
      next: (res: any) => {
        this.benchCompetitors = res?.competitors || [...this.benchCompetitors, name];
        this.benchNewCompetitor = '';
      },
      error: (err: any) => { this.benchError = err?.error?.error || 'Error al agregar'; },
    });
  }

  benchRemoveCompetitor(name: string): void {
    this.crud.deleteById('benchmark/competitors', encodeURIComponent(name)).subscribe({
      next: (res: any) => {
        this.benchCompetitors = res?.competitors || this.benchCompetitors.filter(c => c !== name);
      },
      error: () => {},
    });
  }

  benchViewHistoryDetail(file: string): void {
    this.benchLoading = true;
    this.crud.fetch('benchmark/history/' + file, 'GET', {}).subscribe({
      next: (detail: any) => {
        this.benchScanDetail = Array.isArray(detail) ? detail : [];
        this.benchTab = 'scan';
        this.benchLoading = false;
      },
      error: () => { this.benchLoading = false; },
    });
  }

  // ── Stripe Config Methods ──
  loadStripeConfig(): void {
    this.stripeLoading = true;
    this.stripeError = '';
    this.stripeSuccess = '';
    this.crud.fetch('settings/stripe', 'POST', {}).subscribe({
      next: (res: any) => {
        this.stripeConfig = res?.data || {};
        this.stripeLoading = false;
      },
      error: (err: any) => {
        this.stripeError = err?.error?.message || 'Error al cargar la configuración de Stripe';
        this.stripeLoading = false;
      },
    });
  }

  saveStripeConfig(): void {
    this.stripeSaving = true;
    this.stripeError = '';
    this.stripeSuccess = '';
    const payload: any = { stripe_mode: this.stripeConfig.stripe_mode || 'test' };
    const fields = [
      'stripe_test_publishable_key', 'stripe_test_secret_key', 'stripe_test_webhook_secret',
      'stripe_live_publishable_key', 'stripe_live_secret_key', 'stripe_live_webhook_secret',
    ];
    fields.forEach(f => {
      const val = this.stripeConfig[f];
      // Skip masked values (they start with ••••••••)
      if (val && !val.startsWith('••••••••')) {
        payload[f] = val;
      }
    });
    this.crud.fetch('settings/stripe/update', 'POST', payload).subscribe({
      next: () => {
        this.stripeSuccess = 'Configuración guardada correctamente';
        this.stripeSaving = false;
        this.loadStripeConfig();
      },
      error: (err: any) => {
        const errors = err?.error?.errors;
        this.stripeError = errors
          ? ([] as string[]).concat(...Object.values(errors) as string[][]).join(', ')
          : (err?.error?.message || 'Error al guardar la configuración');
        this.stripeSaving = false;
      },
    });
  }

  toggleStripeMode(): void {
    this.stripeConfig.stripe_mode = this.stripeConfig.stripe_mode === 'live' ? 'test' : 'live';
  }

  loadOpenpayConfig(): void {
    this.openpayLoading = true;
    this.openpayError = '';
    this.openpaySuccess = '';
    this.crud.fetch('settings/openpay', 'POST', {}).subscribe({
      next: (res: any) => {
        const d = res?.data || {};
        this.openpayConfig = { ...d };
        this.openpayPasarelaEnabled = d.boutique_checkout_openpay !== false;
        this.openpayLoading = false;
      },
      error: (err: any) => {
        this.openpayError = err?.error?.message || 'Error al cargar OpenPay';
        this.openpayLoading = false;
      },
    });
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
    ];
    fields.forEach((f) => {
      const val = this.openpayConfig[f];
      if (val && !String(val).startsWith('••••••••')) {
        payload[f] = val;
      }
    });
    this.crud.fetch('settings/openpay/update', 'POST', payload).subscribe({
      next: () => {
        this.openpaySuccess = 'Configuración OpenPay guardada';
        this.openpaySaving = false;
        this.loadOpenpayConfig();
      },
      error: (err: any) => {
        const errors = err?.error?.errors;
        this.openpayError = errors
          ? ([] as string[]).concat(...(Object.values(errors) as string[][])).join(', ')
          : err?.error?.message || 'Error al guardar';
        this.openpaySaving = false;
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

  loadGeminiAiConfig(): void {
    this.geminiAiLoading = true;
    this.geminiAiError = '';
    this.geminiAiSuccess = '';
    this.crud.fetch('settings/gemini_image_ai', 'POST', {}).subscribe({
      next: (res: any) => {
        const d = res?.data || {};
        this.geminiAiConfig = { ...d };
        this.geminiAiFeatureEnabled = d.image_ai_enabled !== false;
        if (typeof d.default_model_hint === 'string') {
          this.geminiAiDefaultModelHint = d.default_model_hint;
        }
        this.geminiAiLoading = false;
      },
      error: (err: any) => {
        this.geminiAiError = err?.error?.message || 'Error al cargar Gemini';
        this.geminiAiLoading = false;
      },
    });
  }

  saveGeminiAiConfig(): void {
    this.geminiAiSaving = true;
    this.geminiAiError = '';
    this.geminiAiSuccess = '';
    const payload: Record<string, unknown> = {
      image_ai_enabled: this.geminiAiFeatureEnabled,
    };
    const model = String(this.geminiAiConfig['gemini_image_model'] ?? '').trim();
    payload['gemini_image_model'] = model;
    const rawKey = String(this.geminiAiConfig['gemini_api_key'] ?? '').trim();
    if (rawKey && !rawKey.startsWith('••')) {
      payload['gemini_api_key'] = rawKey;
    }
    this.crud.fetch('settings/gemini_image_ai/update', 'POST', payload).subscribe({
      next: () => {
        this.geminiAiSuccess = 'Configuración Gemini guardada';
        this.geminiAiSaving = false;
        this.loadGeminiAiConfig();
        setTimeout(() => (this.geminiAiSuccess = ''), 4000);
      },
      error: (err: any) => {
        const errors = err?.error?.errors;
        this.geminiAiError = errors
          ? ([] as string[]).concat(...(Object.values(errors) as string[][])).join(', ')
          : err?.error?.message || 'Error al guardar';
        this.geminiAiSaving = false;
      },
    });
  }

  toggleGeminiAiFeature(): void {
    this.geminiAiFeatureEnabled = !this.geminiAiFeatureEnabled;
  }

  get benchmarkPanelUrl(): string {
    return adminBenchmarkUrl(this.role) ?? '/admin/benchmark';
  }

  /** Panel operativo fuera del Dev Panel (p. ej. administrador si el rol en sesión es developer). */
  get mainPanelUrl(): string {
    return adminPrimaryPanelUrl(this.role);
  }

  navigate(route: string): void { this.router.navigateByUrl(route); }
  logout(): void {
    this.auth.signOut(this.router);
  }
}
