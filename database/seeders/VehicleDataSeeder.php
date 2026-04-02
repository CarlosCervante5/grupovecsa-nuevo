<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use App\Models\BrandLine;
use App\Models\LineModel;
use App\Models\VehicleBody;
use App\Models\Dealership;

class VehicleDataSeeder extends Seeder
{
    public function run()
    {
        // Clean up duplicate brands (keep only first of each name)
        $brandNames = VehicleBrand::select('name')->groupBy('name')->pluck('name');
        foreach ($brandNames as $name) {
            $duplicates = VehicleBrand::where('name', $name)->orderBy('id')->get();
            if ($duplicates->count() > 1) {
                VehicleBrand::where('name', $name)->where('id', '>', $duplicates->first()->id)->delete();
            }
        }

        // Marcas
        $bmw = VehicleBrand::firstOrCreate(['name' => 'BMW'], ['image_path' => 'bmw-logo.png']);
        $mini = VehicleBrand::firstOrCreate(['name' => 'MINI'], ['image_path' => 'mini-logo.png']);
        $motorrad = VehicleBrand::firstOrCreate(['name' => 'BMW Motorrad'], ['image_path' => 'motorrad-logo.png']);
        $chevrolet = VehicleBrand::firstOrCreate(['name' => 'Chevrolet'], ['image_path' => 'chevrolet-logo.png']);
        $gmc = VehicleBrand::firstOrCreate(['name' => 'GMC'], ['image_path' => 'gmc-logo.png']);

        // Líneas
        $x3Line = BrandLine::firstOrCreate(['name' => 'X3', 'brand_id' => $bmw->id]);
        $x4Line = BrandLine::firstOrCreate(['name' => 'X4', 'brand_id' => $bmw->id]);
        $cooperLine = BrandLine::firstOrCreate(['name' => 'Cooper', 'brand_id' => $mini->id]);
        $trackerLine = BrandLine::firstOrCreate(['name' => 'Tracker', 'brand_id' => $chevrolet->id]);

        // Modelos
        LineModel::firstOrCreate(['name' => 'X3 30e xDrive', 'line_id' => $x3Line->id], ['year' => 2025]);
        LineModel::firstOrCreate(['name' => 'X3 M40i', 'line_id' => $x3Line->id], ['year' => 2024]);
        LineModel::firstOrCreate(['name' => 'Cooper C', 'line_id' => $cooperLine->id], ['year' => 2025]);
        LineModel::firstOrCreate(['name' => 'Tracker Premier', 'line_id' => $trackerLine->id], ['year' => 2023]);

        // Carrocerías
        VehicleBody::firstOrCreate(['name' => 'SUV']);
        VehicleBody::firstOrCreate(['name' => 'Hatchback']);
        VehicleBody::firstOrCreate(['name' => 'Sedan']);
        VehicleBody::firstOrCreate(['name' => 'Coupe']);

        // Concesionarios
        Dealership::firstOrCreate(['name' => 'VECSA Hidalgo'], ['location' => 'hidalgo', 'description' => 'Concesionario BMW en Pachuca, Hidalgo']);
        Dealership::firstOrCreate(['name' => 'VECSA Puebla'], ['location' => 'puebla', 'description' => 'Concesionario BMW en Puebla, Puebla']);

        $this->command->info('Datos de vehículos creados exitosamente!');
    }
}
