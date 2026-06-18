<?php

namespace App\Console\Commands;

use App\Models\Boutique\BoutiqueProduct;
use App\Models\Dealership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoutiqueCatalogImportFixup extends Command
{
    protected $signature = 'boutique:catalog-import-fixup
                            {--map= : JSON sandbox_id→name exportado desde sandbox (default: storage/app/boutique-dealership-map.json)}
                            {--null-dealerships : Pone dealership_id en NULL si no existe en esta BD}
                            {--remap-dealerships : Reasigna dealership_id por nombre de sucursal usando --map}
                            {--dry-run : Solo muestra cambios sin aplicarlos}';

    protected $description = 'Ajustes post-import del catálogo boutique (dealership_id y conteos)';

    public function handle(): int
    {
        $prefix = (string) env('DB_TABLE_PREFIX', '');
        $productsTable = $prefix.'boutique_products';

        if (! Schema::hasTable($productsTable)) {
            $this->error("No existe {$productsTable}.");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $nullDealerships = (bool) $this->option('null-dealerships');
        $remapDealerships = (bool) $this->option('remap-dealerships');

        if (! $nullDealerships && ! $remapDealerships) {
            $nullDealerships = true;
            $remapDealerships = Schema::hasTable($prefix.'dealerships')
                && Schema::hasColumn($productsTable, 'dealership_id');
        }

        $stats = [
            'products' => BoutiqueProduct::query()->count(),
            'with_dealership' => 0,
            'remapped' => 0,
            'nulled' => 0,
            'invalid_remaining' => 0,
        ];

        if (! Schema::hasColumn($productsTable, 'dealership_id')) {
            $this->info("Tabla {$productsTable} sin columna dealership_id; nada que ajustar.");
            $this->printSummary($stats);

            return self::SUCCESS;
        }

        $validIds = Schema::hasTable($prefix.'dealerships')
            ? DB::table($prefix.'dealerships')->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];
        $validIdSet = array_fill_keys($validIds, true);

        $nameToId = [];
        if ($remapDealerships && Schema::hasTable($prefix.'dealerships')) {
            foreach (Dealership::query()->select(['id', 'name'])->get() as $dealership) {
                $key = $this->normalizeName((string) $dealership->name);
                if ($key !== '') {
                    $nameToId[$key] = (int) $dealership->id;
                }
            }
        }

        $sandboxNames = $this->loadSandboxDealershipMap(
            (string) ($this->option('map') ?: storage_path('app/boutique-dealership-map.json'))
        );

        BoutiqueProduct::query()
            ->whereNotNull('dealership_id')
            ->select(['id', 'dealership_id', 'sku'])
            ->orderBy('id')
            ->chunkById(200, function ($products) use (
                $dryRun,
                $remapDealerships,
                $nullDealerships,
                $validIdSet,
                $nameToId,
                $sandboxNames,
                &$stats
            ) {
                foreach ($products as $product) {
                    $stats['with_dealership']++;
                    $currentId = (int) $product->dealership_id;

                    if (isset($validIdSet[$currentId])) {
                        continue;
                    }

                    $newId = null;
                    if ($remapDealerships) {
                        $sandboxName = $sandboxNames[$currentId] ?? null;
                        if ($sandboxName !== null) {
                            $newId = $nameToId[$this->normalizeName((string) $sandboxName)] ?? null;
                        }
                    }

                    if ($newId !== null) {
                        $stats['remapped']++;
                        if (! $dryRun) {
                            $product->update(['dealership_id' => $newId]);
                        }
                        $this->line("  ↪ SKU {$product->sku}: dealership {$currentId} → {$newId}");

                        continue;
                    }

                    if ($nullDealerships) {
                        $stats['nulled']++;
                        if (! $dryRun) {
                            $product->update(['dealership_id' => null]);
                        }
                        $this->line("  ∅ SKU {$product->sku}: dealership {$currentId} → NULL");

                        continue;
                    }

                    $stats['invalid_remaining']++;
                }
            });

        $this->printSummary($stats);

        if ($stats['invalid_remaining'] > 0) {
            $this->warn('Quedan productos con dealership_id inválido. Usa --remap-dealerships o --null-dealerships.');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->comment('Dry-run: no se guardaron cambios.');
        } else {
            $this->info('Fixup completado.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $stats
     */
    private function printSummary(array $stats): void
    {
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Productos', $stats['products']],
                ['Con dealership_id', $stats['with_dealership']],
                ['Reasignados por nombre', $stats['remapped']],
                ['Puestos en NULL', $stats['nulled']],
                ['Inválidos restantes', $stats['invalid_remaining']],
            ]
        );
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;

        return $name;
    }

    /**
     * @return array<int, string>
     */
    private function loadSandboxDealershipMap(string $path): array
    {
        if (! is_readable($path)) {
            $this->warn("Mapa de sucursales no encontrado: {$path}");

            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            $this->warn("Mapa de sucursales inválido: {$path}");

            return [];
        }

        $map = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = (int) ($row['sandbox_id'] ?? 0);
            $name = trim((string) ($row['name'] ?? ''));
            if ($id > 0 && $name !== '') {
                $map[$id] = $name;
            }
        }

        $this->comment('Mapa sandbox cargado: '.count($map).' sucursal(es).');

        return $map;
    }
}
