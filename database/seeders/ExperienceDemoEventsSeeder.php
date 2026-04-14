<?php

namespace Database\Seeders;

use App\Models\MarketingEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Eventos de prueba para la home de Experience (carrusel + calendario).
 * Idempotente: no duplica si ya existen filas con segment_name = demo_exp_ui.
 */
class ExperienceDemoEventsSeeder extends Seeder
{
    public function run(): void
    {
        $already = MarketingEvent::withTrashed()
            ->where('type', 'experience')
            ->where('segment_name', 'demo_exp_ui')
            ->exists();

        if ($already) {
            $this->command?->info('Experience: ya hay eventos demo (segment_name=demo_exp_ui). No se insertaron duplicados.');

            return;
        }

        $today = Carbon::today();

        $rows = [
            [
                'name' => 'Rodada a Cabañas Los Pinares',
                'begin_plus' => 8,
                'end_plus' => 8,
                'location' => 'Salida BMW Motorrad VECSA Hidalgo',
            ],
            [
                'name' => 'Rodada Honey Puebla',
                'begin_plus' => 12,
                'end_plus' => 12,
                'location' => 'Honey, Puebla',
            ],
            [
                'name' => 'Clínica de manejo off-road',
                'begin_plus' => 18,
                'end_plus' => 18,
                'location' => 'Instalaciones VECSA',
            ],
            [
                'name' => 'Noche de comunidad rider',
                'begin_plus' => 22,
                'end_plus' => 22,
                'location' => 'Showroom VECSA Hidalgo · 17:30',
            ],
            [
                'name' => 'Presentación nueva gama GS',
                'begin_plus' => 28,
                'end_plus' => 28,
                'location' => 'VECSA Hidalgo',
            ],
        ];

        foreach ($rows as $row) {
            $begin = $today->copy()->addDays($row['begin_plus']);
            $end = $today->copy()->addDays($row['end_plus']);

            MarketingEvent::create([
                'begin_date' => $begin->toDateString(),
                'end_date' => $end->toDateString(),
                'name' => $row['name'],
                'segment_name' => 'demo_exp_ui',
                'type' => 'experience',
                'page_status' => 'public',
                'location' => $row['location'],
                'description' => 'Evento de demostración para probar el carrusel y el calendario en VECSA Experience.',
                'image_path' => null,
            ]);
        }

        $this->command?->info('Experience: creados '.count($rows).' eventos demo (futuros respecto a hoy).');
    }
}
