<?php

namespace Database\Seeders;

use App\Models\MarketingEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Eventos de prueba para la home de Experience (carrusel + calendario).
 * Idempotente: no duplica mientras haya al menos un demo con fecha >= hoy.
 * Si todos los demo_exp_ui quedaron en el pasado (p. ej. sandbox antiguo), los reemplaza.
 */
class ExperienceDemoEventsSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $todayStr = $today->toDateString();

        $demos = MarketingEvent::withTrashed()
            ->where('type', 'experience')
            ->where('segment_name', 'demo_exp_ui')
            ->get();

        if ($demos->isNotEmpty()) {
            $hasUpcoming = $demos->contains(function (MarketingEvent $e) use ($todayStr) {
                if ($e->trashed()) {
                    return false;
                }
                if (! $e->begin_date) {
                    return false;
                }

                return Carbon::parse($e->begin_date)->toDateString() >= $todayStr;
            });

            if ($hasUpcoming) {
                $this->command?->info('Experience: ya hay eventos demo futuros (demo_exp_ui). No se modificaron.');

                return;
            }

            MarketingEvent::withTrashed()
                ->where('type', 'experience')
                ->where('segment_name', 'demo_exp_ui')
                ->forceDelete();

            $this->command?->info('Experience: eventos demo estaban vencidos; se recrearon con fechas nuevas.');
        }

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
