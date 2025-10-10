<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PartsManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::findByName('spare_parts');

        // Create demo users
        $user = User::factory()->create([
            'nickname' => 'spare_parts',
            'email' => 'spare_parts@vecsa.com',
            'password' => 'SpareParts%2024%%'
        ]);
        
        $user->assignRole($role);
        
    }
}
