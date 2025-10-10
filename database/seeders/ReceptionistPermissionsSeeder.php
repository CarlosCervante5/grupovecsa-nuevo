<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ReceptionistPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role6 = Role::create(['name' => 'receptionist']);

        // create demo users
        $user = User::factory()->create([
            'nickname' => 'Ivonne_Recepcion',
            'email' => 'ivonne@vecsa.com',
            'password' => 'IvonneSalinas%2024%%'
        ]);
        $user->assignRole($role6);

        $user->userProfile()->create([
            'name' => 'Ivonne',
            'last_name' => 'Salinas'
        ]);

        // create demo users
        $user = User::factory()->create([
            'nickname' => 'Valeria.Recepcion',
            'email' => 'valeria@vecsa.com',
            'password' => 'ValeriaGalicia%2024%%'
        ]);
        $user->assignRole($role6);

        $user->userProfile()->create([
            'name' => 'Valeria',
            'last_name' => 'Galicia'
        ]);

    }
}
