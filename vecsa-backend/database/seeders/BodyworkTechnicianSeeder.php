<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BodyworkTechnicianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::findByName('bodywork_paint_technician');

        // Create demo users
        $user = User::factory()->create([
            'nickname' => 'bodywork_technician',
            'email' => 'bodywork_technician@vecsa.com',
            'password' => 'BodyworkTechnician%2024%%'
        ]);
        
        $user->assignRole($role);
        
    }
}
