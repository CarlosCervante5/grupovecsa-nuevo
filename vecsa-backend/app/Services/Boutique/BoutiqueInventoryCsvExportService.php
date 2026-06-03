<?php

namespace App\Services\Boutique;

use App\Models\Boutique\BoutiqueCategory;
use App\Models\Boutique\BoutiqueProduct;
use App\Services\DealershipAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BoutiqueInventoryCsvExportService
{
    public function __construct(private readonly DealershipAccessService $dealershipAccess) {}

    public function streamDownload(Request $request): StreamedResponse
    {
        $filename = 'inventario-boutique-'.now()->format('Y-m-d_His').'.csv';

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $this->headers(), ',', '"', '\\');

            $this->buildFilteredQuery($request)
                ->orderBy('sku')
                ->chunkById(150, function ($products) use ($handle) {
                    foreach ($products as $product) {
                        foreach ($this->rowsForProduct($product) as $row) {
                            fputcsv($handle, $row, ',', '"', '\\');
                        }
                    }
                });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }

    /**
     * @return list<string>
     */
    private function headers(): array
    {
        return [
            'sku',
            'tipo',
            'sku_padre',
            'nombre',
            'descripcion',
            'categorias',
            'precio',
            'stock',
            'publicado',
            'imagenes',
            'talla',
            'color',
            'sucursal',
        ];
    }

    private function buildFilteredQuery(Request $request): Builder
    {
        $query = BoutiqueProduct::query()->with([
            'category.parent',
            'dealership',
            'images' => fn ($q) => $q->orderBy('sort_id'),
            'variants' => fn ($q) => $q->orderBy('color')->orderBy('size')->orderBy('sku'),
        ]);

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

        return $query;
    }

    /**
     * @return list<list<string|int|float|null>>
     */
    private function rowsForProduct(BoutiqueProduct $product): array
    {
        $variants = $product->variants;

        $images = $product->images
            ->pluck('image_path')
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->implode(', ');

        $base = [
            'nombre' => (string) $product->name,
            'descripcion' => (string) ($product->description ?? ''),
            'categorias' => $this->categoryPath($product->category),
            'publicado' => $product->active ? 'si' : 'no',
            'imagenes' => $images,
            'sucursal' => (string) ($product->dealership?->name ?? ''),
        ];

        if ($variants->isEmpty()) {
            return [[
                (string) $product->sku,
                'producto',
                '',
                $base['nombre'],
                $base['descripcion'],
                $base['categorias'],
                $this->formatPrice($product->price),
                (int) $product->stock,
                $base['publicado'],
                $base['imagenes'],
                '',
                '',
                $base['sucursal'],
            ]];
        }

        $rows = [[
            (string) $product->sku,
            'variable',
            '',
            $base['nombre'],
            $base['descripcion'],
            $base['categorias'],
            $this->formatPrice($product->price),
            (int) $product->stock,
            $base['publicado'],
            $base['imagenes'],
            '',
            '',
            $base['sucursal'],
        ]];

        foreach ($variants as $variant) {
            $rows[] = [
                (string) $variant->sku,
                'variante',
                (string) $product->sku,
                $base['nombre'],
                '',
                $base['categorias'],
                $this->formatPrice($variant->price ?? $product->price),
                (int) $variant->stock,
                $variant->active ? 'si' : 'no',
                '',
                (string) ($variant->size ?? ''),
                (string) ($variant->color ?? ''),
                $base['sucursal'],
            ];
        }

        return $rows;
    }

    private function categoryPath(?BoutiqueCategory $category): string
    {
        if ($category === null) {
            return '';
        }

        $category->loadMissing('parent');
        if ($category->parent) {
            return trim($category->parent->name.' > '.$category->name);
        }

        return (string) $category->name;
    }

    private function formatPrice(mixed $price): string
    {
        if ($price === null || $price === '') {
            return '0';
        }

        return rtrim(rtrim(number_format((float) $price, 2, '.', ''), '0'), '.') ?: '0';
    }
}
