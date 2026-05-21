<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Dealership;
use App\Support\BoutiqueDealershipPresenter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoutiqueDealershipController extends Controller
{
    /**
     * Listado para panel tienda boutique (WhatsApp ventas + asignación de productos).
     */
    public function listForAdmin(Request $request)
    {
        try {
            $dealerships = Dealership::query()
                ->orderBy('name')
                ->get(['id', 'name', 'location', 'state', 'phone', 'whatsapp_phone']);

            $rows = $dealerships->map(fn (Dealership $d) => BoutiqueDealershipPresenter::checkoutSummary($d));

            return ApiResponseHelper::apiSuccess(200, 'Sucursales obtenidas', ['dealerships' => $rows]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener sucursales', $e->getMessage(), 500);
        }
    }

    public function updateWhatsapp(Request $request)
    {
        try {
            $table = (new Dealership)->getTable();
            $data = $request->validate([
                'id' => ['required', 'integer', Rule::exists($table, 'id')],
                'whatsapp_phone' => 'nullable|string|max:50',
            ]);

            $dealership = Dealership::findOrFail($data['id']);
            $dealership->update([
                'whatsapp_phone' => isset($data['whatsapp_phone']) ? trim((string) $data['whatsapp_phone']) : null,
            ]);

            return ApiResponseHelper::apiSuccess(200, 'WhatsApp de sucursal actualizado', [
                'dealership' => BoutiqueDealershipPresenter::checkoutSummary($dealership->fresh()),
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar WhatsApp', $e->getMessage(), 500);
        }
    }
}
