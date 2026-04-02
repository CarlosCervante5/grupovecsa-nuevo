<?php

namespace App\Services\Incadea;

use App\Models\Boutique\BoutiqueCategory;

class CategoryMapper
{
    /**
     * Mapping table: Incadea category name → Boutique category name.
     */
    protected array $mapping = [
        'Tires'                        => 'Llantas y rines',
        'Complete wheels/Rim'          => 'Llantas y rines',
        'Car accesories'               => 'Accesorios para vehiculo',
        'Life Style Accesories'        => 'Life Style',
        'Workshop Equipment'           => 'Accesorios para vehiculo',
        'Operating/Auxiliary material'  => 'Clean & Care',
        'Original Part'                => 'Accesorios para vehiculo',
        'Exchange part'                => 'Accesorios para vehiculo',
    ];

    /**
     * Resolve an Incadea category to a Boutique category ID.
     */
    public function resolve(string $incadeaCategory): ?int
    {
        $boutiqueName = $this->mapping[$incadeaCategory] ?? null;

        if ($boutiqueName === null) {
            return null;
        }

        return BoutiqueCategory::where('name', $boutiqueName)->value('id');
    }

    /**
     * Return the full mapping table.
     */
    public function getMappingTable(): array
    {
        return $this->mapping;
    }
}
