<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class StregaFollowUpSurveysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Inicia Mecánica y Eléctrica
        DB::table(env('DB_TABLE_PREFIX_STREGA', '') . 'follow-up_surveys')->insert([
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 0,
                'name' => 'Queja o ticket',
                'description' => 'Queja o ticket sobre el servicio.',
                'values' => '',
                'status' => 'active',
                'question_type' => 'abierta',
                'element_type' => 'select',
                'group_name' => 'FollowUpSurvey'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 1,
                'name' => 'CSI',
                'description' => 'Calificacion en customer satisfaction index',
                'values' => '10,9,8,7,6,5,4,3,2,1',
                'status' => 'active',
                'question_type' => 'cerrada',
                'element_type' => 'select',
                'group_name' => 'FollowUpSurvey'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 2,
                'name' => 'Satisfacción',
                'description' => 'Nivel de satisfacción',
                'values' => 'Completamente satisfecho, Muy satisfecho, Algo satisfecho, Poco satisfecho, Nada satisfecho',
                'status' => 'active',
                'question_type' => 'cerrada',
                'element_type' => 'select',
                'group_name' => 'FollowUpSurvey'
            ],
            [
                'uuid' => Uuid::uuid4(),
                'sort_id' => 3,
                'name' => 'Ofrecimiento de test drive',
                'description' => '¿Se ofreció un test drive en la cita?',
                'values' => 'si,no',
                'status' => 'active',
                'question_type' => 'cerrada',
                'element_type' => 'select',
                'group_name' => 'FollowUpSurvey'
            ],
            // [
            //     'uuid' => Uuid::uuid4(),
            //     'sort_id' => 0,
            //     'name' => 'Status',
            //     'description' => 'Estatus de contactación para encuesta de satisfacción',
            //     'values' => 'Contactado,no contactado',
            //     'status' => 'active',
            //     'question_type' => 'cerrada',
            //     'element_type' => 'select',
            //     'group_name' => 'FollowUpSurvey'
            // ],
            // [
            //     'uuid' => Uuid::uuid4(),
            //     'sort_id' => 1,
            //     'name' => 'No encuestado',
            //     'description' => 'Motivo de no encuestado',
            //     'values' => '',
            //     'status' => 'active',
            //     'question_type' => 'cerrada',
            //     'element_type' => 'select',
            //     'group_name' => 'FollowUpSurvey'
            // ],
            // [
            //     'uuid' => Uuid::uuid4(),
            //     'sort_id' => 3,
            //     'name' => 'Comentario sobre la queja',
            //     'description' => 'Comentario sobre queja o ticket sobre el servicio.',
            //     'values' => '',
            //     'status' => 'active',
            //     'question_type' => 'abierta',
            //     'element_type' => 'select',
            //     'group_name' => 'FollowUpSurvey'
            // ],
            
            // [
            //     'uuid' => Uuid::uuid4(),
            //     'sort_id' => 6,
            //     'name' => 'Comentarios',
            //     'description' => 'Comentario sobre la atención brindada',
            //     'values' => '',
            //     'status' => 'active',
            //     'question_type' => 'abierta',
            //     'element_type' => 'select',
            //     'group_name' => 'FollowUpSurvey'
            // ],
        ]);
    }
}
