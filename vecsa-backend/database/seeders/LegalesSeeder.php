<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use App\Support\LegalDocumentRegistry;
use Illuminate\Database\Seeder;

/**
 * Idempotente: solo inserta slugs que aún no existen (no sobrescribe ediciones del admin).
 * Ejecutar manualmente: php artisan db:seed --class=LegalesSeeder --force
 * También se invoca al crear la tabla en la migración (una sola vez por entorno).
 */
class LegalesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (LegalDocumentRegistry::DOCUMENTS as $slug => $meta) {
            if (LegalDocument::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $body = $this->loadSeedBody($meta['seed_file'] ?? null, $meta['title']);

            LegalDocument::query()->create([
                'slug' => $slug,
                'title' => $meta['title'],
                'body_html' => $body,
                'meta_description' => $meta['meta_description'],
                'is_published' => true,
            ]);
        }
    }

    private function loadSeedBody(?string $filename, string $title): string
    {
        if ($filename === null) {
            return '<p>Contenido pendiente de configuración.</p>';
        }

        $path = database_path('seeders/data/legal/'.$filename);
        if (! is_file($path)) {
            return '<h2>'.e($title).'</h2><p>Contenido pendiente de configuración.</p>';
        }

        return trim((string) file_get_contents($path));
    }
}
