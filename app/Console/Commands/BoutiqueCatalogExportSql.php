<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoutiqueCatalogExportSql extends Command
{
    protected $signature = 'boutique:catalog-export-sql
                            {--output= : Ruta del archivo .sql (default: storage/app/boutique-catalog-export/latest.sql)}';

    protected $description = 'Exporta catálogo boutique a SQL sin depender de mysqldump';

    /** @var list<string> */
    private const TABLE_SUFFIXES = [
        'boutique_categories',
        'boutique_banners',
        'boutique_product_attributes',
        'boutique_product_attribute_values',
        'boutique_products',
        'boutique_product_attribute_product',
        'boutique_product_variants',
        'boutique_variant_attribute_values',
        'boutique_product_images',
    ];

    public function handle(): int
    {
        $output = (string) ($this->option('output') ?: storage_path('app/boutique-catalog-export/latest.sql'));
        $dir = dirname($output);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $prefix = (string) env('DB_TABLE_PREFIX', '');
        $tables = [];
        foreach (self::TABLE_SUFFIXES as $suffix) {
            $table = $prefix.$suffix;
            if (! Schema::hasTable($table)) {
                $this->warn("Tabla omitida (no existe): {$table}");

                continue;
            }
            $tables[] = $table;
        }

        if ($tables === []) {
            $this->error('No hay tablas boutique para exportar.');

            return self::FAILURE;
        }

        $handle = fopen($output, 'wb');
        if ($handle === false) {
            $this->error("No se pudo escribir: {$output}");

            return self::FAILURE;
        }

        $timestamp = gmdate('Y-m-d H:i:s').' UTC';
        fwrite($handle, "-- Boutique catalog export {$timestamp}\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");

        $totalRows = 0;
        foreach ($tables as $table) {
            $count = (int) DB::table($table)->count();
            $this->line("Exportando {$table} ({$count} filas)...");
            fwrite($handle, "\n-- Table: {$table}\n");

            if ($count === 0) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            DB::table($table)->orderBy($columns[0] ?? 'id')->chunk(200, function ($rows) use ($handle, $table, $columns, &$totalRows, $bar) {
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($columns as $column) {
                        $values[] = $this->quoteSqlValue($row->{$column} ?? null);
                    }
                    $sql = sprintf(
                        "INSERT INTO `%s` (`%s`) VALUES (%s);\n",
                        $table,
                        implode('`, `', $columns),
                        implode(', ', $values)
                    );
                    fwrite($handle, $sql);
                    $totalRows++;
                    $bar->advance();
                }
            });

            $bar->finish();
            $this->newLine();
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $bytes = filesize($output) ?: 0;
        $this->info("Export SQL listo: {$output}");
        $this->info("Filas: {$totalRows} | Tamaño: {$bytes} bytes");

        return self::SUCCESS;
    }

    private function quoteSqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $string = (string) $value;

        return "'".str_replace(["\\", "'", "\0", "\n", "\r"], ["\\\\", "''", '\\0', '\\n', '\\r'], $string)."'";
    }
}
