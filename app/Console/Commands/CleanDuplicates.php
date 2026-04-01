<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDuplicates extends Command
{
    protected $signature = 'clean:duplicates';
    protected $description = 'Remove duplicate records created by repeated seeder runs';

    public function handle(): int
    {
        $prefix = env('DB_TABLE_PREFIX', '');

        // Clean duplicate slides (keep first by title)
        $this->cleanTable($prefix . 'home_slides', 'title');
        // Clean duplicate testimonials (keep first by image_path)
        $this->cleanTable($prefix . 'home_testimonials', 'image_path');
        // Clean duplicate banners (keep first by title)
        $this->cleanTable($prefix . 'boutique_banners', 'title');
        // Clean duplicate brands (keep first by name)
        $this->cleanTable($prefix . 'vehicle_brands', 'name');
        // Clean duplicate brand lines (keep first by name)
        $this->cleanTable($prefix . 'brand_lines', 'name');
        // Clean duplicate line models (keep first by name)
        $this->cleanTable($prefix . 'line_models', 'name');
        // Clean duplicate vehicle bodies (keep first by name)
        $this->cleanTable($prefix . 'vehicle_bodies', 'name');
        // Clean duplicate dealerships (keep first by name)
        $this->cleanTable($prefix . 'dealerships', 'name');

        $this->info('✅ Duplicates cleaned!');
        return Command::SUCCESS;
    }

    private function cleanTable(string $table, string $uniqueColumn): void
    {
        try {
            $deleted = DB::statement("
                DELETE t1 FROM `{$table}` t1
                INNER JOIN `{$table}` t2
                WHERE t1.id > t2.id AND t1.`{$uniqueColumn}` = t2.`{$uniqueColumn}`
            ");
            $count = DB::table($table)->count();
            $this->info("  {$table}: {$count} records remaining");
        } catch (\Exception $e) {
            $this->warn("  {$table}: skipped ({$e->getMessage()})");
        }
    }
}
