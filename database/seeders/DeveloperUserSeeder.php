<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DeveloperUserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Create all permissions used in routes ──
        $permissions = [
            'list users', 'create users', 'update users', 'delete users',
            'access benchmark', 'access store_management',
            'access gerente', 'access gestor', 'access receptionist',
            'access valuator', 'access appointment_manager', 'access staff',
            'access bodywork_paint_technician', 'access spare_parts',
            'access marketing',
            'access vehicle_inventory',
            'access gestor_promotions', 'access gestor_scheduled_events', 'access gestor_rewards',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Create all roles used in the system ──
        $roleNames = [
            'developer', 'administrator', 'staff', 'marketing', 'gestor', 'manager',
            'receptionist', 'valuator', 'appointment_manager',
            'bodywork_paint_technician', 'spare_parts', 'client', 'gerente',
        ];
        $roles = [];
        foreach ($roleNames as $rn) {
            $roles[$rn] = Role::firstOrCreate(['name' => $rn]);
        }

        // Give administrator and developer all permissions
        $allPermissions = Permission::all();
        $roles['administrator']->syncPermissions($allPermissions);
        $roles['developer']->syncPermissions($allPermissions);

        // Give gerente all access permissions, excluding user management
        $gerentePermissions = Permission::where('name', 'like', 'access %')->get();
        $roles['gerente']->syncPermissions($gerentePermissions);

        $gestorNav = [
            'access gestor_promotions',
            'access gestor_scheduled_events',
            'access gestor_rewards',
        ];
        foreach ($gestorNav as $navPerm) {
            $roles['gestor']->givePermissionTo($navPerm);
        }
        // Ejemplo: rol manager con un subconjunto (ajustar en BD según negocio)
        $roles['manager']->givePermissionTo('access gestor_promotions');

        // ── Developer user (superuser) ──
        $devUser = User::where('email', 'dev@vecsa.com')->first();
        if (!$devUser) {
            $devUser = User::factory()->create([
                'nickname' => 'developer',
                'email'    => 'dev@vecsa.com',
                'password' => 'Developer%2024%%',
            ]);
            $devUser->userProfile()->create([
                'name'      => 'Developer',
                'last_name' => 'Vecsa',
            ]);
        }
        $devUser->syncRoles(['developer', 'administrator', 'staff']);

        // ── Test users per role ──
        $testUsers = [
            ['nickname' => 'admin_test',     'email' => 'admin@vecsa.com',       'role' => 'administrator',             'name' => 'Admin',      'last_name' => 'Test'],
            ['nickname' => 'marketing_test', 'email' => 'marketing@vecsa.com',   'role' => 'marketing',                 'name' => 'Marketing',  'last_name' => 'Test'],
            ['nickname' => 'staff_test',     'email' => 'staff@vecsa.com',       'role' => 'staff',                     'name' => 'Staff',      'last_name' => 'Test'],
            ['nickname' => 'gestor_test',    'email' => 'gestor@vecsa.com',      'role' => 'gestor',                    'name' => 'Gestor',     'last_name' => 'Test'],
            ['nickname' => 'recep_test',     'email' => 'receptionist@vecsa.com','role' => 'receptionist',              'name' => 'Recepción',  'last_name' => 'Test'],
            ['nickname' => 'valuator_test',  'email' => 'valuator@vecsa.com',    'role' => 'valuator',                  'name' => 'Valuador',   'last_name' => 'Test'],
            ['nickname' => 'appt_test',      'email' => 'appointments@vecsa.com','role' => 'appointment_manager',       'name' => 'Citas',      'last_name' => 'Test'],
            ['nickname' => 'bodywork_test',  'email' => 'bodywork@vecsa.com',    'role' => 'bodywork_paint_technician', 'name' => 'Hojalatería','last_name' => 'Test'],
            ['nickname' => 'parts_test',     'email' => 'parts@vecsa.com',       'role' => 'spare_parts',               'name' => 'Refacciones','last_name' => 'Test'],
            ['nickname' => 'client_test',    'email' => 'client@vecsa.com',      'role' => 'client',                    'name' => 'Cliente',    'last_name' => 'Test'],
            ['nickname' => 'gerente_test',   'email' => 'gerente@vecsa.com',     'role' => 'gerente',                   'name' => 'Gerente',    'last_name' => 'Test'],
        ];

        $password = 'TestUser%2024%%';

        foreach ($testUsers as $tu) {
            $user = User::where('email', $tu['email'])->first();
            if (!$user) {
                $user = User::factory()->create([
                    'nickname' => $tu['nickname'],
                    'email'    => $tu['email'],
                    'password' => $password,
                ]);
                if ($tu['role'] === 'client') {
                    $user->customerProfile()->create([
                        'name'      => $tu['name'],
                        'last_name' => $tu['last_name'],
                    ]);
                } else {
                    $user->userProfile()->create([
                        'name'      => $tu['name'],
                        'last_name' => $tu['last_name'],
                    ]);
                }
            }
            $user->syncRoles([$tu['role']]);
            // Give admin test user all permissions too
            if ($tu['role'] === 'administrator') {
                $user->syncPermissions(Permission::all());
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✓ Permissions created: ' . implode(', ', $permissions));
        $this->command->info('✓ Roles created: ' . implode(', ', $roleNames));
        $this->command->info('✓ Developer user: dev@vecsa.com / Developer%2024%%');
        $this->command->info('✓ Test users created (password: TestUser%2024%%)');
    }
}
