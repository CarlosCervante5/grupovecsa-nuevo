<?php

namespace App\Http\Controllers\AdminDashboard;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueOrder;
use App\Models\Boutique\BoutiqueOrderItem;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Customer;
use App\Models\CustomerAppointment;
use App\Models\CustomerReward;
use App\Models\Dealership;
use App\Models\MarketingCampaign;
use App\Models\MarketingEvent;
use App\Models\MarketingPromotion;
use App\Models\Reward;
use App\Models\User;
use App\Models\Valuations\VehicleValuation;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDashboardController extends Controller
{
    public function metrics(Request $request)
    {
        try {
            $role = $request->user()->getRoleNames()->first();

            $data = match ($role) {
                'administrator' => $this->administratorMetrics(),
                'marketing'     => $this->marketingMetrics(),
                'staff'         => $this->staffMetrics(),
                'gestor'        => $this->gestorMetrics(),
                'receptionist'  => $this->receptionistMetrics(),
                'valuator'      => $this->valuatorMetrics(),
                'appointment_manager' => $this->appointmentManagerMetrics(),
                'bodywork_paint_technician' => $this->bodyworkMetrics(),
                'spare_parts'   => $this->sparePartsMetrics(),
                'gerente'       => $this->gerenteMetrics(),
                default         => ['stats' => (object)[], 'charts' => (object)[]],
            };

            return ApiResponseHelper::apiSuccess(200, 'Métricas obtenidas', $data);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener métricas', $e->getMessage(), 500, 'DASHBOARD_METRICS_ERROR');
        }
    }

    private function administratorMetrics(): array
    {
        $stats = [
            'vehicles'     => Vehicle::count(),
            'products'     => BoutiqueProduct::where('active', true)->count(),
            'orders'       => BoutiqueOrder::count(),
            'users'        => User::count(),
            'customers'    => Customer::count(),
            'dealerships'  => Dealership::count(),
            'valuations'   => VehicleValuation::count(),
            'appointments' => CustomerAppointment::count(),
        ];

        $ordersByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $ordersByMonth[] = [
                'month' => $start->format('Y-m'),
                'count' => BoutiqueOrder::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        $ordersByStatus = BoutiqueOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')->get()->map(fn($r) => ['status' => $r->status, 'count' => $r->count])->values()->toArray();

        return ['stats' => $stats, 'charts' => ['orders_by_month' => $ordersByMonth, 'orders_by_status' => $ordersByStatus]];
    }

    private function marketingMetrics(): array
    {
        // marketing_campaigns usa page_status (enum), no existe columna status.
        // vehicles usa page_status (active|inactive|sale|valuing), no status/available.
        $stats = [
            'campaigns'  => MarketingCampaign::count(),
            'promotions' => MarketingPromotion::count(),
            'events'     => MarketingEvent::count(),
            'vehicles'   => Vehicle::where('page_status', 'active')->count(),
        ];

        $vehiclesByBrand = VehicleBrand::withCount('vehicles')->get()
            ->map(fn($b) => ['name' => $b->name, 'count' => $b->vehicles_count])->values()->toArray();

        return ['stats' => $stats, 'charts' => ['vehicles_by_brand' => $vehiclesByBrand]];
    }

    private function staffMetrics(): array
    {
        return [
            'stats' => [
                'customers'      => Customer::count(),
                'active_rewards' => Reward::where('status', 'active')->count(),
                'total_points'   => (int) CustomerReward::sum('points'),
            ],
            'charts' => (object)[],
        ];
    }

    private function gestorMetrics(): array
    {
        $stats = [
            'promotions'     => MarketingPromotion::count(),
            'events'         => MarketingEvent::count(),
            'active_rewards' => Reward::where('status', 'active')->count(),
        ];

        $eventsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $eventsByMonth[] = [
                'month' => $start->format('Y-m'),
                'count' => MarketingEvent::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        return ['stats' => $stats, 'charts' => ['events_by_month' => $eventsByMonth]];
    }

    private function receptionistMetrics(): array
    {
        $today     = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd   = Carbon::now()->endOfWeek();

        $stats = [
            'today'      => CustomerAppointment::whereDate('scheduled_date', $today)->count(),
            'this_week'  => CustomerAppointment::whereBetween('scheduled_date', [$weekStart, $weekEnd])->count(),
            'total'      => CustomerAppointment::count(),
        ];

        $byType = CustomerAppointment::selectRaw('type, count(*) as count')
            ->groupBy('type')->get()->map(fn($r) => ['type' => $r->type, 'count' => $r->count])->values()->toArray();

        return ['stats' => $stats, 'charts' => ['appointments_by_type' => $byType]];
    }

    private function valuatorMetrics(): array
    {
        $stats = [
            'pending'     => VehicleValuation::where('status', 'pending')->count(),
            'in_progress' => VehicleValuation::where('status', 'in_progress')->count(),
            'completed'   => VehicleValuation::where('status', 'completed')->count(),
            'total'       => VehicleValuation::count(),
        ];

        $byStatus = VehicleValuation::selectRaw('status, count(*) as count')
            ->groupBy('status')->get()->map(fn($r) => ['status' => $r->status, 'count' => $r->count])->values()->toArray();

        return ['stats' => $stats, 'charts' => ['valuations_by_status' => $byStatus]];
    }

    private function appointmentManagerMetrics(): array
    {
        $today     = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd   = Carbon::now()->endOfWeek();

        $stats = [
            'today'       => CustomerAppointment::whereDate('scheduled_date', $today)->count(),
            'this_week'   => CustomerAppointment::whereBetween('scheduled_date', [$weekStart, $weekEnd])->count(),
            'unassigned'  => CustomerAppointment::whereNull('valuator_uuid')->count(),
            'total'       => CustomerAppointment::count(),
        ];

        $byMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $byMonth[] = [
                'month' => $start->format('Y-m'),
                'count' => CustomerAppointment::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        return ['stats' => $stats, 'charts' => ['appointments_by_month' => $byMonth]];
    }

    private function bodyworkMetrics(): array
    {
        $stats = [
            'pending'     => VehicleValuation::where('status_repairs', 'pending')->count(),
            'in_progress' => VehicleValuation::where('status_repairs', 'in_progress')->count(),
            'completed'   => VehicleValuation::where('status_repairs', 'completed')->count(),
        ];

        $byStatus = VehicleValuation::selectRaw('status_repairs as status, count(*) as count')
            ->whereNotNull('status_repairs')
            ->groupBy('status_repairs')->get()->map(fn($r) => ['status' => $r->status, 'count' => $r->count])->values()->toArray();

        return ['stats' => $stats, 'charts' => ['repairs_by_status' => $byStatus]];
    }

    private function sparePartsMetrics(): array
    {
        $stats = [
            'pending'        => VehicleValuation::where('status_parts', 'pending')->count(),
            'pending_review' => VehicleValuation::where('status_parts', 'pending_review')->count(),
            'completed'      => VehicleValuation::where('status_parts', 'parts_done')->count(),
        ];

        $byStatus = VehicleValuation::selectRaw('status_parts as status, count(*) as count')
            ->whereNotNull('status_parts')
            ->groupBy('status_parts')->get()->map(fn($r) => ['status' => $r->status, 'count' => $r->count])->values()->toArray();

        return ['stats' => $stats, 'charts' => ['parts_by_status' => $byStatus]];
    }

    private function gerenteMetrics(): array
    {
        // ── Stats generales ──
        $stats = [
            'vehicles'     => Vehicle::count(),
            'products'     => BoutiqueProduct::where('active', true)->count(),
            'orders'       => BoutiqueOrder::count(),
            'users'        => User::count(),
            'customers'    => Customer::count(),
            'dealerships'  => Dealership::count(),
            'valuations'   => VehicleValuation::count(),
            'appointments' => CustomerAppointment::count(),
        ];

        // ── Stats boutique ──
        $paidStatuses = ['pagado', 'en_preparacion', 'enviado', 'entregado'];
        $stats['total_sales'] = (float) BoutiqueOrder::whereIn('status', $paidStatuses)->sum('total');
        $stats['pending_orders'] = BoutiqueOrder::where('status', 'pendiente')->count();

        // ── Stats citas/valuaciones ──
        $today     = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd   = Carbon::now()->endOfWeek();

        $stats['appointments_today']      = CustomerAppointment::whereDate('scheduled_date', $today)->count();
        $stats['appointments_week']       = CustomerAppointment::whereBetween('scheduled_date', [$weekStart, $weekEnd])->count();
        $stats['valuations_pending']      = VehicleValuation::where('status', 'pending')->count();
        $stats['valuations_in_progress']  = VehicleValuation::where('status', 'in_progress')->count();

        // ── Stats benchmark ──
        $stats['benchmark_competitors'] = 0;
        $stats['benchmark_scans']       = 0;

        if (Storage::exists('benchmark/competitors.json')) {
            $competitors = json_decode(Storage::get('benchmark/competitors.json'), true);
            $stats['benchmark_competitors'] = is_array($competitors) ? count($competitors) : 0;
        }

        if (Storage::exists('benchmark/data')) {
            $stats['benchmark_scans'] = count(
                array_filter(Storage::files('benchmark/data'), fn($f) => str_ends_with($f, '.json'))
            );
        }

        // ── Charts: Pedidos por mes (últimos 6 meses) ──
        $ordersByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $ordersByMonth[] = [
                'month' => $start->format('Y-m'),
                'count' => BoutiqueOrder::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        // ── Charts: Pedidos por estado ──
        $ordersByStatus = BoutiqueOrder::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->map(fn($r) => ['status' => $r->status, 'count' => $r->count])
            ->values()
            ->toArray();

        // ── Charts: Top 5 productos más vendidos ──
        $topProducts = BoutiqueOrderItem::selectRaw('product_name as name, SUM(quantity) as quantity')
            ->groupBy('product_name')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'quantity' => (int) $r->quantity])
            ->values()
            ->toArray();

        // ── Charts: Valuaciones por sucursal ──
        $valuationsByDealership = VehicleValuation::selectRaw('dealership_id, count(*) as count')
            ->whereNotNull('dealership_id')
            ->groupBy('dealership_id')
            ->get()
            ->map(function ($r) {
                $dealership = Dealership::find($r->dealership_id);
                return [
                    'name'  => $dealership ? $dealership->name : 'Desconocida',
                    'count' => $r->count,
                ];
            })
            ->values()
            ->toArray();

        // ── Charts: Citas por sucursal ──
        $appointmentsByDealership = CustomerAppointment::selectRaw('dealership_name as name, count(*) as count')
            ->whereNotNull('dealership_name')
            ->where('dealership_name', '!=', '')
            ->groupBy('dealership_name')
            ->get()
            ->map(fn($r) => ['name' => $r->name, 'count' => $r->count])
            ->values()
            ->toArray();

        // ── Charts: Citas por mes (últimos 6 meses) ──
        $appointmentsByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end   = Carbon::now()->subMonths($i)->endOfMonth();
            $appointmentsByMonth[] = [
                'month' => $start->format('Y-m'),
                'count' => CustomerAppointment::whereBetween('created_at', [$start, $end])->count(),
            ];
        }

        return [
            'stats'  => $stats,
            'charts' => [
                'orders_by_month'            => $ordersByMonth,
                'orders_by_status'           => $ordersByStatus,
                'top_products'               => $topProducts,
                'valuations_by_dealership'   => $valuationsByDealership,
                'appointments_by_dealership' => $appointmentsByDealership,
                'appointments_by_month'      => $appointmentsByMonth,
            ],
        ];
    }

}
