<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\IncadeaSyncLog;
use App\Services\Incadea\IncadeaSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncadeaSyncController extends Controller
{
    /**
     * Trigger the Incadea → Boutique sync process.
     */
    public function sync(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return ApiResponseHelper::apiError('No autenticado', null, 401, 'UNAUTHENTICATED');
            }

            $canSync = $user->hasRole('developer')
                || $user->hasRole('administrator')
                || $user->hasRole('admin');

            if (! $canSync) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $config = IncadeaSyncService::getSyncConfig();
            $filters = [
                'excluded_brands'     => $request->input('excluded_brands', $config['excluded_brands'] ?? []),
                'excluded_categories' => $request->input('excluded_categories', $config['excluded_categories'] ?? []),
            ];
            if (! is_array($filters['excluded_brands'])) {
                $filters['excluded_brands'] = [];
            }
            if (! is_array($filters['excluded_categories'])) {
                $filters['excluded_categories'] = [];
            }

            $service = app(IncadeaSyncService::class);
            $result = $service->executeSyncProcess($filters);

            return ApiResponseHelper::apiSuccess(200, 'Sincronización completada exitosamente', $result);
        } catch (\Throwable $e) {
            Log::error('INCADEA_SYNC_HTTP_ERROR', [
                'message' => $e->getMessage(),
                'exception' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return ApiResponseHelper::apiError('Error en la sincronización: ' . $e->getMessage(), $e->getMessage(), 500, 'SYNC_ERROR');
        }
    }

    /**
     * Return paginated sync logs.
     */
    public function logs(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return ApiResponseHelper::apiError('No autenticado', null, 401, 'UNAUTHENTICATED');
            }
            $can = $user->hasRole('developer') || $user->hasRole('administrator') || $user->hasRole('admin');
            if (! $can) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $logs = IncadeaSyncLog::orderBy('created_at', 'desc')->paginate(15);

            return ApiResponseHelper::apiSuccess(200, 'Logs obtenidos exitosamente', ['logs' => $logs]);
        } catch (\Throwable $e) {
            Log::error('INCADEA_LOGS_ERROR', ['message' => $e->getMessage(), 'exception' => $e::class]);

            return ApiResponseHelper::apiError('Error al obtener logs', $e->getMessage(), 500, 'GET_LOGS_ERROR');
        }
    }

    /**
     * Return current sync config from system_settings.
     */
    public function getConfig(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return ApiResponseHelper::apiError('No autenticado', null, 401, 'UNAUTHENTICATED');
            }
            $can = $user->hasRole('developer') || $user->hasRole('administrator') || $user->hasRole('admin');
            if (! $can) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $config = IncadeaSyncService::getSyncConfig();

            return ApiResponseHelper::apiSuccess(200, 'Configuración obtenida exitosamente', [
                'config' => $config,
                'api_probe' => IncadeaSyncService::probeApi(),
            ]);
        } catch (\Throwable $e) {
            Log::error('INCADEA_GET_CONFIG_ERROR', ['message' => $e->getMessage(), 'exception' => $e::class]);

            return ApiResponseHelper::apiError('Error al obtener configuración', $e->getMessage(), 500, 'GET_CONFIG_ERROR');
        }
    }

    /**
     * Update sync config in system_settings.
     */
    public function updateConfig(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user) {
                return ApiResponseHelper::apiError('No autenticado', null, 401, 'UNAUTHENTICATED');
            }
            $can = $user->hasRole('developer') || $user->hasRole('administrator') || $user->hasRole('admin');
            if (! $can) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $config = [
                'excluded_brands'     => $request->input('excluded_brands', []),
                'excluded_categories' => $request->input('excluded_categories', []),
            ];
            if (! is_array($config['excluded_brands'])) {
                $config['excluded_brands'] = [];
            }
            if (! is_array($config['excluded_categories'])) {
                $config['excluded_categories'] = [];
            }

            IncadeaSyncService::setSyncConfig($config);

            return ApiResponseHelper::apiSuccess(200, 'Configuración actualizada exitosamente', ['config' => $config]);
        } catch (\Throwable $e) {
            Log::error('INCADEA_UPDATE_CONFIG_ERROR', ['message' => $e->getMessage(), 'exception' => $e::class]);

            return ApiResponseHelper::apiError('Error al actualizar configuración', $e->getMessage(), 500, 'UPDATE_CONFIG_ERROR');
        }
    }
}
