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
        $bmw = VehicleBrand::whereRaw('LOWER(name) = ?', ['bmw'])->first();
        $mini = VehicleBrand::whereRaw('LOWER(name) = ?', ['mini'])->first();

        if (!$bmw || !$mini) {
            $this->command->warn('Marcas BMW/MINI no encontradas. Ejecuta VehicleDataSeeder primero.');
            return;
        }

        $x3Line = BrandLine::whereRaw('LOWER(name) = ?', ['x3'])->first();
        $cooperLine = BrandLine::whereRaw('LOWER(name) = ?', ['cooper'])->first();
        if (!$x3Line || !$cooperLine) { $this->command->warn('Líneas no encontradas: x3=' . ($x3Line ? 'OK' : 'NULL') . ' cooper=' . ($cooperLine ? 'OK' : 'NULL')); return; }

        $x3Model = LineModel::whereRaw('LOWER(name) LIKE ?', ['%x3 30e%'])->first();
        $cooperModel = LineModel::whereRaw('LOWER(name) LIKE ?', ['%cooper c%'])->first();
        if (!$x3Model || !$cooperModel) { $this->command->warn('Modelos no encontrados: x3Model=' . ($x3Model ? 'OK' : 'NULL') . ' cooperModel=' . ($cooperModel ? 'OK' : 'NULL')); return; }

        $suv = VehicleBody::where('name', 'SUV')->first();
        $hatchback = VehicleBody::where('name', 'Hatchback')->first();
        $dealer1 = Dealership::first();
        $dealer2 = Dealership::skip(1)->first() ?? $dealer1;

        $x3Version = ModelVersion::firstOrCreate(
            ['name' => '30e xDrive', 'model_id' => $x3Model->id],
            ['description' => 'Versión híbrida enchufable del BMW X3']
        );
        $cooperVersion = ModelVersion::firstOrCreate(
            ['name' => 'C 5 Puertas', 'model_id' => $cooperModel->id],
            ['description' => 'Versión de 5 puertas del MINI Cooper']
        );

        $vehicles = [
            [
                'vin' => 'WBA65GP09SN323721',
                'name' => 'BMW X3 30e xDrive',
                'description' => 'BMW X3 30e xDrive 2025 híbrido enchufable, 299 hp.',
                'sale_price' => 1570800, 'list_price' => 1494900, 'mileage' => 0,
                'type' => 'car', 'category' => 'new', 'cylinders' => 4,
                'interior_color' => 'espresso brown', 'exterior_color' => 'alpine white',
                'transmission' => 'automatic', 'fuel_type' => 'hybrid', 'page_status' => 'active',
                'brand_id' => $bmw->id, 'line_id' => $x3Line->id, 'model_id' => $x3Model->id,
                'version_id' => $x3Version->id, 'body_id' => $suv?->id, 'dealership_id' => $dealer1?->id,
            ],
            [
                'vin' => 'WMW41GD04S2W65439',
                'name' => 'MINI Cooper C 5 Puertas',
                'description' => 'MINI Cooper C 5 Puertas, motor turbo 3 cilindros, 156 hp.',
                'sale_price' => 721900, 'list_price' => 721900, 'mileage' => 0,
                'type' => 'car', 'category' => 'new', 'cylinders' => 3,
                'interior_color' => 'grey / cloth blue', 'exterior_color' => 'british racing green',
                'transmission' => 'automatic', 'fuel_type' => 'gasoline', 'page_status' => 'active',
                'brand_id' => $mini->id, 'line_id' => $cooperLine->id, 'model_id' => $cooperModel->id,
                'version_id' => $cooperVersion->id, 'body_id' => $hatchback?->id, 'dealership_id' => $dealer2?->id,
            ],
            [
                'vin' => 'WBA85DP06RN250731',
                'name' => 'BMW X3 M40i (Seminuevo)',
                'description' => 'Seminuevo verificado, un año de garantía.',
                'sale_price' => 1120000, 'list_price' => 1120000, 'mileage' => 17480,
                'type' => 'car', 'category' => 'pre_owned', 'cylinders' => 6,
                'interior_color' => 'marron', 'exterior_color' => 'brooklyn grey',
                'transmission' => 'automatic', 'fuel_type' => 'gasoline', 'page_status' => 'active',
                'brand_id' => $bmw->id, 'line_id' => $x3Line->id, 'model_id' => $x3Model->id,
                'version_id' => $x3Version->id, 'body_id' => $suv?->id, 'dealership_id' => $dealer1?->id,
            ],
        ];

        foreach ($vehicles as $v) {
            $created = Vehicle::firstOrCreate(
                ['vin' => $v['vin']],
                array_merge($v, ['uuid' => Uuid::uuid4()->toString()])
            );
            $this->command->info('Vehicle: ' . $v['name'] . ' - ' . ($created->wasRecentlyCreated ? 'CREATED' : 'EXISTS'));
        }

        $this->command->info('Inventario de vehículos creado exitosamente!');
    }
}
