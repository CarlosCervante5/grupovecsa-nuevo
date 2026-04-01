<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class CampaignsSeeder extends Seeder
{
    public function run(): void
    {
        $prefix = env('DB_TABLE_PREFIX', '');

        $campaigns = [
            [
                'name'         => 'POSTVENTA BMW MARZO',
                'description'  => 'Promociones de postventa para vehículos BMW',
                'category'     => 'postventa',
                'segment_name' => 'BMW',
            ],
            [
                'name'         => 'POSTVENTA MINI MARZO',
                'description'  => 'Promociones de postventa para vehículos MINI',
                'category'     => 'postventa',
                'segment_name' => 'MINI',
            ],
            [
                'name'         => 'OFERTA COMERCIAL BMW MARZO',
                'description'  => 'Ofertas comerciales exclusivas BMW',
                'category'     => 'comercial',
                'segment_name' => 'BMW',
            ],
            [
                'name'         => 'SEMINUEVOS MARZO',
                'description'  => 'Promociones en vehículos seminuevos ejecutivos',
                'category'     => 'seminuevos',
                'segment_name' => 'Seminuevos',
            ],
        ];

        // Imágenes de prueba (CloudFront del sitio de producción)
        $sampleImages = [
            'https://d1ywfyeze82s0s.cloudfront.net/vecsa_promociones/51253054-64d4-49a4-bbee-b1032417b10a/1768261604_13.jpg',
            'https://d1ywfyeze82s0s.cloudfront.net/vecsa_promociones/51253054-64d4-49a4-bbee-b1032417b10a/1768253377_2.jpg',
            'https://d1ywfyeze82s0s.cloudfront.net/vecsa_promociones/51253054-64d4-49a4-bbee-b1032417b10a/1768253523_3.jpg',
        ];

        foreach ($campaigns as $campaignData) {
            $campaignId = DB::table($prefix . 'marketing_campaigns')->insertGetId([
                'uuid'        => (string) Uuid::uuid4(),
                'name'        => $campaignData['name'],
                'description' => $campaignData['description'],
                'category'    => $campaignData['category'],
                'segment_name'=> $campaignData['segment_name'],
                'begin_date'  => Carbon::now()->subDays(1),
                'end_date'    => Carbon::now()->addMonths(2),
                'page_status' => 'public',
                'visits'      => 0,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ]);

            foreach ($sampleImages as $index => $imagePath) {
                $promotionId = DB::table($prefix . 'marketing_promotions')->insertGetId([
                    'uuid'       => (string) Uuid::uuid4(),
                    'name'       => $campaignData['name'] . ' - Imagen ' . ($index + 1),
                    'sort_id'    => $index + 1,
                    'image_path' => $imagePath,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                DB::table($prefix . 'campaign_promotion')->insert([
                    'campaign_id'  => $campaignId,
                    'promotion_id' => $promotionId,
                ]);
            }
        }
    }
}
