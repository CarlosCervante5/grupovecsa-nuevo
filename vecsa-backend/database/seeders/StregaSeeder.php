<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StregaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign existing permissions para productivo
        // $role1 = Role::create(['name' => 'strega-manager']);

        // $role2 = Role::create(['name' => 'strega-administrator']);

        // $role3 = Role::create(['name' => 'strega-seller']);

        $role1 = Role::findByName('strega-manager');

        $role2 = Role::findByName('strega-administrator');

        $role3 = Role::findByName('strega-seller');

        // create demo users
        $user = User::factory()->create([
            'nickname' => 'strega-manager',
            'email' => 'strega_manager@strega.com',
            'password' => 'Manager%2025%%'
        ]);
        $user->assignRole($role1);

        $user->userProfile()->create([
            'name' => 'Manager',
            'last_name' => 'Strega'
        ]);

        $user = User::factory()->create([
            'nickname' => 'strega-administrador',
            'email' => 'strega_admin@strega.com',
            'password' => 'Administrator%2025%%'
        ]);
        $user->assignRole($role2);

        $user->userProfile()->create([
            'name' => 'Administrator',
            'last_name' => 'Strega'
        ]);

        $user = User::factory()->create([
            'nickname' => 'strega-seller',
            'email' => 'strega_seller@strega.com',
            'password' => 'Seller%2025%%'
        ]);
        $user->assignRole($role3);

        $user->userProfile()->create([
            'name' => 'Seller',
            'last_name' => 'Strega'
        ]);

    }
}
