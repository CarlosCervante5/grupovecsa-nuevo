<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\BrandLine;
use App\Models\LineModel;
use App\Models\VehicleBody;
use App\Models\Dealership;
use App\Models\ModelVersion;
use Ramsey\Uuid\Uuid;

class VehicleInventorySeeder extends Seeder
{
    public function run()
    {
        // Obtener marcas existentes
        $bmw = VehicleBrand::where('name', 'bmw')->first();
        $mini = VehicleBrand::where('name', 'mini')->first();
        $chevrolet = VehicleBrand::where('name', 'chevrolet')->first();

        // Obtener líneas existentes
        $x3Line = BrandLine::where('name', 'X3')->first();
        $cooperLine = BrandLine::where('name', 'Cooper')->first();
        $trackerLine = BrandLine::where('name', 'Tracker')->first();

        // Obtener modelos existentes
        $x3Model = LineModel::where('name', 'x3 30e xdrive')->first();
        $cooperModel = LineModel::where('name', 'cooper c')->first();

        // Obtener carrocerías
        $suv = VehicleBody::where('name', 'SUV')->first();
        $hatchback = VehicleBody::where('name', 'Hatchback')->first();

        // Obtener concesionarios
        $vecsaHidalgo = Dealership::where('name', 'vecsa hidalgo')->first();
        $vecsaPuebla = Dealership::where('name', 'vecsa puebla')->first();

        // Crear versiones de modelos
        $x3Version = ModelVersion::create([
            'name' => '30e xDrive',
            'description' => 'Versión híbrida enchufable del BMW X3',
            'model_id' => $x3Model->id
        ]);

        $cooperVersion = ModelVersion::create([
            'name' => 'C 5 Puertas',
            'description' => 'Versión de 5 puertas del MINI Cooper',
            'model_id' => $cooperModel->id
        ]);

        // Crear vehículos específicos basados en el respaldo
        Vehicle::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'BMW X3 30e xDrive (Híbrido/Automático)',
            'description' => 'BMW X3 30e xDrive 2025 representa la evolución de la conducción híbrida enchufable en el segmento de los SUV de lujo. Este vehículo combina un motor de gasolina de 4 cilindros con un sistema eléctrico, entregando una potencia total de 299 hp.',
            'vin' => 'WBA65GP09SN323721',
            'purchase_date' => '2025-03-17',
            'sale_price' => 1570800,
            'list_price' => 1494900,
            'mileage' => 0,
            'type' => 'car',
            'category' => 'new',
            'cylinders' => 4,
            'interior_color' => 'veganza | espresso brown',
            'exterior_color' => 'alpine white',
            'transmission' => 'automatic',
            'fuel_type' => 'hybrid',
            'page_status' => 'active',
            'brand_id' => $bmw->id,
            'line_id' => $x3Line->id,
            'model_id' => $x3Model->id,
            'version_id' => $x3Version->id,
            'body_id' => $suv->id,
            'dealership_id' => $vecsaHidalgo->id
        ]);

        Vehicle::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'MINI Cooper C 5 Puertas (Automático)',
            'description' => 'MINI Cooper C 5 Puertas combina el icónico diseño británico con mayor practicidad gracias a sus cinco puertas. Está equipado con un motor turbo de 3 cilindros y 1.5 litros, que entrega 156 hp.',
            'vin' => 'WMW41GD04S2W65439',
            'purchase_date' => '2024-12-10',
            'sale_price' => 721900,
            'list_price' => 721900,
            'mileage' => 0,
            'type' => 'car',
            'category' => 'new',
            'cylinders' => 3,
            'interior_color' => 'grey / cloth blue',
            'exterior_color' => 'british racing green iv',
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'page_status' => 'active',
            'brand_id' => $mini->id,
            'line_id' => $cooperLine->id,
            'model_id' => $cooperModel->id,
            'version_id' => $cooperVersion->id,
            'body_id' => $hatchback->id,
            'dealership_id' => $vecsaPuebla->id
        ]);

        // Crear un vehículo seminuevo
        Vehicle::create([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => 'BMW X3 M40i',
            'description' => '¡Adquiere un seminuevo ya mismo! Un año de garantía. Unidades verificadas por revisión de 100 puntos de calidad. Documentación 100% verificada.',
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
            'model_id' => $x3Model->id,
            'version_id' => $x3Version->id,
            'body_id' => $suv->id,
            'dealership_id' => $vecsaHidalgo->id
        ]);

        $this->command->info('Inventario de vehículos creado exitosamente!');
    }
} 