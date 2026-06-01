<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Boutique\BoutiqueGoogleSheetSyncService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoutiqueGoogleSheetController extends Controller
{
    public function __construct(
        private readonly BoutiqueGoogleSheetSyncService $syncService,
    ) {}

    /**
     * GET /api/boutique/admin/google-sheet/template
     */
    public function template(Request $request)
    {
        if (! $this->canManage($request)) {
            return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
        }

        return ApiResponseHelper::apiSuccess(200, 'Plantilla de hoja', [
            'template' => $this->syncService->templateDefinition(),
            'default_sheet_url' => config('boutique.google_sheet.default_url'),
            'default_gid' => config('boutique.google_sheet.default_gid'),
        ]);
    }

    /**
     * POST /api/boutique/admin/google-sheet/preview
     */
    public function preview(Request $request)
    {
        if (! $this->canManage($request)) {
            return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
        }

        $data = $this->validatedSheetRequest($request);

        try {
            $result = $this->syncService->preview(
                $data['sheet_url'] ?? null,
                $data['gid'] ?? null,
                $data['mode']
            );

            return ApiResponseHelper::apiSuccess(200, 'Vista previa generada', $result);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 422, 'INVALID_SHEET_URL');
        } catch (\Throwable $e) {
            return ApiResponseHelper::apiError('Error al leer la hoja', $e->getMessage(), 500, 'SHEET_PREVIEW_ERROR');
        }
    }

    /**
     * POST /api/boutique/admin/google-sheet/sync
     */
    public function sync(Request $request)
    {
        if (! $this->canManage($request)) {
            return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
        }

        $data = $this->validatedSheetRequest($request);

        try {
            $result = $this->syncService->sync(
                $data['sheet_url'] ?? null,
                $data['gid'] ?? null,
                $data['mode'],
                (bool) ($data['dry_run'] ?? false)
            );

            $message = ($data['dry_run'] ?? false)
                ? 'Simulación completada (sin cambios en base de datos)'
                : 'Sincronización completada';

            return ApiResponseHelper::apiSuccess(200, $message, $result);
        } catch (\InvalidArgumentException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 422, 'INVALID_SHEET_URL');
        } catch (\Throwable $e) {
            return ApiResponseHelper::apiError('Error al sincronizar', $e->getMessage(), 500, 'SHEET_SYNC_ERROR');
        }
    }

    private function canManage(Request $request): bool
    {
        $user = $request->user();

        return $user && ($user->hasRole('developer') || $user->hasRole('administrator'));
    }

    /**
     * @return array{sheet_url?: string|null, gid?: string|null, mode: string, dry_run?: bool}
     */
    private function validatedSheetRequest(Request $request): array
    {
        return $request->validate([
            'sheet_url' => 'nullable|string|max:500',
            'gid' => 'nullable|string|max:20',
            'mode' => ['required', 'string', Rule::in(['inventory', 'full'])],
            'dry_run' => 'sometimes|boolean',
        ]);
    }
}
