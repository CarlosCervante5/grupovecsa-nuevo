<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ValuatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::findByName('valuator');

        // Create demo users
        $user = User::factory()->create([
            'nickname' => 'cesar_fuentes',
            'email' => 'cesar_fuentes@vecsa.com',
            'password' => 'CesarFuentes%2024%%'
        ]);
        $user->assignRole($role);

        $user->userProfile()->create([
            'name' => 'Cesar',
            'last_name' => 'Fuentes'
        ]);

        $user = User::factory()->create([
            'nickname' => 'fabian_tapia',
            'email' => 'fabian_tapia@vecsa.com',
            'password' => 'FabianTapia%2024%%'
        ]);
        $user->assignRole($role);

        $user->userProfile()->create([
            'name' => 'Fabian',
            'last_name' => 'Tapia'
        ]);

    }
}
