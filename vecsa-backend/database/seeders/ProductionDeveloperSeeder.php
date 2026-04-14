<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea o actualiza únicamente el usuario developer y los roles/permisos mínimos del panel VECSA.
 * Pensado para producción: no crea usuarios de prueba (a diferencia de DeveloperUserSeeder).
 *
 * En producción (APP_ENV=production) es obligatorio definir VECSA_SEED_DEVELOPER_PASSWORD.
 *
 * Uso:
 *   php artisan db:seed --class=ProductionDeveloperSeeder --force
 */
class ProductionDeveloperSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $email = (string) env('VECSA_SEED_DEVELOPER_EMAIL', 'dev@vecsa.com');
        $nickname = (string) env('VECSA_SEED_DEVELOPER_NICKNAME', 'developer');
        $name = (string) env('VECSA_SEED_DEVELOPER_NAME', 'Developer');
        $lastName = (string) env('VECSA_SEED_DEVELOPER_LAST_NAME', 'Vecsa');
        $password = env('VECSA_SEED_DEVELOPER_PASSWORD');

        if (app()->environment('production')) {
            if (! is_string($password) || $password === '') {
                throw new \InvalidArgumentException(
                    'En producción define VECSA_SEED_DEVELOPER_PASSWORD en el entorno antes de ejecutar ProductionDeveloperSeeder.'
                );
            }
        } else {
            if (! is_string($password) || $password === '') {
                $password = 'Developer%2024%%';
            }
        }

        if (! Str::contains($email, '@')) {
            throw new \InvalidArgumentException('VECSA_SEED_DEVELOPER_EMAIL no es un correo válido.');
        }

        $permissions = [
            'list users', 'create users', 'update users', 'delete users',
            'access benchmark', 'access store_management',
            'access gerente', 'access gestor', 'access receptionist',
            'access valuator', 'access appointment_manager', 'access staff',
            'access bodywork_paint_technician', 'access spare_parts',
            'access marketing',
            'access administrator', 'access developer',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $roleNames = [
            'developer', 'administrator', 'staff', 'marketing', 'gestor',
            'receptionist', 'valuator', 'appointment_manager',
            'bodywork_paint_technician', 'spare_parts', 'client', 'gerente',
        ];

        $roles = [];
        foreach ($roleNames as $rn) {
            $roles[$rn] = Role::firstOrCreate(['name' => $rn]);
        }

        $allPermissions = Permission::all();
        $roles['administrator']->syncPermissions($allPermissions);
        $roles['developer']->syncPermissions($allPermissions);

        $gerentePermissions = Permission::where('name', 'like', 'access %')->get();
        $roles['gerente']->syncPermissions($gerentePermissions);

        $devUser = User::withTrashed()->where('email', $email)->first();

        if ($devUser === null) {
            $devUser = new User([
                'email' => $email,
                'nickname' => $nickname,
                'password' => $password,
            ]);
            $devUser->save();
            $devUser->userProfile()->create([
                'name' => $name,
                'last_name' => $lastName,
            ]);
        } else {
            if ($devUser->trashed()) {
                $devUser->restore();
            }
            $devUser->nickname = $nickname;
            $devUser->password = $password;
            $devUser->save();

            if ($devUser->userProfile === null) {
                $devUser->userProfile()->create([
                    'name' => $name,
                    'last_name' => $lastName,
                ]);
            }
        }

        $devUser->syncRoles(['developer', 'administrator', 'staff']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('ProductionDeveloperSeeder: usuario developer listo.');
        $this->command?->info('  Email: '.$email);
        $this->command?->warn('  No se muestra la contraseña. Comprueba VECSA_SEED_DEVELOPER_PASSWORD en el entorno.');
    }
}
