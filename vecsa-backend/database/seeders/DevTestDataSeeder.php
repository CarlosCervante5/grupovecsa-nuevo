<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class DevTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $prefix = env('DB_TABLE_PREFIX', '');

        // ── Testimonials ──
        $testCount = DB::table($prefix . 'home_testimonials')->whereNull('deleted_at')->count();
        if ($testCount === 0) {
            for ($i = 1; $i <= 5; $i++) {
                DB::table($prefix . 'home_testimonials')->insert([
                    'uuid' => (string) Uuid::uuid4(),
                    'sort_id' => $i,
                    'image_path' => 'testimonials/test-' . $i . '.jpg',
                    'alt' => 'Testimonio de prueba ' . $i,
                    'active' => $i <= 3 ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('✓ 5 testimonios creados');
        }

        // ── Appointments ──
        $customers = DB::table($prefix . 'customers')->whereNull('deleted_at')->take(4)->get();
        $apptCount = DB::table($prefix . 'customer_appointments')->whereNull('deleted_at')->count();
        if ($apptCount === 0 && $customers->count() > 0) {
            $types = ['valuation', 'service', 'valuation', 'service'];
            $statuses = ['scheduled', 'completed', 'in_progress', 'cancelled'];
            $dealers = ['BMW Puebla Angelópolis', 'BMW Pachuca', 'BMW Oaxaca', 'BMW Veracruz'];

            foreach ($customers->take(4) as $idx => $cust) {
                $apptId = DB::table($prefix . 'customer_appointments')->insertGetId([
                    'uuid' => (string) Uuid::uuid4(),
                    'type' => $types[$idx],
                    'description' => 'Cita de prueba ' . ($idx + 1),
                    'scheduled_date' => now()->addDays($idx + 1)->format('Y-m-d H:i:s'),
                    'dealership_name' => $dealers[$idx],
                    'status' => $statuses[$idx],
                    'customer_id' => $cust->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create valuation for valuation-type appointments
                if ($types[$idx] === 'valuation') {
                    DB::table($prefix . 'vehicle_valuations')->insert([
                        'uuid' => (string) Uuid::uuid4(),
                        'status' => $statuses[$idx] === 'completed' ? 'completed' : 'pending',
                        'status_repairs' => 'pending',
                        'status_parts' => 'pending',
                        'status_acquisition' => 'pending',
                        'appointment_id' => $apptId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $this->command->info('✓ 4 citas y 2 valuaciones creadas');
        }

        $this->command->info('✓ Datos de prueba completados');
    }
}
