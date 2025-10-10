<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class StregaFormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Inicia Mecánica y Eléctrica
        DB::table(env('DB_TABLE_PREFIX_STREGA', '') . 'forms')->insert([
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Modelo de interés',
                'description' => 'Nombre del modelo de interés del lead',
                'values' => '',
                'status' => 'active',
                'question_type' => 'input',
                'element_type' => 'select',
                'group_name' => 'LeadInformation'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 1,
                'name' => 'Marca de interés',
                'description' => 'Marca de interés del lead',
                'values' => '',
                'status' => 'active',
                'question_type' => 'input',
                'element_type' => 'select',
                'group_name' => 'LeadInformation'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 2,
                'name' => 'Tiempo estimado para compra',
                'description' => 'Tiempo que el lead considera que tendrá la oportunidad de comprar',
                'values' => '',
                'status' => 'active',
                'question_type' => 'input',
                'element_type' => 'select',
                'group_name' => 'LeadInformation'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 3,
                'name' => 'Tipo de compra',
                'description' => 'Compra por crédito? contado? plan de renta?',
                'values' => '',
                'status' => 'active',
                'question_type' => 'input',
                'element_type' => 'select',
                'group_name' => 'LeadInformation'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 4,
                'name' => 'Inversión inicial',
                'description' => 'La cantidad a invertir al momento de la compra',
                'values' => '',
                'status' => 'active',
                'question_type' => 'input',
                'element_type' => 'select',
                'group_name' => 'LeadInformation'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 5,
                'name' => 'Comentarios',
                'description' => 'Comentarios que el cliente exprese sobre su solicitud en lead',
                'values' => '',
                'status' => 'active',
                'question_type' => 'input',
                'element_type' => 'select',
                'group_name' => 'LeadInformation'
            ],
        ]);
    }
}
