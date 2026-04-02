<?php

namespace App\Http\Controllers\Developer;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApiMonitorController extends Controller
{
    /**
     * Get recent API request logs with stats.
     */
    public function logs(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $table = env('DB_TABLE_PREFIX', '') . 'request_logs';
            $perPage = $request->input('per_page', 50);
            $search = $request->input('search', '');

            $query = DB::table($table)->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('path', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%")
                      ->orWhere('method', 'like', "%{$search}%");
                });
            }

            $logs = $query->paginate($perPage);

            return ApiResponseHelper::apiSuccess(200, 'Logs obtenidos', ['logs' => $logs]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener logs', $e->getMessage(), 500, 'GET_LOGS_ERROR');
        }
    }

    /**
     * Get API stats: requests per hour, top endpoints, top IPs.
     */
    public function stats(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $table = env('DB_TABLE_PREFIX', '') . 'request_logs';

            $totalToday = DB::table($table)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            $totalHour = DB::table($table)
                ->where('created_at', '>=', now()->subHour())
                ->count();

            $total24h = DB::table($table)
                ->where('created_at', '>=', now()->subHours(24))
                ->count();

            $topEndpoints = DB::table($table)
                ->select('path', 'method', DB::raw('COUNT(*) as hits'))
                ->where('created_at', '>=', now()->subHours(24))
                ->groupBy('path', 'method')
                ->orderByDesc('hits')
                ->limit(15)
                ->get();

            $topIps = DB::table($table)
                ->select('ip_address', DB::raw('COUNT(*) as hits'))
                ->where('created_at', '>=', now()->subHours(24))
                ->groupBy('ip_address')
                ->orderByDesc('hits')
                ->limit(10)
                ->get();

            $bandwidthToday = DB::table($table)
                ->where('created_at', '>=', now()->startOfDay())
                ->sum('total_size');

            return ApiResponseHelper::apiSuccess(200, 'Stats obtenidos', [
                'total_today'   => $totalToday,
                'total_hour'    => $totalHour,
                'total_24h'     => $total24h,
                'bandwidth_today' => $bandwidthToday,
                'top_endpoints' => $topEndpoints,
                'top_ips'       => $topIps,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener stats', $e->getMessage(), 500, 'GET_STATS_ERROR');
        }
    }

    /**
     * Check connection status of external services.
     */
    public function health(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $services = [];

            // Database
            try {
                $start = microtime(true);
                DB::select('SELECT 1');
                $ms = round((microtime(true) - $start) * 1000);
                $services[] = ['name' => 'Base de Datos', 'status' => 'ok', 'latency_ms' => $ms, 'icon' => 'storage'];
            } catch (\Exception $e) {
                $services[] = ['name' => 'Base de Datos', 'status' => 'error', 'latency_ms' => null, 'error' => $e->getMessage(), 'icon' => 'storage'];
            }

            // Incadea API
            try {
                $start = microtime(true);
                $resp = Http::timeout(10)->get(config('services.incadea.api_url', 'http://52.21.121.207/api/incadea/get_spare_parts'));
                $ms = round((microtime(true) - $start) * 1000);
                $services[] = [
                    'name' => 'Incadea API',
                    'status' => $resp->successful() ? 'ok' : 'error',
                    'latency_ms' => $ms,
                    'http_status' => $resp->status(),
                    'icon' => 'sync',
                ];
            } catch (\Exception $e) {
                $services[] = ['name' => 'Incadea API', 'status' => 'error', 'latency_ms' => null, 'error' => $e->getMessage(), 'icon' => 'sync'];
            }

            // Stripe API
            try {
                $start = microtime(true);
                $resp = Http::timeout(5)->get('https://api.stripe.com/v1');
                $ms = round((microtime(true) - $start) * 1000);
                $services[] = [
                    'name' => 'Stripe API',
                    'status' => ($resp->status() === 401 || $resp->successful()) ? 'ok' : 'error',
                    'latency_ms' => $ms,
                    'icon' => 'credit_card',
                ];
            } catch (\Exception $e) {
                $services[] = ['name' => 'Stripe API', 'status' => 'error', 'latency_ms' => null, 'error' => $e->getMessage(), 'icon' => 'credit_card'];
            }

            // Cache/Redis
            try {
                $start = microtime(true);
                cache()->put('health_check', true, 5);
                $val = cache()->get('health_check');
                $ms = round((microtime(true) - $start) * 1000);
                $services[] = ['name' => 'Cache', 'status' => $val ? 'ok' : 'error', 'latency_ms' => $ms, 'icon' => 'memory'];
            } catch (\Exception $e) {
                $services[] = ['name' => 'Cache', 'status' => 'error', 'latency_ms' => null, 'error' => $e->getMessage(), 'icon' => 'memory'];
            }

            return ApiResponseHelper::apiSuccess(200, 'Health check completado', ['services' => $services]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error en health check', $e->getMessage(), 500, 'HEALTH_ERROR');
        }
    }
}
