<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use App\Models\BrandLine;
use App\Models\LineModel;
use App\Models\VehicleBody;
use App\Models\Dealership;
use App\Models\ModelVersion;
use App\Models\Vehicle;
use Ramsey\Uuid\Uuid;

class CompleteVecsaSeeder extends Seeder
{
    public function run()
    {
        // 1. Crear marcas
        $bmw = VehicleBrand::create(['name' => 'BMW', 'image_path' => 'bmw-logo.png']);
        $mini = VehicleBrand::create(['name' => 'MINI', 'image_path' => 'mini-logo.png']);
        $chevrolet = VehicleBrand::create(['name' => 'Chevrolet', 'image_path' => 'chevrolet-logo.png']);

        // 2. Crear tipos de carrocería
        $suv = VehicleBody::create(['name' => 'SUV']);
        $hatchback = VehicleBody::create(['name' => 'Hatchback']);
        $sedan = VehicleBody::create(['name' => 'Sedan']);

        // 3. Crear concesionarios
        $hidalgo = Dealership::create([
            'name' => 'VECSA Hidalgo',
            'location' => 'hidalgo',
            'description' => 'Concesionario BMW en Pachuca, Hidalgo'
        ]);

        $puebla = Dealership::create([
            'name' => 'VECSA Puebla',
            'location' => 'puebla',
            'description' => 'Concesionario BMW en Puebla, Puebla'
        ]);

        // 4. Crear líneas BMW
        $x3Line = BrandLine::create(['name' => 'X3', 'brand_id' => $bmw->id]);
        $x4Line = BrandLine::create(['name' => 'X4', 'brand_id' => $bmw->id]);

        // 5. Crear líneas MINI
        $cooperLine = BrandLine::create(['name' => 'Cooper', 'brand_id' => $mini->id]);

        // 6. Crear líneas Chevrolet
        $trackerLine = BrandLine::create(['name' => 'Tracker', 'brand_id' => $chevrolet->id]);

        // 7. Crear modelos BMW X3
        $x3Hybrid = LineModel::create([
            'name' => 'X3 30e xDrive',
            'year' => 2025,
            'line_id' => $x3Line->id
        ]);

        $x3M40i = LineModel::create([
            'name' => 'X3 M40i',
            'year' => 2024,
            'line_id' => $x3Line->id
        ]);

        // 8. Crear modelos MINI
        $cooper5Door = LineModel::create([
            'name' => 'Cooper C 5 Puertas',
            'year' => 2025,
            'line_id' => $cooperLine->id
        ]);

        // 9. Crear modelos Chevrolet
        $trackerPremier = LineModel::create([
            'name' => 'Tracker Premier',
            'year' => 2023,
            'line_id' => $trackerLine->id
        ]);

        // 10. Crear versiones
        $x3HybridVersion = ModelVersion::create([
            'name' => '30e xDrive',
            'description' => 'Versión híbrida enchufable',
            'model_id' => $x3Hybrid->id
        ]);

        $x3M40iVersion = ModelVersion::create([
            'name' => 'M40i',
            'description' => 'Versión deportiva M Performance',
            'model_id' => $x3M40i->id
        ]);

        $cooperVersion = ModelVersion::create([
            'name' => 'C 5 Puertas',
            'description' => 'Versión de 5 puertas',
            'model_id' => $cooper5Door->id
        ]);

        // 11. Crear vehículos de ejemplo
        Vehicle::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'BMW X3 30e xDrive (Híbrido/Automático)',
            'description' => 'BMW X3 30e xDrive 2025 representa la evolución de la conducción híbrida enchufable en el segmento de los SUV de lujo.',
            'vin' => 'WBA65GP09SN323721',
            'purchase_date' => '2025-03-17',
            'sale_price' => 1570800,
            'list_price' => 1494900,
            'mileage' => 0,
            'type' => 'car',
            'category' => 'new',
            'cylinders' => 4,
            'interior_color' => 'veganza espresso brown',
            'exterior_color' => 'alpine white',
            'transmission' => 'automatic',
            'fuel_type' => 'hybrid',
            'page_status' => 'active',
            'brand_id' => $bmw->id,
            'line_id' => $x3Line->id,
            'model_id' => $x3Hybrid->id,
            'version_id' => $x3HybridVersion->id,
            'body_id' => $suv->id,
            'dealership_id' => $hidalgo->id
        ]);

        Vehicle::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'BMW X3 M40i',
            'description' => '¡Adquiere un seminuevo ya mismo! Un año de garantía. Unidades verificadas por revisión de 100 puntos de calidad.',
            'vin' => 'WBA85DP06RN250731',
            'purchase_date' => '2025-05-06',
            'sale_price' => 1120000,
            'list_price' => 1120000,
            'mileage' => 17480,
            'type' => 'car',
            'category' => 'pre_owned',
            'cylinders' => 6,
            'interior_color' => 'marron',
            'exterior_color' => 'brooklyn grey',
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'page_status' => 'active',
            'brand_id' => $bmw->id,
            'line_id' => $x3Line->id,
            'model_id' => $x3M40i->id,
            'version_id' => $x3M40iVersion->id,
            'body_id' => $suv->id,
            'dealership_id' => $hidalgo->id
        ]);

        Vehicle::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'MINI Cooper C 5 Puertas (Automático)',
            'description' => 'MINI Cooper C 5 Puertas combina el icónico diseño británico con mayor practicidad gracias a sus cinco puertas.',
            'vin' => 'WMW41GD04S2W65439',
            'purchase_date' => '2024-12-10',
            'sale_price' => 721900,
            'list_price' => 721900,
            'mileage' => 0,
            'type' => 'car',
            'category' => 'new',
            'cylinders' => 3,
            'interior_color' => 'grey cloth blue',
            'exterior_color' => 'british racing green iv',
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'page_status' => 'active',
            'brand_id' => $mini->id,
            'line_id' => $cooperLine->id,
            'model_id' => $cooper5Door->id,
            'version_id' => $cooperVersion->id,
            'body_id' => $hatchback->id,
            'dealership_id' => $puebla->id
        ]);

        $this->command->info('Base de datos VECSA poblada exitosamente!');
        $this->command->info('Marcas creadas: ' . VehicleBrand::count());
        $this->command->info('Vehículos creados: ' . Vehicle::count());
        $this->command->info('Concesionarios creados: ' . Dealership::count());
    }
} 