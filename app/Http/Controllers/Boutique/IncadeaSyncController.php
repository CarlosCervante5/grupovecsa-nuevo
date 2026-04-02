<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\IncadeaSyncLog;
use App\Services\Incadea\IncadeaSyncService;
use Illuminate\Http\Request;

class IncadeaSyncController extends Controller
{
    /**
     * Trigger the Incadea → Boutique sync process.
     */
    public function sync(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $config = IncadeaSyncService::getSyncConfig();
            $filters = [
                'excluded_brands'     => $request->input('excluded_brands', $config['excluded_brands'] ?? []),
                'excluded_categories' => $request->input('excluded_categories', $config['excluded_categories'] ?? []),
            ];

            $service = app(IncadeaSyncService::class);
            $result = $service->executeSyncProcess($filters);

            return ApiResponseHelper::apiSuccess(200, 'Sincronización completada exitosamente', $result);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error en la sincronización', $e->getMessage(), 500, 'SYNC_ERROR');
        }
    }

    /**
     * Return paginated sync logs.
     */
    public function logs(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $logs = IncadeaSyncLog::orderBy('created_at', 'desc')->paginate(15);

            return ApiResponseHelper::apiSuccess(200, 'Logs obtenidos exitosamente', ['logs' => $logs]);
        } catch (\Exception $e) {
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
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $config = IncadeaSyncService::getSyncConfig();

            return ApiResponseHelper::apiSuccess(200, 'Configuración obtenida exitosamente', ['config' => $config]);
        } catch (\Exception $e) {
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
            if (!$user->hasRole('developer') && !$user->hasRole('administrator')) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $config = [
                'excluded_brands'     => $request->input('excluded_brands', []),
                'excluded_categories' => $request->input('excluded_categories', []),
            ];

            IncadeaSyncService::setSyncConfig($config);

            return ApiResponseHelper::apiSuccess(200, 'Configuración actualizada exitosamente', ['config' => $config]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar configuración', $e->getMessage(), 500, 'UPDATE_CONFIG_ERROR');
        }
    }
}
