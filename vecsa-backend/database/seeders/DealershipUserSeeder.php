<?php

namespace Database\Seeders;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealershipUserSeeder extends Seeder
{
    public function run(): void
    {
        $dealerships = Dealership::all();
        if ($dealerships->isEmpty()) {
            $this->command->warn('No dealerships found, skipping');
            return;
        }

        $users = User::whereHas('userProfile')->get();
        $dealershipIds = $dealerships->pluck('id')->toArray();
        $assigned = 0;

        foreach ($users as $user) {
            if ($user->dealerships()->count() > 0) continue;

            // Assign 1-3 random dealerships per user
            $count = min(rand(1, 3), count($dealershipIds));
            $randomIds = array_rand(array_flip($dealershipIds), $count);
            if (!is_array($randomIds)) $randomIds = [$randomIds];

            $user->dealerships()->sync($randomIds);
            $assigned++;
        }

        $this->command->info("✓ Assigned dealerships to {$assigned} users");
    }
}
