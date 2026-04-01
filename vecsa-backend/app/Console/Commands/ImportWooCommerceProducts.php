<?php

namespace App\Console\Commands;

use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use App\Models\Boutique\BoutiqueProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWooCommerceProducts extends Command
{
    protected $signature = 'import:woocommerce {file}';
    protected $description = 'Import products from a WooCommerce CSV export';

    private $categoryCache = [];
    private $parentSkuToId = [];
    private $stats = ['categories' => 0, 'products' => 0, 'variants' => 0, 'images' => 0, 'skipped' => 0];

    public function handle()
    {
        $file = $this->argument('file');
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $this->info("Reading file...");
        if (str_ends_with($file, '.json')) {
            $rows = json_decode(file_get_contents($file), true);
        } else {
            $rows = $this->parseCsv($file);
        }
        $this->info("Found " . count($rows) . " rows");

        // Pass 1: Create categories
        $this->info("Creating categories...");
        foreach ($rows as $row) {
            if (in_array($row['Tipo'], ['simple', 'variable'])) {
                $this->ensureCategories($row['Categorías'] ?? '');
            }
        }
        $this->info("✓ {$this->stats['categories']} categories created");

        // Pass 2: Create simple + variable products
        $this->info("Creating products...");
        foreach ($rows as $row) {
            if ($row['Tipo'] === 'simple') {
                $this->createSimpleProduct($row);
            } elseif ($row['Tipo'] === 'variable') {
                $this->createVariableProduct($row);
            }
        }
        $this->info("✓ {$this->stats['products']} products created");

        // Pass 3: Create variations
        $this->info("Creating variants...");
        foreach ($rows as $row) {
            if ($row['Tipo'] === 'variation') {
                $this->createVariation($row);
            }
        }
        $this->info("✓ {$this->stats['variants']} variants created");
        $this->info("✓ {$this->stats['images']} images created");
        $this->info("⚠ {$this->stats['skipped']} skipped (no parent/sku)");

        return 0;
    }

    private function parseCsv(string $file): array
    {
        $rows = [];
        $handle = fopen($file, 'r');
        // Set a large length to handle multiline fields
        $headers = fgetcsv($handle, 0, ',', '"', '\\');
        // Clean BOM
        if ($headers) $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        // Clean header whitespace
        $headers = array_map('trim', $headers);
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if (count($data) === count($headers)) {
                $rows[] = array_combine($headers, $data);
            } elseif (count($data) > count($headers)) {
                // Truncate extra columns
                $rows[] = array_combine($headers, array_slice($data, 0, count($headers)));
            }
            // Skip rows with fewer columns (broken multiline)
        }
        fclose($handle);
        return $rows;
    }

    private function ensureCategories(string $catString): void
    {
        if (!$catString) return;
        $cats = array_map('trim', explode(',', $catString));
        foreach ($cats as $cat) {
            if (!$cat || isset($this->categoryCache[$cat])) continue;
            // Handle hierarchy: "Life Style > Camisetas" → use leaf
            $parts = array_map('trim', explode('>', $cat));
            $name = end($parts);
            $existing = BoutiqueCategory::where('name', $name)->first();
            if (!$existing) {
                $existing = BoutiqueCategory::create(['name' => $name, 'active' => true]);
                $this->stats['categories']++;
            }
            $this->categoryCache[$cat] = $existing->id;
            $this->categoryCache[$name] = $existing->id;
        }
    }

    private function getCategoryId(string $catString): ?int
    {
        if (!$catString) return null;
        $cats = array_map('trim', explode(',', $catString));
        // Use the most specific (last) category
        foreach (array_reverse($cats) as $cat) {
            if (isset($this->categoryCache[$cat])) return $this->categoryCache[$cat];
            $parts = array_map('trim', explode('>', $cat));
            $name = end($parts);
            if (isset($this->categoryCache[$name])) return $this->categoryCache[$name];
        }
        return null;
    }

    private function createSimpleProduct(array $row): void
    {
        $sku = trim($row['SKU'] ?? '');
        if (!$sku) { $this->stats['skipped']++; return; }
        if (BoutiqueProduct::where('sku', $sku)->exists()) { $this->stats['skipped']++; return; }

        $product = BoutiqueProduct::create([
            'category_id' => $this->getCategoryId($row['Categorías'] ?? ''),
            'name' => $row['Nombre'] ?? 'Sin nombre',
            'description' => $row['Descripción corta'] ?: ($row['Descripción'] ?? ''),
            'price' => $this->parsePrice($row['Precio rebajado'] ?: $row['Precio normal']),
            'sku' => $sku,
            'stock' => (int)($row['Inventario'] ?? 0),
            'active' => ($row['Publicado'] ?? '0') == '1',
        ]);

        $this->createImages($product, $row['Imágenes'] ?? '');
        $this->stats['products']++;
    }

    private function createVariableProduct(array $row): void
    {
        $sku = trim($row['SKU'] ?? '');
        if (!$sku) { $this->stats['skipped']++; return; }
        if (BoutiqueProduct::where('sku', $sku)->exists()) { $this->stats['skipped']++; return; }

        $price = $this->parsePrice($row['Meta: _min_variation_regular_price'] ?: $row['Precio normal']);

        $product = BoutiqueProduct::create([
            'category_id' => $this->getCategoryId($row['Categorías'] ?? ''),
            'name' => $row['Nombre'] ?? 'Sin nombre',
            'description' => $row['Descripción corta'] ?: ($row['Descripción'] ?? ''),
            'price' => $price ?: 0,
            'sku' => $sku,
            'stock' => 0, // Stock managed by variants
            'active' => ($row['Publicado'] ?? '0') == '1',
        ]);

        $this->parentSkuToId[$sku] = $product->id;
        $this->createImages($product, $row['Imágenes'] ?? '');
        $this->stats['products']++;
    }

    private function createVariation(array $row): void
    {
        $parentSku = trim($row['Superior'] ?? '');
        if (!$parentSku) { $this->stats['skipped']++; return; }

        $productId = $this->parentSkuToId[$parentSku] ?? null;
        if (!$productId) {
            // Try to find parent by SKU in DB
            $parent = BoutiqueProduct::where('sku', $parentSku)->first();
            if ($parent) {
                $productId = $parent->id;
                $this->parentSkuToId[$parentSku] = $productId;
            } else {
                $this->stats['skipped']++;
                return;
            }
        }

        // Extract attributes
        $size = '';
        $color = '';
        for ($i = 1; $i <= 8; $i++) {
            $attrName = strtolower(trim($row["Nombre del atributo $i"] ?? ''));
            $attrValue = trim($row["Valor(es) del atributo $i"] ?? '');
            if (!$attrName || !$attrValue) continue;
            if (in_array($attrName, ['talla', 'size'])) $size = $attrValue;
            if (in_array($attrName, ['color'])) $color = $attrValue;
        }

        $sku = trim($row['SKU'] ?? '');
        if (!$sku) $sku = $parentSku . '-' . ($size ?: 'v') . ($color ? '-' . $color : '');

        // Skip if variant already exists
        if (BoutiqueProductVariant::where('sku', $sku)->where('product_id', $productId)->exists()) {
            $this->stats['skipped']++;
            return;
        }

        BoutiqueProductVariant::create([
            'product_id' => $productId,
            'color' => $color ?: null,
            'color_hex' => null,
            'size' => $size ?: null,
            'sku' => $sku,
            'stock' => (int)($row['Inventario'] ?? 0),
            'active' => true,
        ]);

        $this->stats['variants']++;
    }

    private function createImages(BoutiqueProduct $product, string $imageString): void
    {
        if (!$imageString) return;
        $urls = array_map('trim', explode(',', $imageString));
        $order = 0;
        foreach ($urls as $url) {
            if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) continue;
            BoutiqueProductImage::create([
                'product_id' => $product->id,
                'image_path' => $url,
                'sort_order' => $order++,
            ]);
            $this->stats['images']++;
        }
    }

    private function parsePrice($value): float
    {
        if (!$value) return 0;
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }
}
