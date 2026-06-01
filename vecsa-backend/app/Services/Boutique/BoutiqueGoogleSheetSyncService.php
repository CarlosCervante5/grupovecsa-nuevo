<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use App\Models\Boutique\BoutiqueProductVariant;
use App\Support\CsvTableReader;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class BoutiqueGoogleSheetSyncService
{
    /** @var array<string, int> */
    private array $categoryCache = [];

    /** @var array<string, int> */
    private array $parentSkuToId = [];

    /** @var array<int, true> */
    private array $syncAttributeParentIds = [];

    public function __construct(
        private readonly BoutiqueGoogleSheetUrlResolver $urlResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function templateDefinition(): array
    {
        return [
            'title' => 'Inventario Boutique — Google Sheets',
            'instructions' => [
                'Publica la hoja en la web o compártela con enlace (cualquiera con el enlace puede ver).',
                'La primera fila debe ser encabezados. El SKU es obligatorio en cada fila con datos.',
                'Modo inventario: actualiza stock, precio y publicado de productos/variantes existentes.',
                'Modo completo: crea o actualiza productos, variantes, categorías e imágenes por URL.',
            ],
            'columns' => [
                ['key' => 'sku', 'required' => true, 'example' => 'BMW-MALETA-001'],
                ['key' => 'tipo', 'required' => false, 'example' => 'producto | variable | variante'],
                ['key' => 'sku_padre', 'required' => false, 'example' => 'BMW-MALETA-001 (solo variante)'],
                ['key' => 'nombre', 'required' => false, 'example' => 'Maleta BMW'],
                ['key' => 'descripcion', 'required' => false, 'example' => 'Descripción corta'],
                ['key' => 'categorias', 'required' => false, 'example' => 'Accesorios > BMW'],
                ['key' => 'precio', 'required' => false, 'example' => '1299.00'],
                ['key' => 'stock', 'required' => false, 'example' => '5'],
                ['key' => 'publicado', 'required' => false, 'example' => 'si | no | 1 | 0'],
                ['key' => 'imagenes', 'required' => false, 'example' => 'https://.../foto1.jpg, https://.../foto2.jpg'],
                ['key' => 'talla', 'required' => false, 'example' => 'L'],
                ['key' => 'color', 'required' => false, 'example' => 'Negro'],
            ],
            'aliases' => [
                'sku' => ['sku', 'código', 'codigo'],
                'tipo' => ['tipo', 'type'],
                'sku_padre' => ['sku_padre', 'sku padre', 'parent_sku', 'superior'],
                'nombre' => ['nombre', 'name'],
                'descripcion' => ['descripcion', 'descripción', 'description'],
                'categorias' => ['categorias', 'categorías', 'categories', 'categoria'],
                'precio' => ['precio', 'price', 'precio normal'],
                'stock' => ['stock', 'inventario'],
                'publicado' => ['publicado', 'published', 'active', 'activo'],
                'imagenes' => ['imagenes', 'imágenes', 'images', 'imagen_url'],
                'talla' => ['talla', 'size'],
                'color' => ['color'],
            ],
        ];
    }

    /**
     * @return array{headers: list<string>, rows: list<array<string, string>>, export_url: string}
     */
    public function fetchSheet(?string $sheetUrl, ?string $gid): array
    {
        $exportUrl = $this->urlResolver->resolveExportCsvUrl($sheetUrl, $gid);
        $response = Http::timeout(45)
            ->withHeaders(['User-Agent' => 'VECSA-Boutique-Sheet-Sync/1.0'])
            ->get($exportUrl);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'No se pudo descargar la hoja (HTTP '.$response->status().'). Verifica que esté publicada o accesible.'
            );
        }

        $body = (string) $response->body();
        if (trim($body) === '') {
            throw new \RuntimeException('La hoja está vacía o no se exportó como CSV.');
        }

        $parsed = CsvTableReader::fromString($body);

        return [
            'headers' => $parsed['headers'],
            'rows' => $parsed['rows'],
            'export_url' => $exportUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function preview(?string $sheetUrl, ?string $gid, string $mode = 'inventory'): array
    {
        $sheet = $this->fetchSheet($sheetUrl, $gid);
        $normalized = array_map(fn (array $row) => $this->normalizeRow($row), $sheet['rows']);

        $stats = [
            'total_rows' => count($normalized),
            'would_update' => 0,
            'would_create' => 0,
            'would_skip' => 0,
            'not_found' => 0,
        ];
        $samples = [];

        foreach ($normalized as $row) {
            $action = $this->classifyRow($row, $mode);
            $stats[$action]++;
            if (count($samples) < 12) {
                $samples[] = ['sku' => $row['sku'] ?? '', 'action' => $action, 'tipo' => $row['tipo'] ?? ''];
            }
        }

        return [
            'export_url' => $sheet['export_url'],
            'headers_detected' => $sheet['headers'],
            'mode' => $mode,
            'stats' => $stats,
            'sample' => $samples,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sync(?string $sheetUrl, ?string $gid, string $mode = 'inventory', bool $dryRun = false): array
    {
        $sheet = $this->fetchSheet($sheetUrl, $gid);
        $rows = array_map(fn (array $row) => $this->normalizeRow($row), $sheet['rows']);

        $stats = [
            'total_rows' => count($rows),
            'products_created' => 0,
            'products_updated' => 0,
            'variants_created' => 0,
            'variants_updated' => 0,
            'categories' => 0,
            'images' => 0,
            'skipped' => 0,
            'not_found' => 0,
            'errors' => 0,
        ];
        $errors = [];

        if ($dryRun) {
            foreach ($rows as $row) {
                $action = $this->classifyRow($row, $mode);
                if ($action === 'would_create') {
                    $tipo = $this->resolveTipo($row['tipo'] ?? '');
                    if ($tipo === 'variante') {
                        $stats['variants_created']++;
                    } else {
                        $stats['products_created']++;
                    }
                } elseif ($action === 'would_update') {
                    $tipo = $this->resolveTipo($row['tipo'] ?? '');
                    if ($tipo === 'variante') {
                        $stats['variants_updated']++;
                    } else {
                        $stats['products_updated']++;
                    }
                } elseif ($action === 'not_found') {
                    $stats['not_found']++;
                } else {
                    $stats['skipped']++;
                }
            }

            return [
                'dry_run' => true,
                'export_url' => $sheet['export_url'],
                'mode' => $mode,
                'stats' => $stats,
            ];
        }

        $this->categoryCache = [];
        $this->parentSkuToId = [];
        $this->syncAttributeParentIds = [];

        if ($mode === 'full') {
            foreach ($rows as $row) {
                $tipo = $this->resolveTipo($row['tipo'] ?? 'producto');
                if (in_array($tipo, ['producto', 'variable'], true) && ! empty($row['categorias'])) {
                    $this->ensureCategories($row['categorias'], $stats);
                }
            }
        }

        foreach ($rows as $row) {
            try {
                if ($mode === 'inventory') {
                    $this->syncInventoryRow($row, $stats);
                } else {
                    $this->syncFullRow($row, $stats);
                }
            } catch (\Throwable $e) {
                $stats['errors']++;
                $errors[] = ['sku' => $row['sku'] ?? '', 'error' => $e->getMessage()];
            }
        }

        $catalogStats = null;
        if ($mode === 'full' && $this->syncAttributeParentIds !== []) {
            $catalogStats = (new BoutiqueVariantAttributeCatalogSync)->sync(
                array_map('intval', array_keys($this->syncAttributeParentIds))
            );
        }

        return [
            'dry_run' => false,
            'export_url' => $sheet['export_url'],
            'mode' => $mode,
            'stats' => $stats,
            'error_details' => array_slice($errors, 0, 30),
            'attribute_catalog_sync' => $catalogStats,
        ];
    }

    /**
     * @param  array<string, string>  $row
     */
    private function syncInventoryRow(array $row, array &$stats): void
    {
        $sku = trim($row['sku'] ?? '');
        if ($sku === '') {
            $stats['skipped']++;

            return;
        }

        $variant = BoutiqueProductVariant::query()->where('sku', $sku)->first();
        if ($variant) {
            $this->applyVariantUpdates($variant, $row, $stats, true);

            return;
        }

        $product = BoutiqueProduct::query()->where('sku', $sku)->first();
        if ($product) {
            $this->applyProductUpdates($product, $row, $stats, true);

            return;
        }

        $stats['not_found']++;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function syncFullRow(array $row, array &$stats): void
    {
        $sku = trim($row['sku'] ?? '');
        if ($sku === '') {
            $stats['skipped']++;

            return;
        }

        $tipo = $this->resolveTipo($row['tipo'] ?? 'producto');

        if ($tipo === 'variante') {
            $this->upsertVariant($row, $stats);

            return;
        }

        if ($tipo === 'variable') {
            $this->upsertVariableProduct($row, $stats);

            return;
        }

        $this->upsertSimpleProduct($row, $stats);
    }

    /**
     * @param  array<string, string>  $row
     */
    private function upsertSimpleProduct(array $row, array &$stats): void
    {
        $sku = trim($row['sku']);
        $product = BoutiqueProduct::query()->where('sku', $sku)->first();

        if ($product) {
            $this->applyProductUpdates($product, $row, $stats, false);
            if (! empty($row['imagenes'])) {
                $this->attachImagesIfMissing($product, $row['imagenes'], $stats);
            }

            return;
        }

        $product = new BoutiqueProduct([
            'category_id' => $this->getCategoryId($row['categorias'] ?? ''),
            'name' => $row['nombre'] ?? 'Sin nombre',
            'description' => $row['descripcion'] ?? '',
            'price' => $this->parsePrice($row['precio'] ?? ''),
            'sku' => $sku,
            'stock' => (int) ($row['stock'] ?? 0),
            'active' => false,
        ]);
        $product->uuid = (string) Uuid::uuid4();
        $product->save();

        if (! empty($row['imagenes'])) {
            $this->createImages($product, $row['imagenes'], $stats);
        }

        $wantsPublished = $this->parsePublished($row['publicado'] ?? '');
        $product->update([
            'active' => BoutiqueProductPublicationService::resolveActiveFromImportFlag($product, $wantsPublished),
        ]);

        $stats['products_created']++;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function upsertVariableProduct(array $row, array &$stats): void
    {
        $sku = trim($row['sku']);
        $product = BoutiqueProduct::query()->where('sku', $sku)->first();

        if ($product) {
            $updates = [];
            if (! empty($row['nombre'])) {
                $updates['name'] = $row['nombre'];
            }
            if (array_key_exists('descripcion', $row) && $row['descripcion'] !== '') {
                $updates['description'] = $row['descripcion'];
            }
            if (isset($row['precio']) && $row['precio'] !== '') {
                $updates['price'] = $this->parsePrice($row['precio']);
            }
            if ($updates !== []) {
                $product->update($updates);
            }
            if (isset($row['publicado'])) {
                $product->update([
                    'active' => BoutiqueProductPublicationService::resolveActiveFromImportFlag(
                        $product,
                        $this->parsePublished($row['publicado'])
                    ),
                ]);
            }
            $this->parentSkuToId[$sku] = (int) $product->id;
            $this->syncAttributeParentIds[(int) $product->id] = true;
            $stats['products_updated']++;

            return;
        }

        $product = new BoutiqueProduct([
            'category_id' => $this->getCategoryId($row['categorias'] ?? ''),
            'name' => $row['nombre'] ?? 'Sin nombre',
            'description' => $row['descripcion'] ?? '',
            'price' => $this->parsePrice($row['precio'] ?? ''),
            'sku' => $sku,
            'stock' => 0,
            'active' => false,
        ]);
        $product->uuid = (string) Uuid::uuid4();
        $product->save();

        $this->parentSkuToId[$sku] = (int) $product->id;
        $this->syncAttributeParentIds[(int) $product->id] = true;

        if (! empty($row['imagenes'])) {
            $this->createImages($product, $row['imagenes'], $stats);
        }

        $product->update([
            'active' => BoutiqueProductPublicationService::resolveActiveFromImportFlag(
                $product,
                $this->parsePublished($row['publicado'] ?? '')
            ),
        ]);

        $stats['products_created']++;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function upsertVariant(array $row, array &$stats): void
    {
        $parentSku = trim($row['sku_padre'] ?? '');
        if ($parentSku === '') {
            $stats['skipped']++;

            return;
        }

        $productId = $this->parentSkuToId[$parentSku] ?? null;
        if (! $productId) {
            $parent = BoutiqueProduct::query()->where('sku', $parentSku)->first();
            if ($parent) {
                $productId = (int) $parent->id;
                $this->parentSkuToId[$parentSku] = $productId;
            }
        }
        if (! $productId) {
            $stats['skipped']++;

            return;
        }

        $sku = trim($row['sku']);
        $variant = BoutiqueProductVariant::query()
            ->where('sku', $sku)
            ->where('product_id', $productId)
            ->first();

        if ($variant) {
            $this->applyVariantUpdates($variant, $row, $stats, false);
            $this->syncAttributeParentIds[$productId] = true;

            return;
        }

        BoutiqueProductVariant::create([
            'product_id' => $productId,
            'color' => $row['color'] ?? null,
            'size' => $row['talla'] ?? null,
            'sku' => $sku,
            'stock' => (int) ($row['stock'] ?? 0),
            'price' => isset($row['precio']) && $row['precio'] !== ''
                ? $this->parsePrice($row['precio'])
                : null,
            'active' => $this->parsePublished($row['publicado'] ?? 'si'),
        ]);
        $this->syncAttributeParentIds[$productId] = true;
        $stats['variants_created']++;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function applyProductUpdates(
        BoutiqueProduct $product,
        array $row,
        array &$stats,
        bool $inventoryOnly
    ): void {
        $updates = [];

        if (! $inventoryOnly) {
            if (! empty($row['nombre'])) {
                $updates['name'] = $row['nombre'];
            }
            if (array_key_exists('descripcion', $row) && $row['descripcion'] !== '') {
                $updates['description'] = $row['descripcion'];
            }
            if (! empty($row['categorias'])) {
                $catId = $this->getCategoryId($row['categorias']);
                if ($catId) {
                    $updates['category_id'] = $catId;
                }
            }
        }

        if (isset($row['precio']) && $row['precio'] !== '') {
            $updates['price'] = $this->parsePrice($row['precio']);
        }
        if (isset($row['stock']) && $row['stock'] !== '') {
            $updates['stock'] = (int) $row['stock'];
        }

        if ($updates !== []) {
            $product->update($updates);
        }

        if (isset($row['publicado'])) {
            $product->update([
                'active' => BoutiqueProductPublicationService::resolveActiveFromImportFlag(
                    $product->fresh(),
                    $this->parsePublished($row['publicado'])
                ),
            ]);
        }

        $stats['products_updated']++;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function applyVariantUpdates(
        BoutiqueProductVariant $variant,
        array $row,
        array &$stats,
        bool $inventoryOnly
    ): void {
        $updates = [];

        if (! $inventoryOnly) {
            if (! empty($row['color'])) {
                $updates['color'] = $row['color'];
            }
            if (! empty($row['talla'])) {
                $updates['size'] = $row['talla'];
            }
        }

        if (isset($row['stock']) && $row['stock'] !== '') {
            $updates['stock'] = (int) $row['stock'];
        }
        if (isset($row['precio']) && $row['precio'] !== '') {
            $updates['price'] = $this->parsePrice($row['precio']);
        }
        if (isset($row['publicado'])) {
            $updates['active'] = $this->parsePublished($row['publicado']);
        }

        if ($updates !== []) {
            $variant->update($updates);
        }

        $stats['variants_updated']++;
    }

    private function attachImagesIfMissing(BoutiqueProduct $product, string $imageString, array &$stats): void
    {
        if ($product->images()->exists()) {
            return;
        }
        $this->createImages($product, $imageString, $stats);
    }

    private function createImages(BoutiqueProduct $product, string $imageString, array &$stats): void
    {
        $order = (int) ($product->images()->max('sort_id') ?? -1) + 1;
        foreach (array_map('trim', explode(',', $imageString)) as $url) {
            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            BoutiqueProductImage::create([
                'product_id' => $product->id,
                'image_path' => $url,
                'sort_id' => $order++,
                'status' => 'uploaded',
            ]);
            $stats['images']++;
        }
    }

    private function ensureCategories(string $catString, array &$stats): void
    {
        if ($catString === '') {
            return;
        }
        foreach (array_map('trim', explode(',', $catString)) as $cat) {
            if ($cat === '' || isset($this->categoryCache[$cat])) {
                continue;
            }
            $parts = array_map('trim', explode('>', $cat));
            $name = end($parts);
            $existing = BoutiqueCategory::query()->where('name', $name)->first();
            if (! $existing) {
                $existing = BoutiqueCategory::create(['name' => $name, 'active' => true]);
                $stats['categories']++;
            }
            $this->categoryCache[$cat] = (int) $existing->id;
            $this->categoryCache[$name] = (int) $existing->id;
        }
    }

    private function getCategoryId(string $catString): ?int
    {
        if ($catString === '') {
            return null;
        }
        foreach (array_reverse(array_map('trim', explode(',', $catString))) as $cat) {
            if (isset($this->categoryCache[$cat])) {
                return $this->categoryCache[$cat];
            }
            $parts = array_map('trim', explode('>', $cat));
            $name = end($parts);
            if (isset($this->categoryCache[$name])) {
                return $this->categoryCache[$name];
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $raw
     * @return array<string, string>
     */
    private function normalizeRow(array $raw): array
    {
        $aliases = $this->templateDefinition()['aliases'];
        $out = [];

        foreach ($raw as $header => $value) {
            $normalizedHeader = mb_strtolower(trim($header));
            foreach ($aliases as $canonical => $list) {
                foreach ($list as $alias) {
                    if ($normalizedHeader === mb_strtolower($alias)) {
                        $out[$canonical] = trim((string) $value);

                        continue 2;
                    }
                }
            }
        }

        if (isset($out['sku'])) {
            $out['sku'] = trim($out['sku']);
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function classifyRow(array $row, string $mode): string
    {
        $sku = trim($row['sku'] ?? '');
        if ($sku === '') {
            return 'would_skip';
        }

        $variant = BoutiqueProductVariant::query()->where('sku', $sku)->exists();
        $product = BoutiqueProduct::query()->where('sku', $sku)->exists();

        if ($variant || $product) {
            return 'would_update';
        }

        if ($mode === 'inventory') {
            return 'not_found';
        }

        $tipo = $this->resolveTipo($row['tipo'] ?? 'producto');
        if ($tipo === 'variante' && trim($row['sku_padre'] ?? '') === '') {
            return 'would_skip';
        }

        return 'would_create';
    }

    private function resolveTipo(string $tipo): string
    {
        $t = mb_strtolower(trim($tipo));
        if (in_array($t, ['variante', 'variation', 'variant', 'variacion'], true)) {
            return 'variante';
        }
        if (in_array($t, ['variable', 'variables'], true)) {
            return 'variable';
        }

        return 'producto';
    }

    private function parsePrice(string $value): float
    {
        if ($value === '') {
            return 0.0;
        }

        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    private function parsePublished(string $value): bool
    {
        $v = mb_strtolower(trim($value));
        if ($v === '') {
            return true;
        }

        return in_array($v, ['1', 'si', 'sí', 'yes', 'true', 'activo', 'publicado'], true);
    }
}
