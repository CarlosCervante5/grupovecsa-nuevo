<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'create vehicles']);
        Permission::create(['name' => 'update vehicles']);
        Permission::create(['name' => 'delete vehicles']);
        Permission::create(['name' => 'list all vehicles']);

        Permission::create(['name' => 'list users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'delete users']);

        // create roles and assign existing permissions
        $role1 = Role::create(['name' => 'manager']);
        $role1->givePermissionTo('create vehicles');
        $role1->givePermissionTo('update vehicles');
        $role1->givePermissionTo('delete vehicles');


        $role2 = Role::create(['name' => 'administrator']);
        $role2->givePermissionTo('list users');
        $role2->givePermissionTo('create users');
        $role2->givePermissionTo('update users');
        $role2->givePermissionTo('delete users');

        $role3 = Role::create(['name' => 'client']);

        $role4 = Role::create(['name' => 'gestor']);
        $role4->givePermissionTo('list all vehicles');

        $role5 = Role::create(['name' => 'marketing']);

        $role6 = Role::create(['name' => 'staff']);

        $role7 = Role::create(['name' => 'valuator']);

        $role8 = Role::create(['name' => 'technician']);

        $role9 = Role::create(['name' => 'seller']);

        $role10 = Role::create(['name' => 'bodywork_paint_technician']);

        $role11 = Role::create(['name' => 'spare_parts']);

        $role12 = Role::create(['name' => 'strega-seller']);

        $role13 = Role::create(['name' => 'strega-manager']);

        $role14 = Role::create(['name' => 'strega-administrator']);

        $role15 = Role::create(['name' => 'appointment_manager']);

        // create demo users
        $user = User::factory()->create([
            'nickname' => 'manager',
            'email' => 'manager@vecsa.com',
            'password' => 'Manager%2024%%'
        ]);
        $user->assignRole($role5);

        $user->userProfile()->create([
            'name' => 'Manager',
            'last_name' => 'Vecsa'
        ]);

        $user = User::factory()->create([
            'nickname' => 'administrador',
            'email' => 'admin@vecsa.com',
            'password' => 'Administrator%2024%%'
        ]);
        $user->assignRole($role2);

        $user->userProfile()->create([
            'name' => 'Admin',
            'last_name' => 'Vecsa'
        ]);

        $user->userProfile()->create([
            'name' => 'Client',
            'last_name' => 'Vecsa'
        ]);

        $user = User::factory()->create([
            'nickname' => 'gestor',
            'email' => 'gestor@vecsa.com',
            'password' => 'Gestor%2024%%'
        ]);
        $user->assignRole($role4);

        $user->userProfile()->create([
            'name' => 'Gestor',
            'last_name' => 'Vecsa'
        ]);


        $user = User::factory()->create([
            'nickname' => 'staff',
            'email' => 'staff@vecsa.com',
            'password' => 'Staff%2024%%'
        ]);
        $user->assignRole($role6);

        $user->userProfile()->create([
            'name' => 'Staff',
            'last_name' => 'Vecsa'
        ]);
    }
}
