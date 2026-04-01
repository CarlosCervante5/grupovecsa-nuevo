<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class TestCustomersSeeder extends Seeder
{
    public function run(): void
    {
        $prefix = env('DB_TABLE_PREFIX', '');

        // Create a test reward
        $rewardId = DB::table($prefix . 'rewards')->insertGetId([
            'uuid' => (string) Uuid::uuid4(),
            'name' => 'Programa Riders 2026',
            'description' => 'Programa de lealtad',
            'type' => 'loyalty',
            'begin_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $users = User::take(5)->get();

        $names = [
            ['Carlos', 'García López', 'M', '5551234567', 'carlos.garcia@example.com', 'Puebla'],
            ['María', 'Hernández Ruiz', 'F', '5559876543', 'maria.hdz@example.com', 'Pachuca'],
            ['Roberto', 'Martínez Soto', 'M', '5554567890', 'roberto.mtz@example.com', 'Oaxaca'],
            ['Ana', 'López Vega', 'F', '5557654321', 'ana.lopez@example.com', 'Veracruz'],
            ['Jorge', 'Ramírez Cruz', 'M', '5552345678', 'jorge.ramirez@example.com', 'Puebla'],
            ['Laura', 'Sánchez Díaz', 'F', '5558765432', 'laura.sanchez@example.com', 'Pachuca'],
            ['Pedro', 'Torres Mendoza', 'M', '5553456789', 'pedro.torres@example.com', 'Oaxaca'],
            ['Sofía', 'Flores Reyes', 'F', '5556543210', 'sofia.flores@example.com', 'Veracruz'],
        ];

        foreach ($names as $i => $n) {
            $userId = isset($users[$i]) ? $users[$i]->id : ($users[0]->id ?? null);

            $custId = DB::table($prefix . 'customers')->insertGetId([
                'uuid'           => (string) Uuid::uuid4(),
                'name'           => $n[0],
                'last_name'      => $n[1],
                'gender'         => $n[2],
                'cellphone'      => $n[3],
                'email_1'        => $n[4],
                'origin_agency'  => $n[5],
                'phone_1'        => $n[3],
                'user_id'        => $userId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $crId = DB::table($prefix . 'customer_reward')->insertGetId([
                'uuid'        => (string) Uuid::uuid4(),
                'status'      => 'active',
                'reward_id'   => $rewardId,
                'customer_id' => $custId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::table($prefix . 'reward_points')->insert([
                'uuid'               => (string) Uuid::uuid4(),
                'customer_reward_id' => $crId,
                'earned_points'      => rand(50, 500),
                'redeemed'           => 0,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        $this->command->info('✓ Created ' . count($names) . ' test customers with rewards');
    }
}
