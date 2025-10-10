<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppointmentManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::findByName('appointment_manager');

        // Create demo users
        $user = User::factory()->create([
            'nickname' => 'Asigna_Cita_Valuacion',
            'email' => 'corina@vecsa.com',
            'password' => 'CorinaEstebanes%2024%%'
        ]);
        $user->assignRole($role);

        $user->userProfile()->create([
            'name' => 'Corina',
            'last_name' => 'Estebanes'
        ]);
    }
}
