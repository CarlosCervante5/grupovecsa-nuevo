<?php

namespace App\Console\Commands;

use App\Services\WordPressExperienceImportService;
use Illuminate\Console\Command;

class ImportWordPressExperienceCommand extends Command
{
    protected $signature = 'experience:import-wordpress
                            {--url=https://vecsaexperience.com : URL base del sitio WordPress}
                            {--limit= : Máximo de entradas a importar (omitir = todas)}';

    protected $description = 'Importa posts publicados de WordPress como historias Experience (marketing_posts)';

    public function handle(WordPressExperienceImportService $import): int
    {
        $base = (string) $this->option('url');
        $limit = $this->option('limit');
        $limit = $limit !== null && $limit !== '' ? (int) $limit : null;

        $this->info('Importando desde: '.$base);

        $result = $import->importFromRest($base, $limit);

        $this->info('Importadas/actualizadas: '.$result['imported']);
        $this->info('Omitidas por error: '.$result['skipped']);
        foreach ($result['errors'] as $err) {
            $this->warn($err);
        }

        return Command::SUCCESS;
    }
}
