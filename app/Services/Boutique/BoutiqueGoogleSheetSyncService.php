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
        private readonly BoutiqueGoogleSheetColumnMapper $columnMapper,
        private readonly BoutiqueExternalImageImporter $imageImporter,
    ) {}

    /**
     * @return list<array{key: string, label: string, required: bool, example: string}>
     */
    public static function columnCatalog(): array
    {
        return [
            ['key' => 'sku', 'label' => 'SKU', 'required' => true, 'example' => 'BMW-MALETA-001'],
            ['key' => 'tipo', 'label' => 'Tipo de fila', 'required' => false, 'example' => 'producto | variable | variante'],
            ['key' => 'sku_padre', 'label' => 'SKU padre', 'required' => false, 'example' => 'BMW-MALETA-001'],
            ['key' => 'nombre', 'label' => 'Nombre', 'required' => false, 'example' => 'Maleta BMW'],
            ['key' => 'descripcion', 'label' => 'Descripción', 'required' => false, 'example' => 'Descripción corta'],
            ['key' => 'categorias', 'label' => 'Categorías', 'required' => false, 'example' => 'Accesorios > BMW'],
            ['key' => 'precio', 'label' => 'Precio', 'required' => false, 'example' => '1299.00'],
            ['key' => 'stock', 'label' => 'Stock / inventario', 'required' => false, 'example' => '5'],
            ['key' => 'publicado', 'label' => 'Publicado / activo', 'required' => false, 'example' => 'si | no | 1 | 0'],
            ['key' => 'imagenes', 'label' => 'Imágenes (URLs)', 'required' => false, 'example' => 'https://drive.google.com/... o https://.../foto.jpg'],
            ['key' => 'talla', 'label' => 'Talla', 'required' => false, 'example' => 'L'],
            ['key' => 'color', 'label' => 'Color', 'required' => false, 'example' => 'Negro'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function columnAliases(): array
    {
        return [
            'sku' => ['sku', 'código', 'codigo', 'code', 'referencia', 'ref'],
            'tipo' => ['tipo', 'type', 'tipo de producto'],
            'sku_padre' => ['sku_padre', 'sku padre', 'parent_sku', 'superior', 'sku parent'],
            'nombre' => ['nombre', 'name', 'producto', 'titulo', 'título'],
            'descripcion' => ['descripcion', 'descripción', 'description', 'descripcion corta'],
            'categorias' => ['categorias', 'categorías', 'categories', 'categoria', 'categoría'],
            'precio' => ['precio', 'price', 'precio normal', 'precio venta', 'sale price'],
            'stock' => ['stock', 'inventario', 'cantidad', 'qty', 'existencia'],
            'publicado' => ['publicado', 'published', 'active', 'activo', 'visible', 'estado'],
            'imagenes' => ['imagenes', 'imágenes', 'images', 'imagen_url', 'imagen', 'url imagen', 'fotos'],
            'talla' => ['talla', 'size', 'tamano', 'tamaño'],
            'color' => ['color', 'colour'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function templateDefinition(): array
    {
        return [
            'title' => 'Inventario Boutique — Google Sheets',
            'instructions' => [
                'Publica la hoja en la web o compártela con enlace.',
                'Carga la hoja, asigna cada columna de tu Sheet al campo del sistema y luego sincroniza.',
                'El campo SKU es obligatorio para importar.',
                'En Imágenes puedes pegar enlaces de Google Drive (compartir → cualquier persona con el enlace); se descargan y suben al catálogo automáticamente.',
                'También acepta URLs directas a .jpg/.png (varias separadas por coma).',
            ],
            'columns' => self::columnCatalog(),
            'fields' => $this->columnMapper->fieldDefinitions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function loadHeaders(?string $sheetUrl, ?string $gid): array
    {
        $sheet = $this->fetchSheet($sheetUrl, $gid);
        $suggested = $this->columnMapper->suggestMapping($sheet['headers']);

        return [
            'export_url' => $sheet['export_url'],
            'sheet_headers' => $sheet['headers'],
            'total_rows' => count($sheet['rows']),
            'suggested_mapping' => $suggested,
            'sample_raw_rows' => $this->columnMapper->sampleRawRows($sheet['headers'], $sheet['rows'], 4),
        ];
    }

    /**
     * @param  array<string, string|null>  $columnMapping
     * @return array{mapping: array<string, string>, rows: list<array<string, string>>}
     */
    private function prepareRows(array $sheetRows, array $sheetHeaders, array $columnMapping): array
    {
        $resolved = $this->columnMapper->resolveMapping($columnMapping, $sheetHeaders);
        if ($resolved['missing_required'] !== []) {
            throw new \InvalidArgumentException(
                'Falta mapear columnas obligatorias: '.implode(', ', $resolved['missing_required'])
            );
        }

        return [
            'mapping' => $resolved['mapping'],
            'rows' => $this->columnMapper->mapRows($sheetRows, $resolved['mapping']),
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
    /**
     * @param  array<string, string|null>  $columnMapping
     */
    public function preview(?string $sheetUrl, ?string $gid, string $mode = 'inventory', array $columnMapping = []): array
    {
        $sheet = $this->fetchSheet($sheetUrl, $gid);
        if ($columnMapping === []) {
            $columnMapping = $this->columnMapper->suggestMapping($sheet['headers']);
        }
        $prepared = $this->prepareRows($sheet['rows'], $sheet['headers'], $columnMapping);
        $normalized = $prepared['rows'];

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
            'column_mapping' => $prepared['mapping'],
            'mode' => $mode,
            'stats' => $stats,
            'sample' => $samples,
        ];
    }

    /**
     * @param  array<string, string|null>  $columnMapping
     * @return array<string, mixed>
     */
    public function sync(
        ?string $sheetUrl,
        ?string $gid,
        string $mode = 'inventory',
        bool $dryRun = false,
        array $columnMapping = []
    ): array {
        $sheet = $this->fetchSheet($sheetUrl, $gid);
        if ($columnMapping === []) {
            $columnMapping = $this->columnMapper->suggestMapping($sheet['headers']);
        }
        $prepared = $this->prepareRows($sheet['rows'], $sheet['headers'], $columnMapping);
        $rows = $prepared['rows'];

        $stats = [
            'total_rows' => count($rows),
            'products_created' => 0,
            'products_updated' => 0,
            'variants_created' => 0,
            'variants_updated' => 0,
            'categories' => 0,
            'images' => 0,
            'images_failed' => 0,
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
                'column_mapping' => $prepared['mapping'],
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
            'column_mapping' => $prepared['mapping'],
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

            if ($this->imageImporter->isGoogleDriveUrl($url)) {
                if ($this->imageImporter->importDriveImage($product, $url, $order++)) {
                    $stats['images']++;
                } else {
                    $stats['images_failed']++;
                }

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
