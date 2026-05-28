<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Services\Boutique\BoutiqueProductPublicationService;
use App\Support\BoutiqueDealershipPresenter;
use Illuminate\Http\Request;

class BoutiqueCatalogController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = BoutiqueProduct::published()
                ->with([
                    'category',
                    'dealership',
                    'images' => function ($q) {
                        $q->where('status', 'uploaded')->orderBy('sort_id')->limit(1);
                    },
                ]);

            if ($request->filled('category_uuid')) {
                $category = BoutiqueCategory::findByUuid($request->input('category_uuid'));
                if ($category) {
                    $categoryIds = BoutiqueCategory::idsSelfAndDescendants((int) $category->id);
                    $query->whereIn('category_id', $categoryIds);
                }
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->input('min_price'));
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->input('max_price'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $total = $query->count();
            $paginator = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            $paginator->getCollection()->transform(function (BoutiqueProduct $product) {
                $arr = $product->toArray();
                $arr['dealership'] = BoutiqueDealershipPresenter::catalogSummary($product->dealership);
                $arr = array_merge($arr, BoutiqueProductPublicationService::catalogPresentation($product));

                return $arr;
            });

            return ApiResponseHelper::apiSuccess(200, 'Catálogo obtenido exitosamente', [
                'products' => $paginator,
                'total' => $total,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el catálogo', $e->getMessage(), 500, 'GET_CATALOG_ERROR');
        }
    }

    public function detail(Request $request)
    {
        try {
            $uuid = $request->input('uuid');

            $product = BoutiqueProduct::published()
                ->where('uuid', $uuid)
                ->with([
                    'category',
                    'dealership',
                    'images' => function ($q) {
                        $q->where('status', 'uploaded')->orderBy('sort_id');
                    },
                    'attributes.values',
                    'variants' => function ($q) {
                        $q->where('active', true);
                    },
                    'variants.attributeValues.attribute',
                ])
                ->first();

            if (!$product) {
                return ApiResponseHelper::apiError('El producto no existe', null, 404, 'PRODUCT_NOT_FOUND');
            }

            // Transform variants to include effective_price and readable description
            $productArray = $product->toArray();
            $productArray['dealership'] = BoutiqueDealershipPresenter::catalogSummary($product->dealership);
            if (isset($productArray['variants'])) {
                foreach ($productArray['variants'] as &$variant) {
                    // Effective price: variant price or product price
                    $variant['effective_price'] = $variant['price'] !== null
                        ? $variant['price']
                        : $product->price;

                    // Readable description of the combination (e.g., "Rojo / M")
                    $description = '';
                    if (isset($variant['attribute_values']) && is_array($variant['attribute_values'])) {
                        $parts = [];
                        foreach ($variant['attribute_values'] as &$av) {
                            $parts[] = $av['value'];
                            // Include attribute_name for frontend convenience
                            $av['attribute_name'] = $av['attribute']['name'] ?? null;
                        }
                        $description = implode(' / ', $parts);
                    }
                    $variant['combination_description'] = $description;
                }
            }

            // Related products: same category, exclude current, limit 4
            $relatedProducts = BoutiqueProduct::published()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with(['images' => function ($q) {
                    $q->where('status', 'uploaded')->orderBy('sort_id')->limit(1);
                }])
                ->limit(4)
                ->get();

            return ApiResponseHelper::apiSuccess(200, 'Detalle del producto obtenido exitosamente', [
                'product' => $productArray,
                'related_products' => $relatedProducts,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle del producto', $e->getMessage(), 500, 'GET_PRODUCT_DETAIL_ERROR');
        }
    }

    public function categories()
    {
        try {
            $productCategoryIds = BoutiqueProduct::query()
                ->published()
                ->whereNotNull('category_id')
                ->distinct()
                ->pluck('category_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $qualifiedIds = $this->categoryIdsWithProductsOrAncestors($productCategoryIds);

            if ($qualifiedIds === []) {
                return ApiResponseHelper::apiSuccess(200, 'Categorías obtenidas exitosamente', ['categories' => collect()]);
            }

            $categories = BoutiqueCategory::where('active', true)
                ->whereIn('id', $qualifiedIds)
                ->with(['children' => function ($q) use ($qualifiedIds) {
                    $q->where('active', true)
                        ->whereIn('id', $qualifiedIds)
                        ->orderBy('name');
                }])
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get();

            return ApiResponseHelper::apiSuccess(200, 'Categorías obtenidas exitosamente', ['categories' => $categories]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las categorías', $e->getMessage(), 500, 'GET_CATEGORIES_ERROR');
        }
    }

    /**
     * IDs de categorías visibles en el catálogo público: toda categoría con al menos un producto
     * activo, más sus ancestros activos (para conservar la jerarquía en el menú).
     *
     * @param  int[]  $productCategoryIds  category_id distintos de productos activos
     * @return int[]
     */
    private function categoryIdsWithProductsOrAncestors(array $productCategoryIds): array
    {
        if ($productCategoryIds === []) {
            return [];
        }

        $qualified = [];
        foreach ($productCategoryIds as $cid) {
            $qualified[(int) $cid] = true;
        }

        foreach (array_keys($qualified) as $cid) {
            $walker = BoutiqueCategory::query()->where('active', true)->where('id', $cid)->first();
            while ($walker && $walker->parent_id) {
                $pid = (int) $walker->parent_id;
                $qualified[$pid] = true;
                $walker = BoutiqueCategory::query()->where('active', true)->where('id', $pid)->first();
            }
        }

        return array_values(array_map('intval', array_keys($qualified)));
    }
}
