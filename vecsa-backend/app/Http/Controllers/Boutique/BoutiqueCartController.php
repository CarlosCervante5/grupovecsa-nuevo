<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\AddToCartRequest;
use App\Http\Requests\Boutique\RemoveCartItemRequest;
use App\Http\Requests\Boutique\UpdateCartItemRequest;
use App\Models\Boutique\BoutiqueCart;
use App\Models\Boutique\BoutiqueCartItem;
use App\Models\Boutique\BoutiqueProduct;
use App\Services\Boutique\BoutiqueProductPublicationService;
use App\Support\BoutiqueDealershipPresenter;
use Illuminate\Http\Request;

class BoutiqueCartController extends Controller
{
    public function get(Request $request)
    {
        try {
            $user = $request->user();

            $cart = BoutiqueCart::where('user_id', $user->id)
                ->with(['items' => function ($q) {
                    $q->with(['product' => function ($pq) {
                        $pq->with([
                            'dealership',
                            'images' => function ($iq) {
                                $iq->where('status', 'uploaded')->orderBy('sort_id')->limit(1);
                            },
                        ]);
                    }]);
                }])
                ->first();

            if (!$cart) {
                return ApiResponseHelper::apiSuccess(200, 'Carrito obtenido exitosamente', [
                    'cart' => null,
                    'items' => [],
                    'total' => 0,
                ]);
            }

            $items = $cart->items->map(function ($item) {
                $product = $item->product;
                $productArr = $product->toArray();
                $productArr['dealership'] = BoutiqueDealershipPresenter::checkoutSummary($product->dealership)
                    ?? BoutiqueDealershipPresenter::catalogSummary($product->dealership);

                return [
                    'uuid' => $item->uuid,
                    'product' => $productArr,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->price,
                    'subtotal' => round($item->quantity * $item->product->price, 2),
                ];
            });

            $total = $items->sum('subtotal');

            return ApiResponseHelper::apiSuccess(200, 'Carrito obtenido exitosamente', [
                'cart' => $cart,
                'items' => $items,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el carrito', $e->getMessage(), 500, 'GET_CART_ERROR');
        }
    }

    public function add(AddToCartRequest $request)
    {
        try {
            $data = $request->validated();
            $user = $request->user();

            $product = BoutiqueProduct::findByUuid($data['product_uuid']);
            if (!$product) {
                return ApiResponseHelper::apiError('El producto no existe', null, 404, 'PRODUCT_NOT_FOUND');
            }

            if (! BoutiqueProductPublicationService::isPublished($product)) {
                return ApiResponseHelper::apiError(
                    'Producto no disponible',
                    'Este producto no está publicado en la boutique.',
                    400,
                    'PRODUCT_NOT_PUBLISHED'
                );
            }

            // Find or create cart
            $cart = BoutiqueCart::firstOrCreate(['user_id' => $user->id]);

            // Check if product already in cart
            $cartItem = BoutiqueCartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $requestedQty = $data['quantity'];

            if ($cartItem) {
                $newQty = $cartItem->quantity + $requestedQty;
                // Limit to available stock
                $newQty = min($newQty, $product->stock);
                $cartItem->update(['quantity' => $newQty]);
            } else {
                // Limit to available stock
                $qty = min($requestedQty, $product->stock);
                $cartItem = BoutiqueCartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                ]);
            }

            // Return updated cart
            return $this->get($request);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al agregar al carrito', $e->getMessage(), 500, 'ADD_TO_CART_ERROR');
        }
    }

    public function update(UpdateCartItemRequest $request)
    {
        try {
            $data = $request->validated();

            $cartItem = BoutiqueCartItem::findByUuid($data['item_uuid']);
            if (!$cartItem) {
                return ApiResponseHelper::apiError('El item del carrito no existe', null, 404, 'CART_ITEM_NOT_FOUND');
            }

            $product = $cartItem->product;
            $qty = min($data['quantity'], $product->stock);

            $cartItem->update(['quantity' => $qty]);

            return $this->get($request);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el carrito', $e->getMessage(), 500, 'UPDATE_CART_ERROR');
        }
    }

    public function remove(RemoveCartItemRequest $request)
    {
        try {
            $data = $request->validated();

            $cartItem = BoutiqueCartItem::findByUuid($data['item_uuid']);
            if (!$cartItem) {
                return ApiResponseHelper::apiError('El item del carrito no existe', null, 404, 'CART_ITEM_NOT_FOUND');
            }

            $cartItem->delete();

            return ApiResponseHelper::apiSuccess(200, 'Item eliminado del carrito exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar del carrito', $e->getMessage(), 500, 'REMOVE_CART_ERROR');
        }
    }
}
