<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StregaManagersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $role1 = Role::findByName('strega-manager');

        $user = User::factory()->create([
            'nickname' => 'Carmen',
            'email' => 'carmen@vecsa.com',
            'password' => 'Carmen%2024%%'
        ]);
        $user->assignRole($role1);
        $user->userProfile()->create([
            'name' => 'Carmen',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Abigail',
            'email' => 'abigail@vecsa.com',
            'password' => 'Abigail%2024%%'
        ]);
        $user->assignRole($role1);
        $user->userProfile()->create([
            'name' => 'Abigail',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Alondra',
            'email' => 'alondra@vecsa.com',
            'password' => 'Alondra%2024%%'
        ]);
        $user->assignRole($role1);
        $user->userProfile()->create([
            'name' => 'Alondra',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Carla',
            'email' => 'carla@vecsa.com',
            'password' => 'Carla%2024%%'
        ]);
        $user->assignRole($role1);
        $user->userProfile()->create([
            'name' => 'Carla',
            'last_name' => '_'
        ]);

        $user = User::factory()->create([
            'nickname' => 'Jon',
            'email' => 'jon@vecsa.com',
            'password' => 'Jon%2024%%'
        ]);
        $user->assignRole($role1);
        $user->userProfile()->create([
            'name' => 'Jon',
            'last_name' => '_'
        ]);
    }
}
