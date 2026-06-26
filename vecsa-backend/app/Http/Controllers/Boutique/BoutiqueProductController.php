<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\DeleteBoutiqueProductRequest;
use App\Http\Requests\Boutique\StoreBoutiqueProductRequest;
use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductAttribute;
use App\Models\Boutique\BoutiqueProductAttributeValue;
use App\Models\Boutique\BoutiqueProductVariant;
use App\Models\Boutique\BoutiqueVariantAttributeValue;
use App\Services\Boutique\BoutiqueInventoryCsvExportService;
use App\Services\Boutique\BoutiqueProductPublicationService;
use App\Services\DealershipAccessService;
use App\Support\BoutiqueDealershipPresenter;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BoutiqueProductController extends Controller
{
    /** Relaciones de categoría con ancestros para selects en cascada y listados. */
    private const CATEGORY_RELATIONS = ['category.parent.parent'];

    public function __construct(
        protected DealershipAccessService $dealershipAccess,
        protected BoutiqueInventoryCsvExportService $inventoryCsvExport,
    ) {}

    public function search(Request $request)
    {
        try {
            $query = BoutiqueProduct::with(array_merge(self::CATEGORY_RELATIONS, [
                'dealership',
                'images' => function ($q) {
                    $q->orderBy('sort_id')->limit(1);
                },
            ]));

            $scopeIds = $this->dealershipAccess->inventoryDealershipIds($request->user());
            $productsTable = (new BoutiqueProduct)->getTable();
            if ($scopeIds !== null && Schema::hasColumn($productsTable, 'dealership_id')) {
                $query->whereIn('dealership_id', $scopeIds);
            }

            if ($request->filled('category_uuid')) {
                $category = BoutiqueCategory::findByUuid($request->input('category_uuid'));
                if ($category) {
                    $categoryIds = BoutiqueCategory::idsSelfAndDescendants((int) $category->id);
                    $query->whereIn('category_id', $categoryIds);
                }
            }

            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->input('per_page', 15);
            $perPage = max(1, min($perPage, 100));
            $page = max(1, (int) $request->input('page', 1));

            $products = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            $products->getCollection()->transform(function ($product) {
                $product->low_stock = $product->stock <= 5;
                if ($product->relationLoaded('dealership')) {
                    $product->setAttribute(
                        'dealership',
                        BoutiqueDealershipPresenter::catalogSummary($product->dealership)
                    );
                }

                return $product;
            });

            return ApiResponseHelper::apiSuccess(200, 'Productos obtenidos exitosamente', ['products' => $products]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener productos', $e->getMessage(), 500, 'GET_PRODUCTS_ERROR');
        }
    }

    /**
     * POST /api/boutique/admin/products/detail
     */
    public function detail(DeleteBoutiqueProductRequest $request)
    {
        try {
            $data = $request->validated();

            $product = BoutiqueProduct::findByUuid($data['uuid']);
            if (! $product) {
                return ApiResponseHelper::apiError('El producto no existe', 'No existe el uuid: '.$data['uuid'], 404, 'PRODUCT_NOT_FOUND');
            }

            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);

            $product->load(array_merge(self::CATEGORY_RELATIONS, [
                'dealership',
                'images' => function ($q) {
                    $q->orderBy('sort_id');
                },
                'attributes.values',
                'variants.attributeValues.attribute',
            ]));

            $product->low_stock = $product->stock <= 5;
            if ($product->relationLoaded('dealership')) {
                $product->setAttribute(
                    'dealership',
                    BoutiqueDealershipPresenter::catalogSummary($product->dealership)
                );
            }

            return ApiResponseHelper::apiSuccess(200, 'Detalle del producto obtenido exitosamente', ['product' => $product]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener el detalle del producto', $e->getMessage(), 500, 'GET_PRODUCT_DETAIL_ERROR');
        }
    }

    /**
     * POST /api/boutique/admin/products/export-csv
     * Mismos filtros que search (search, category_uuid, active). Descarga inventario completo filtrado.
     */
    public function exportCsv(Request $request)
    {
        try {
            return $this->inventoryCsvExport->streamDownload($request);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al exportar inventario', $e->getMessage(), 500, 'EXPORT_INVENTORY_CSV_ERROR');
        }
    }

    public function store(StoreBoutiqueProductRequest $request)
    {
        try {
            $data = $request->validated();

            $category = BoutiqueCategory::findByUuid($data['category_uuid']);
            if (! $category) {
                return ApiResponseHelper::apiError('La categoría no existe', null, 404, 'CATEGORY_NOT_FOUND');
            }

            // Validate SKU uniqueness among active products
            $existingSku = BoutiqueProduct::where('sku', $data['sku'])
                ->where('active', true)
                ->first();

            if ($existingSku) {
                return ApiResponseHelper::apiError('El SKU ya existe en otro producto activo', null, 400, 'SKU_ALREADY_EXISTS');
            }

            $dealershipId = $this->dealershipAccess->resolveDealershipIdForNewBoutiqueProduct($request);

            $requestedActive = (bool) ($data['active'] ?? false);
            if ($requestedActive) {
                return ApiResponseHelper::apiError(
                    'El producto no puede publicarse sin imagen',
                    'Sube al menos una imagen antes de activar el producto.',
                    400,
                    'PRODUCT_PUBLISH_REQUIRES_IMAGE'
                );
            }

            $product = BoutiqueProduct::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'sku' => $data['sku'],
                'category_id' => $category->id,
                'dealership_id' => $dealershipId,
                'stock' => $data['stock'] ?? 0,
                'active' => false,
            ]);

            // Sync attributes if provided
            if ($request->has('attributes') && is_array($request->input('attributes'))) {
                $this->syncProductAttributes($product, $request->input('attributes'));
            }

            $product->load(self::CATEGORY_RELATIONS);

            return ApiResponseHelper::apiSuccess(201, 'Producto creado exitosamente', ['product' => $product]);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el producto', $e->getMessage(), 500, 'CREATE_PRODUCT_ERROR');
        }
    }

    public function update(StoreBoutiqueProductRequest $request)
    {
        try {
            $data = $request->validated();
            $uuid = $request->input('uuid');

            $product = BoutiqueProduct::findByUuid($uuid);
            if (! $product) {
                return ApiResponseHelper::apiError('El producto no existe', 'No existe el uuid: '.$uuid, 404, 'PRODUCT_NOT_FOUND');
            }

            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);

            $category = BoutiqueCategory::findByUuid($data['category_uuid']);
            if (! $category) {
                return ApiResponseHelper::apiError('La categoría no existe', null, 404, 'CATEGORY_NOT_FOUND');
            }

            // Validate SKU uniqueness excluding current product
            $existingSku = BoutiqueProduct::where('sku', $data['sku'])
                ->where('active', true)
                ->where('id', '!=', $product->id)
                ->first();

            if ($existingSku) {
                return ApiResponseHelper::apiError('El SKU ya existe en otro producto activo', null, 400, 'SKU_ALREADY_EXISTS');
            }

            $dealershipId = $product->dealership_id;
            if ($this->dealershipAccess->inventoryDealershipIds($request->user()) === null && array_key_exists('dealership_id', $data)) {
                $dealershipId = $data['dealership_id'] ?? null;
            }

            $requestedActive = array_key_exists('active', $data)
                ? (bool) $data['active']
                : (bool) $product->active;

            $publishError = BoutiqueProductPublicationService::validateActivation($product, $requestedActive);
            if ($publishError !== null) {
                return ApiResponseHelper::apiError('No se puede publicar el producto', $publishError, 400, 'PRODUCT_PUBLISH_REQUIRES_IMAGE');
            }

            $product->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'sku' => $data['sku'],
                'category_id' => $category->id,
                'dealership_id' => $dealershipId,
                'stock' => $data['stock'] ?? $product->stock,
                'active' => $requestedActive,
            ]);

            // Sync attributes if provided
            if ($request->has('attributes') && is_array($request->input('attributes'))) {
                $this->syncProductAttributes($product, $request->input('attributes'));
            }

            $product->load(self::CATEGORY_RELATIONS);

            return ApiResponseHelper::apiSuccess(200, 'Producto actualizado exitosamente', ['product' => $product]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el producto', $e->getMessage(), 500, 'UPDATE_PRODUCT_ERROR');
        }
    }

    public function delete(DeleteBoutiqueProductRequest $request)
    {
        try {
            $data = $request->validated();

            $product = BoutiqueProduct::findByUuid($data['uuid']);

            if (! $product) {
                return ApiResponseHelper::apiError('El producto no existe', 'No existe el uuid: '.$data['uuid'], 404, 'PRODUCT_NOT_FOUND');
            }

            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);

            $product->delete();

            return ApiResponseHelper::apiSuccess(200, 'Producto eliminado exitosamente');
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el producto', $e->getMessage(), 500, 'DELETE_PRODUCT_ERROR');
        }
    }

    public function generateVariants(Request $request)
    {
        try {
            $productUuid = $request->input('product_uuid');
            $attributeConfigs = $request->input('attributes', []);

            $product = BoutiqueProduct::findByUuid($productUuid);
            if (! $product) {
                return ApiResponseHelper::apiError('El producto no existe', null, 404, 'PRODUCT_NOT_FOUND');
            }

            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);

            // Resolve attribute IDs and sync product-attribute pivot
            $attributeIds = [];
            $valueSets = [];

            foreach ($attributeConfigs as $config) {
                $attribute = BoutiqueProductAttribute::findByUuid($config['attribute_uuid']);
                if (! $attribute) {
                    return ApiResponseHelper::apiError('El atributo no existe', null, 404, 'ATTRIBUTE_NOT_FOUND');
                }
                $attributeIds[] = $attribute->id;

                $values = BoutiqueProductAttributeValue::whereIn('uuid', $config['value_uuids'] ?? [])
                    ->where('attribute_id', $attribute->id)
                    ->get();

                if ($values->isEmpty()) {
                    continue;
                }

                $valueSets[] = $values;
            }

            // Sync product-attribute pivot table
            $product->attributes()->sync($attributeIds);

            // If no value sets, remove all existing variants and return empty
            if (empty($valueSets)) {
                $existingVariants = $product->allVariants()->get();
                foreach ($existingVariants as $variant) {
                    BoutiqueVariantAttributeValue::where('variant_id', $variant->id)->delete();
                    $variant->delete();
                }

                return ApiResponseHelper::apiSuccess(200, 'Variantes generadas exitosamente', ['variants' => []]);
            }

            // Calculate cartesian product
            $combinations = [[]];
            foreach ($valueSets as $valueSet) {
                $newCombinations = [];
                foreach ($combinations as $combo) {
                    foreach ($valueSet as $value) {
                        $newCombinations[] = array_merge($combo, [$value]);
                    }
                }
                $combinations = $newCombinations;
            }

            // Validate limit of 100 combinations
            if (count($combinations) > 100) {
                return ApiResponseHelper::apiError(
                    'El número de combinaciones supera el límite de 100 variantes',
                    null,
                    400,
                    'VARIANT_LIMIT_EXCEEDED'
                );
            }

            // Get existing variants with their attribute value IDs
            $existingVariants = $product->allVariants()->with('attributeValues')->get();
            $existingMap = [];
            foreach ($existingVariants as $variant) {
                $key = $variant->attributeValues->pluck('id')->sort()->values()->implode('-');
                $existingMap[$key] = $variant;
            }

            // Create/preserve variants
            $newValueIdKeys = [];
            $resultVariants = [];

            foreach ($combinations as $combo) {
                $valueIds = collect($combo)->pluck('id')->sort()->values();
                $key = $valueIds->implode('-');
                $newValueIdKeys[] = $key;

                if (isset($existingMap[$key])) {
                    // Preserve existing variant
                    $resultVariants[] = $existingMap[$key];
                } else {
                    // Create new variant with auto-generated SKU
                    $skuParts = collect($combo)->pluck('value')->map(fn ($v) => Str::slug($v));
                    $sku = $product->sku.'-'.$skuParts->implode('-');

                    $variant = BoutiqueProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'price' => null,
                        'stock' => 0,
                        'active' => true,
                    ]);

                    // Create pivot records
                    foreach ($combo as $value) {
                        BoutiqueVariantAttributeValue::create([
                            'variant_id' => $variant->id,
                            'attribute_value_id' => $value->id,
                        ]);
                    }

                    $resultVariants[] = $variant;
                }
            }

            // Delete variants whose combination no longer applies
            foreach ($existingMap as $key => $variant) {
                if (! in_array($key, $newValueIdKeys)) {
                    BoutiqueVariantAttributeValue::where('variant_id', $variant->id)->delete();
                    $variant->delete();
                }
            }

            // Reload variants with attributeValues
            $variants = $product->allVariants()->with('attributeValues.attribute')->get();

            return ApiResponseHelper::apiSuccess(200, 'Variantes generadas exitosamente', ['variants' => $variants]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al generar variantes', $e->getMessage(), 500, 'GENERATE_VARIANTS_ERROR');
        }
    }

    public function updateVariant(Request $request)
    {
        try {
            $variantUuid = $request->input('variant_uuid');

            $variant = BoutiqueProductVariant::where('uuid', $variantUuid)->first();
            if (! $variant) {
                return ApiResponseHelper::apiError('La variante no existe', null, 404, 'VARIANT_NOT_FOUND');
            }

            $product = BoutiqueProduct::find($variant->product_id);
            if ($product) {
                $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);
            }

            // Validate SKU uniqueness among active variants of same product
            if ($request->has('sku') && $request->input('sku') !== null) {
                $duplicateSku = BoutiqueProductVariant::where('product_id', $variant->product_id)
                    ->where('sku', $request->input('sku'))
                    ->where('active', true)
                    ->where('id', '!=', $variant->id)
                    ->first();

                if ($duplicateSku) {
                    return ApiResponseHelper::apiError(
                        'El SKU ya existe en otra variante activa del producto',
                        null,
                        400,
                        'VARIANT_SKU_DUPLICATE'
                    );
                }
            }

            $updateData = [];
            if ($request->has('sku')) {
                $updateData['sku'] = $request->input('sku');
            }
            if ($request->has('price')) {
                $updateData['price'] = $request->input('price');
            }
            if ($request->has('stock')) {
                $updateData['stock'] = $request->input('stock');
            }
            if ($request->has('active')) {
                $updateData['active'] = $request->boolean('active');
            }

            $variant->update($updateData);

            $variant->load('attributeValues.attribute');

            return ApiResponseHelper::apiSuccess(200, 'Variante actualizada exitosamente', ['variant' => $variant]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la variante', $e->getMessage(), 500, 'UPDATE_VARIANT_ERROR');
        }
    }

    public function deleteVariant(Request $request)
    {
        try {
            $variantUuid = $request->input('variant_uuid');

            $variant = BoutiqueProductVariant::where('uuid', $variantUuid)->first();
            if (! $variant) {
                return ApiResponseHelper::apiError('La variante no existe', null, 404, 'VARIANT_NOT_FOUND');
            }

            $product = BoutiqueProduct::find($variant->product_id);
            if ($product) {
                $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $product->dealership_id);
            }

            // Delete variant (FK cascade handles pivot records)
            $variant->delete();

            return ApiResponseHelper::apiSuccess(200, 'Variante eliminada exitosamente');
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'INVENTORY_FORBIDDEN');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la variante', $e->getMessage(), 500, 'DELETE_VARIANT_ERROR');
        }
    }

    /**
     * Sync product-attribute assignments from an array of attribute UUIDs.
     */
    private function syncProductAttributes(BoutiqueProduct $product, array $attributeUuids): void
    {
        $attributeIds = [];
        foreach ($attributeUuids as $uuid) {
            $attribute = BoutiqueProductAttribute::findByUuid($uuid);
            if ($attribute) {
                $attributeIds[] = $attribute->id;
            }
        }
        $product->attributes()->sync($attributeIds);
    }
}
