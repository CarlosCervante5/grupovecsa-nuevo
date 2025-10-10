<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleBrand;
use App\Models\BrandLine;
use App\Models\LineModel;
use App\Models\VehicleBody;
use App\Models\Dealership;
use App\Models\Vehicle;

class VehicleDataSeeder extends Seeder
{
    public function run()
    {
        // Crear marcas
        $bmw = VehicleBrand::create([
            'name' => 'BMW',
            'image_path' => 'bmw-logo.png'
        ]);

        $mini = VehicleBrand::create([
            'name' => 'MINI',
            'image_path' => 'mini-logo.png'
        ]);

        $motorrad = VehicleBrand::create([
            'name' => 'BMW Motorrad',
            'image_path' => 'motorrad-logo.png'
        ]);

        $chevrolet = VehicleBrand::create([
            'name' => 'Chevrolet',
            'image_path' => 'chevrolet-logo.png'
        ]);

        $gmc = VehicleBrand::create([
            'name' => 'GMC',
            'image_path' => 'gmc-logo.png'
        ]);

        // Crear líneas de marca
        $x3Line = BrandLine::create([
            'name' => 'X3',
            'brand_id' => $bmw->id
        ]);

        $x4Line = BrandLine::create([
            'name' => 'X4',
            'brand_id' => $bmw->id
        ]);

        $cooperLine = BrandLine::create([
            'name' => 'Cooper',
            'brand_id' => $mini->id
        ]);

        $trackerLine = BrandLine::create([
            'name' => 'Tracker',
            'brand_id' => $chevrolet->id
        ]);

        // Crear modelos
        $x3Model = LineModel::create([
            'name' => 'X3 30e xDrive',
            'year' => 2025,
            'line_id' => $x3Line->id
        ]);

        $x3M40iModel = LineModel::create([
            'name' => 'X3 M40i',
            'year' => 2024,
            'line_id' => $x3Line->id
        ]);

        $cooperModel = LineModel::create([
            'name' => 'Cooper C',
            'year' => 2025,
            'line_id' => $cooperLine->id
        ]);

        $trackerModel = LineModel::create([
            'name' => 'Tracker Premier',
            'year' => 2023,
            'line_id' => $trackerLine->id
        ]);

        // Crear tipos de carrocería
        $suv = VehicleBody::create(['name' => 'SUV']);
        $hatchback = VehicleBody::create(['name' => 'Hatchback']);
        $sedan = VehicleBody::create(['name' => 'Sedan']);
        $coupe = VehicleBody::create(['name' => 'Coupe']);

        // Crear concesionarios
        $vecsaHidalgo = Dealership::create([
            'name' => 'VECSA Hidalgo',
            'location' => 'hidalgo',
            'description' => 'Concesionario BMW en Pachuca, Hidalgo'
        ]);

        $vecsaPuebla = Dealership::create([
            'name' => 'VECSA Puebla',
            'location' => 'puebla',
            'description' => 'Concesionario BMW en Puebla, Puebla'
        ]);

        $this->command->info('Datos de vehículos creados exitosamente!');
    }
} 