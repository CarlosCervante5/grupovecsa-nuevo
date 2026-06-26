<?php

namespace Database\Seeders;

use App\Models\Boutique\BoutiqueCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Sincroniza categorías padre / subcategoría / sub-subcategoría desde
 * database/data/boutique-category-hierarchy.json (generado desde CATEGORIAS Y SUBCATEGORIAS.xlsx).
 */
class SyncBoutiqueCategoryHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/boutique-category-hierarchy.json');
        if (! File::isFile($path)) {
            $this->command?->error('No se encontró boutique-category-hierarchy.json');

            return;
        }

        /** @var array<string, array<string, list<string>>> $hierarchy */
        $hierarchy = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        $created = 0;
        $updated = 0;

        foreach ($hierarchy as $parentName => $subcategories) {
            $parentName = trim((string) $parentName);
            if ($parentName === '') {
                continue;
            }

            $parent = $this->upsertCategory($parentName, null, $created, $updated);

            foreach ($subcategories as $sub1Name => $sub2Names) {
                $sub1Name = trim((string) $sub1Name);
                if ($sub1Name === '') {
                    continue;
                }

                $sub1 = $this->upsertCategory($sub1Name, (int) $parent->id, $created, $updated);

                foreach ($sub2Names as $sub2Name) {
                    $sub2Name = trim((string) $sub2Name);
                    if ($sub2Name === '') {
                        continue;
                    }
                    $this->upsertCategory($sub2Name, (int) $sub1->id, $created, $updated);
                }
            }
        }

        $this->command?->info("SyncBoutiqueCategoryHierarchySeeder: {$created} creada(s), {$updated} actualizada(s).");
    }

    private function upsertCategory(string $name, ?int $parentId, int &$created, int &$updated): BoutiqueCategory
    {
        $existing = BoutiqueCategory::query()
            ->where('name', $name)
            ->when($parentId === null, fn ($q) => $q->whereNull('parent_id'))
            ->when($parentId !== null, fn ($q) => $q->where('parent_id', $parentId))
            ->first();

        if ($existing) {
            return $existing;
        }

        if ($parentId !== null) {
            $orphan = BoutiqueCategory::query()->where('name', $name)->whereNull('parent_id')->first();
            if ($orphan) {
                $orphan->parent_id = $parentId;
                $orphan->save();
                $updated++;

                return $orphan;
            }
        }

        $model = BoutiqueCategory::create([
            'name' => $name,
            'description' => null,
            'active' => true,
            'parent_id' => $parentId,
        ]);
        $created++;

        return $model;
    }
}
