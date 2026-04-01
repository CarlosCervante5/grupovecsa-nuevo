<?php

namespace Database\Seeders;

use App\Models\Boutique\BoutiqueCategory;
use Illuminate\Database\Seeder;

class CategoryHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $hierarchy = [
            'Life Style' => [
                'Camisetas','Sudaderas','Llaveros & Portallaves','Juguetes & Coleccionables',
                'Mochilas','Gorras','Bolsos','Carteras','Bicicletas','Tenis','Gafas',
                'Camisas','Pants','Paraguas','Relojes','Sombrillas','Thermos','Shorts','Overall',
            ],
            'Rider Gear & Garment' => [
                'Chamarras','Pantalones','Cascos','Guantes','Chalecos','Botas','Pasamontañas','Viseras',
            ],
            'Clean & Care' => [
                'Aceites','Selladores','Lubricantes','Liquidos Limpiaparabrisas',
                'Adhesivos','Aromatizantes','Ceras','Liquido de Frenos','Anticongelantes','Crystal Clarity',
            ],
            'Accesorios para vehiculo' => [
                'M Performance','Tapetes','Molduras','Escapes','Cadenas','Protectores de Radiador',
                'Guardamanos','Reposapies','Carcasas de Retrovisor','Espejos','Birlos de Seguridad',
                'Centros de Rin','Parabrisas','Portaequipaje','Contenedores',
                'Redes p/cajuela','Mesas plegables','Travel & Confort System','Ganchos','Soportes',
                'Asientos','Luces Delanteras','Cargadores','Cargadores de Batería','Luces Traseras',
                'Linterna LED','Linternas y Proyectores','Proyectores LED','Cargadores USB',
                'Navegadores','Bombas de Aire','Herramienta','Maletas y Alforjas',
                'Protectores de Faro','Protectores de Motor','Fundas para Moto',
                'Cajas de Techo','JCW','Bocinas','Pasadores de Puerta','Portabicicletas',
                'Barras Protectoras','Top Case','Cargador de Pared','Protectores de Manillar','Pantallas',
            ],
            'Llantas y rines' => ['Llantas','Rines'],
        ];

        $count = 0;
        foreach ($hierarchy as $parentName => $children) {
            $parent = BoutiqueCategory::where('name', $parentName)->first();
            if (!$parent) continue;
            foreach ($children as $childName) {
                $child = BoutiqueCategory::where('name', $childName)->whereNull('parent_id')->first();
                if ($child && $child->id !== $parent->id) {
                    $child->parent_id = $parent->id;
                    $child->save();
                    $count++;
                }
            }
        }

        $this->command->info("Updated $count categories with parent_id");
    }
}
