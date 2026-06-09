<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoutiqueCatalogImportSql extends Command
{
    protected $signature = 'boutique:catalog-import-sql
                            {file : Ruta al archivo .sql generado por boutique:catalog-export-sql}
                            {--truncate : Borra tablas boutique antes de importar (requerido en prod)}';

    protected $description = 'Importa catálogo boutique desde SQL sin depender del cliente mysql';

    /** @var list<string> */
    private const TRUNCATE_SUFFIXES_REVERSE = [
        'boutique_variant_attribute_values',
        'boutique_product_variants',
        'boutique_product_attribute_product',
        'boutique_product_images',
        'boutique_products',
        'boutique_product_attribute_values',
        'boutique_product_attributes',
        'boutique_banners',
        'boutique_categories',
    ];

    public function handle(): int
    {
        $file = (string) $this->argument('file');
        if (! is_readable($file)) {
            $this->error("No se puede leer: {$file}");

            return self::FAILURE;
        }

        if (! $this->option('truncate')) {
            $this->error('Debes pasar --truncate para reemplazar el catálogo existente.');

            return self::FAILURE;
        }

        $prefix = (string) env('DB_TABLE_PREFIX', '');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (self::TRUNCATE_SUFFIXES_REVERSE as $suffix) {
            $table = $prefix.$suffix;
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
                $this->line("Vaciada: {$table}");
            }
        }

        $this->info('Importando SQL...');
        $handle = fopen($file, 'rb');
        if ($handle === false) {
            $this->error('No se pudo abrir el archivo SQL.');

            return self::FAILURE;
        }

        $buffer = '';
        $imported = 0;
        $skipped = 0;

        while (! feof($handle)) {
            $line = fgets($handle);
            if ($line === false) {
                break;
            }

            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $buffer .= $line;
            if (! str_ends_with(rtrim($line), ';')) {
                continue;
            }

            $statement = trim($buffer);
            $buffer = '';

            if (preg_match('/^SET\s+/i', $statement)) {
                DB::unprepared($statement);
                $skipped++;

                continue;
            }

            if (! preg_match('/^INSERT\s+INTO/i', $statement)) {
                $skipped++;

                continue;
            }

            DB::unprepared($statement);
            $imported++;

            if ($imported % 500 === 0) {
                $this->line("  … {$imported} INSERT(s)");
            }
        }

        fclose($handle);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info("Import completado: {$imported} INSERT(s), {$skipped} sentencia(s) auxiliar(es).");

        return self::SUCCESS;
    }
}
