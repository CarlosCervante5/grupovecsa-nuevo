<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\UpdateInventoryRequest;
use App\Models\Boutique\BoutiqueInventoryMovement;
use App\Models\Boutique\BoutiqueProduct;
use App\Services\Boutique\BoutiqueInventoryService;
use App\Services\DealershipAccessService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class BoutiqueInventoryController extends Controller
{
    protected BoutiqueInventoryService $inventoryService;

    public function __construct(
        BoutiqueInventoryService $inventoryService,
        protected DealershipAccessService $dealershipAccess,
    ) {
        $this->inventoryService = $inventoryService;
    }

    public function update(UpdateInventoryRequest $request)
    {
        try {
            $data = $request->validated();

            $product = BoutiqueProduct::findByUuid($data['product_uuid']);
            if (! $product) {
                return ApiResponseHelper::apiError('El producto no existe', null, 404, 'PRODUCT_NOT_FOUND');
            }

            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);

            $movement = $this->inventoryService->manualAdjust($product, $data['new_stock'], $data['reason']);

            return ApiResponseHelper::apiSuccess(200, 'Inventario actualizado exitosamente', [
                'product' => $product->fresh(),
                'movement' => $movement,
            ]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el inventario', $e->getMessage(), 500, 'UPDATE_INVENTORY_ERROR');
        }
    }

    public function movements(Request $request)
    {
        try {
            $productUuid = $request->input('product_uuid');

            $product = BoutiqueProduct::findByUuid($productUuid);
            if (! $product) {
                return ApiResponseHelper::apiError('El producto no existe', null, 404, 'PRODUCT_NOT_FOUND');
            }

            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);

            $movements = BoutiqueInventoryMovement::where('product_id', $product->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return ApiResponseHelper::apiSuccess(200, 'Movimientos de inventario obtenidos exitosamente', [
                'movements' => $movements,
            ]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los movimientos de inventario', $e->getMessage(), 500, 'GET_MOVEMENTS_ERROR');
        }
    }
}
