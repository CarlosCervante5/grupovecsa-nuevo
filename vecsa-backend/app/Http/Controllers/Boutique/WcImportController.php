<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Services\Boutique\BoutiqueProductPublicationService;
use App\Models\Boutique\BoutiqueProductImage;
use App\Models\Boutique\BoutiqueProductVariant;
use App\Services\Boutique\BoutiqueVariantAttributeCatalogSync;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class WcImportController extends Controller
{
    private $categoryCache = [];

    private $parentSkuToId = [];

    /** @var array<int, true> productos padre (variables) tocados en esta importación para sincronizar atributos */
    private array $syncAttributeParentIds = [];

    /**
     * Import products from uploaded WooCommerce CSV.
     * POST /api/boutique/admin/wc-import/upload
     */
    public function upload(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || (!$user->hasRole('developer') && !$user->hasRole('administrator'))) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            if (!$request->hasFile('csv')) {
                return ApiResponseHelper::apiError('No se recibió archivo CSV', null, 400, 'NO_FILE');
            }

            $file = $request->file('csv');
            $path = $file->storeAs('temp_imports', 'wc_import_' . time() . '.csv');
            $fullPath = storage_path('app/' . $path);

            $rows = $this->parseCsv($fullPath);
            $stats = ['categories' => 0, 'products' => 0, 'variants' => 0, 'images' => 0, 'skipped' => 0, 'errors' => 0];
            $errors = [];

            // Pass 1: Categories
            foreach ($rows as $row) {
                if (in_array($row['Tipo'] ?? '', ['simple', 'variable'])) {
                    $this->ensureCategories($row['Categorías'] ?? '', $stats);
                }
            }

            // Pass 2: Products
            foreach ($rows as $row) {
                try {
                    $type = $row['Tipo'] ?? '';
                    if ($type === 'simple') {
                        $this->createSimpleProduct($row, $stats);
                    } elseif ($type === 'variable') {
                        $this->createVariableProduct($row, $stats);
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $errors[] = ['sku' => $row['SKU'] ?? '', 'error' => $e->getMessage()];
                }
            }

            // Pass 3: Variants
            foreach ($rows as $row) {
                try {
                    if (($row['Tipo'] ?? '') === 'variation') {
                        $this->createVariation($row, $stats);
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $errors[] = ['sku' => $row['SKU'] ?? '', 'error' => $e->getMessage()];
                }
            }

            $catalogStats = ['products_touched' => 0, 'variant_links_added' => 0, 'attribute_values_created' => 0];
            $parentIdsForCatalog = array_map('intval', array_keys($this->syncAttributeParentIds));
            if ($parentIdsForCatalog !== []) {
                $catalogStats = (new BoutiqueVariantAttributeCatalogSync)->sync($parentIdsForCatalog);
            }

            // Cleanup temp file
            @unlink($fullPath);

            return ApiResponseHelper::apiSuccess(200, 'Importación completada', [
                'total_rows' => count($rows),
                'categories' => $stats['categories'],
                'products' => $stats['products'],
                'variants' => $stats['variants'],
                'images' => $stats['images'],
                'skipped' => $stats['skipped'],
                'errors' => $stats['errors'],
                'error_details' => array_slice($errors, 0, 20),
                'attribute_catalog_sync' => $catalogStats,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error en la importación', $e->getMessage(), 500, 'IMPORT_ERROR');
        }
    }

    /**
     * Sync images only: link WC image URLs to existing products by SKU.
     * POST /api/boutique/admin/wc-import/sync-images
     */
    public function syncImages(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || (!$user->hasRole('developer') && !$user->hasRole('administrator'))) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            if (!$request->hasFile('csv')) {
                return ApiResponseHelper::apiError('No se recibió archivo CSV', null, 400, 'NO_FILE');
            }

            $file = $request->file('csv');
            $path = $file->storeAs('temp_imports', 'wc_images_' . time() . '.csv');
            $fullPath = storage_path('app/' . $path);

            $rows = $this->parseCsv($fullPath);
            $stats = ['linked' => 0, 'skipped' => 0, 'not_found' => 0, 'errors' => 0];

            foreach ($rows as $row) {
                $sku = trim($row['SKU'] ?? '');
                $images = trim($row['Imágenes'] ?? $row['Images'] ?? '');
                if (!$sku || !$images) { $stats['skipped']++; continue; }

                $product = BoutiqueProduct::where('sku', $sku)->first();
                if (!$product) { $stats['not_found']++; continue; }

                if ($product->images()->count() > 0) { $stats['skipped']++; continue; }

                $urls = array_map('trim', explode(',', $images));
                $order = 0;
                foreach ($urls as $url) {
                    if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) continue;
                    try {
                        BoutiqueProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $url,
                            'sort_id' => $order++,
                            'status' => 'uploaded',
                        ]);
                        $stats['linked']++;
                    } catch (\Exception $e) {
                        $stats['errors']++;
                    }
                }
            }

            @unlink($fullPath);

            return ApiResponseHelper::apiSuccess(200, 'Sincronización de imágenes completada', $stats);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error en sync de imágenes', $e->getMessage(), 500, 'SYNC_IMAGES_ERROR');
        }
    }

    /**
     * Crea atributos Color/Talla y vínculos a partir de variantes ya importadas (p. ej. tras WC sin pasar por upload).
     * POST /api/boutique/admin/wc-import/sync-variant-attributes
     * Body opcional: { "product_ids": [1,2,3] } — si se omite, procesa todas las variantes activas (por lotes).
     */
    public function syncVariantAttributes(Request $request)
    {
        try {
            $user = $request->user();
            if (! $user || (! $user->hasRole('developer') && ! $user->hasRole('administrator'))) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $rawIds = $request->input('product_ids');
            $ids = null;
            if (is_array($rawIds) && $rawIds !== []) {
                $ids = array_values(array_unique(array_filter(array_map('intval', $rawIds))));
            }

            $stats = (new BoutiqueVariantAttributeCatalogSync)->sync($ids);

            return ApiResponseHelper::apiSuccess(200, 'Catálogo de atributos sincronizado', $stats);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al sincronizar atributos', $e->getMessage(), 500, 'SYNC_ATTRIBUTES_ERROR');
        }
    }

    // ── Private helpers ──

    private function parseCsv(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        if ($headers) $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $headers = array_map('trim', $headers);
        $nHeaders = count($headers);
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            // WooCommerce CSV omite columnas vacías al final: fgetcsv devuelve menos celdas que cabeceras.
            $data = array_slice($data, 0, $nHeaders);
            if (count($data) < $nHeaders) {
                $data = array_pad($data, $nHeaders, '');
            }
            $rows[] = array_combine($headers, $data);
        }
        fclose($handle);
        return $rows;
    }

    private function ensureCategories(string $catString, array &$stats): void
    {
        if (!$catString) return;
        foreach (array_map('trim', explode(',', $catString)) as $cat) {
            if (!$cat || isset($this->categoryCache[$cat])) continue;
            $parts = array_map('trim', explode('>', $cat));
            $name = end($parts);
            $existing = BoutiqueCategory::where('name', $name)->first();
            if (!$existing) {
                $existing = BoutiqueCategory::create(['name' => $name, 'active' => true]);
                $stats['categories']++;
            }
            $this->categoryCache[$cat] = $existing->id;
            $this->categoryCache[$name] = $existing->id;
        }
    }

    private function getCategoryId(string $catString): ?int
    {
        if (!$catString) return null;
        foreach (array_reverse(array_map('trim', explode(',', $catString))) as $cat) {
            if (isset($this->categoryCache[$cat])) return $this->categoryCache[$cat];
            $parts = array_map('trim', explode('>', $cat));
            $name = end($parts);
            if (isset($this->categoryCache[$name])) return $this->categoryCache[$name];
        }
        return null;
    }

    private function createSimpleProduct(array $row, array &$stats): void
    {
        $sku = trim($row['SKU'] ?? '');
        if (!$sku) { $stats['skipped']++; return; }
        if (BoutiqueProduct::where('sku', $sku)->exists()) { $stats['skipped']++; return; }

        $product = new BoutiqueProduct([
            'category_id' => $this->getCategoryId($row['Categorías'] ?? ''),
            'name' => $row['Nombre'] ?? 'Sin nombre',
            'description' => $row['Descripción corta'] ?: ($row['Descripción'] ?? ''),
            'price' => $this->parsePrice($row['Precio rebajado'] ?: $row['Precio normal']),
            'sku' => $sku,
            'stock' => (int)($row['Inventario'] ?? 0),
            'active' => false,
        ]);
        $product->uuid = (string) Uuid::uuid4();
        $product->save();

        $this->createImages($product, $row['Imágenes'] ?? '', $stats);
        $product->update([
            'active' => BoutiqueProductPublicationService::resolveActiveFromImportFlag(
                $product,
                ($row['Publicado'] ?? '0') == '1'
            ),
        ]);
        $stats['products']++;
    }

    private function createVariableProduct(array $row, array &$stats): void
    {
        $sku = trim($row['SKU'] ?? '');
        if (!$sku) { $stats['skipped']++; return; }
        if (BoutiqueProduct::where('sku', $sku)->exists()) { $stats['skipped']++; return; }

        $price = $this->parsePrice($row['Meta: _min_variation_regular_price'] ?? $row['Precio normal'] ?? '');
        $product = new BoutiqueProduct([
            'category_id' => $this->getCategoryId($row['Categorías'] ?? ''),
            'name' => $row['Nombre'] ?? 'Sin nombre',
            'description' => $row['Descripción corta'] ?: ($row['Descripción'] ?? ''),
            'price' => $price ?: 0,
            'sku' => $sku,
            'stock' => 0,
            'active' => false,
        ]);
        $product->uuid = (string) Uuid::uuid4();
        $product->save();

        $this->parentSkuToId[$sku] = $product->id;
        $this->syncAttributeParentIds[(int) $product->id] = true;
        $this->createImages($product, $row['Imágenes'] ?? '', $stats);
        $product->update([
            'active' => BoutiqueProductPublicationService::resolveActiveFromImportFlag(
                $product,
                ($row['Publicado'] ?? '0') == '1'
            ),
        ]);
        $stats['products']++;
    }

    private function createVariation(array $row, array &$stats): void
    {
        $parentSku = trim($row['Superior'] ?? '');
        if (!$parentSku) { $stats['skipped']++; return; }

        $productId = $this->parentSkuToId[$parentSku] ?? null;
        if (!$productId) {
            $parent = BoutiqueProduct::where('sku', $parentSku)->first();
            if ($parent) { $productId = $parent->id; $this->parentSkuToId[$parentSku] = $productId; }
            else { $stats['skipped']++; return; }
        }

        $size = ''; $color = '';
        for ($i = 1; $i <= 8; $i++) {
            $attrName = strtolower(trim($row["Nombre del atributo $i"] ?? ''));
            $attrValue = trim($row["Valor(es) del atributo $i"] ?? '');
            if (!$attrName || !$attrValue) continue;
            if (in_array($attrName, ['talla', 'size'])) $size = $attrValue;
            if (in_array($attrName, ['color'])) $color = $attrValue;
        }

        $sku = trim($row['SKU'] ?? '') ?: $parentSku . '-' . ($size ?: 'v') . ($color ? '-' . $color : '');
        if (BoutiqueProductVariant::where('sku', $sku)->where('product_id', $productId)->exists()) {
            $stats['skipped']++; return;
        }

        BoutiqueProductVariant::create([
            'product_id' => $productId, 'color' => $color ?: null, 'size' => $size ?: null,
            'sku' => $sku, 'stock' => (int)($row['Inventario'] ?? 0), 'active' => true,
        ]);
        $this->syncAttributeParentIds[(int) $productId] = true;
        $stats['variants']++;
    }

    private function createImages(BoutiqueProduct $product, string $imageString, array &$stats): void
    {
        if (!$imageString) return;
        $order = 0;
        foreach (array_map('trim', explode(',', $imageString)) as $url) {
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) continue;
            BoutiqueProductImage::create([
                'product_id' => $product->id, 'image_path' => $url,
                'sort_id' => $order++, 'status' => 'uploaded',
            ]);
            $stats['images']++;
        }
    }

    private function parsePrice($value): float
    {
        if (!$value) return 0;
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    /**
     * Deactivate products with no stock or no images.
     * POST /api/boutique/admin/wc-import/cleanup
     */
    public function cleanup(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || (!$user->hasRole('developer') && !$user->hasRole('administrator'))) {
                return ApiResponseHelper::apiError('No autorizado', null, 403, 'UNAUTHORIZED');
            }

            $noStock = 0;
            $noImages = 0;
            $alreadyInactive = 0;

            $products = BoutiqueProduct::where('active', true)->get();

            foreach ($products as $product) {
                $hasStock = $product->stock > 0;

                // Check variant stock too
                if (!$hasStock) {
                    $variantStock = $product->allVariants()->sum('stock');
                    $hasStock = $variantStock > 0;
                }

                $hasImages = BoutiqueProductPublicationService::hasPublishableImage($product);

                if (!$hasStock && !$hasImages) {
                    $product->update(['active' => false]);
                    $noStock++;
                    $noImages++;
                } elseif (!$hasStock) {
                    $product->update(['active' => false]);
                    $noStock++;
                } elseif (!$hasImages) {
                    $product->update(['active' => false]);
                    $noImages++;
                }
            }

            $totalActive = BoutiqueProduct::where('active', true)->count();
            $totalInactive = BoutiqueProduct::where('active', false)->count();

            return ApiResponseHelper::apiSuccess(200, 'Limpieza completada', [
                'deactivated_no_stock' => $noStock,
                'deactivated_no_images' => $noImages,
                'total_active' => $totalActive,
                'total_inactive' => $totalInactive,
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error en limpieza', $e->getMessage(), 500, 'CLEANUP_ERROR');
        }
    }
}
