<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkValuationsSeeder extends Seeder
{
    public function run(): void
    {
        $prefix = env('DB_TABLE_PREFIX', '');
        $devUser = User::where('email', 'dev@vecsa.com')->first();

        if (!$devUser) {
            $this->command->error('Dev user not found');
            return;
        }

        $vals = DB::table($prefix . 'vehicle_valuations')->whereNull('deleted_at')->get();
        $linked = 0;

        foreach ($vals as $v) {
            $exists = DB::table($prefix . 'user_valuation')
                ->where('user_id', $devUser->id)
                ->where('valuation_id', $v->id)
                ->exists();

            if (!$exists) {
                DB::table($prefix . 'user_valuation')->insert([
                    'user_role_name' => 'developer',
                    'user_id' => $devUser->id,
                    'valuation_id' => $v->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $linked++;
            }
        }

        $this->command->info("✓ Linked {$linked} valuations to dev user (total: {$vals->count()})");
    }
}
