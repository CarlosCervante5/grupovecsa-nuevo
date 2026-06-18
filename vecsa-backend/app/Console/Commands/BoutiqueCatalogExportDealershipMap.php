<?php

namespace App\Console\Commands;

use App\Models\Boutique\BoutiqueProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoutiqueCatalogExportDealershipMap extends Command
{
    protected $signature = 'boutique:catalog-export-dealership-map
                            {--output= : Ruta de salida JSON (default: storage/app/boutique-dealership-map.json)}';

    protected $description = 'Exporta mapa sandbox dealership_id → nombre para el fixup post-import en producción';

    public function handle(): int
    {
        $prefix = (string) env('DB_TABLE_PREFIX', '');
        $productsTable = $prefix.'boutique_products';
        $dealershipsTable = $prefix.'dealerships';

        if (! Schema::hasTable($productsTable) || ! Schema::hasColumn($productsTable, 'dealership_id')) {
            $this->warn('No hay dealership_id en boutique_products; mapa vacío.');
            $this->writeMap([]);

            return self::SUCCESS;
        }

        if (! Schema::hasTable($dealershipsTable)) {
            $this->warn('No existe tabla dealerships; mapa vacío.');
            $this->writeMap([]);

            return self::SUCCESS;
        }

        $ids = BoutiqueProduct::query()
            ->whereNotNull('dealership_id')
            ->distinct()
            ->pluck('dealership_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();

        $rows = DB::table($dealershipsTable)
            ->whereIn('id', $ids)
            ->select(['id', 'name'])
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => [
                'sandbox_id' => (int) $row->id,
                'name' => (string) $row->name,
            ])
            ->values()
            ->all();

        $this->writeMap($rows);
        $this->info('Mapa exportado: '.count($rows).' sucursal(es).');

        return self::SUCCESS;
    }

    /**
     * @param  list<array{sandbox_id: int, name: string}>  $rows
     */
    private function writeMap(array $rows): void
    {
        $output = (string) ($this->option('output') ?: storage_path('app/boutique-dealership-map.json'));
        $dir = dirname($output);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $output,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n"
        );

        $this->line("Archivo: {$output}");
    }
}
