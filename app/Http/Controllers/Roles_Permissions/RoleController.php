<?php

namespace App\Http\Controllers\Roles_Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name]);
        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|unique:roles,name,' . $id,
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string',
        ]);

        $role = Role::findOrFail($id);
        if ($request->has('name')) {
            $role->name = $request->name;
            $role->save();
        }

        if ($request->exists('permissions')) {
            $names = collect($request->input('permissions', []))
                ->filter(fn ($n) => is_string($n) && $n !== '')
                ->values()
                ->all();
            try {
                // Asegura filas en `permissions` con el mismo guard que el rol; evita fallos de
                // syncPermissions (p. ej. permiso solo creado bajo otro guard o aún no existente).
                $guard = $role->guard_name;
                foreach ($names as $name) {
                    Permission::findOrCreate($name, $guard);
                }
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $role->syncPermissions($names);
                app(PermissionRegistrar::class)->forgetCachedPermissions();
            } catch (Throwable $e) {
                Log::warning('roles.sync_permissions_failed', [
                    'role_id' => $id,
                    'guard' => $role->guard_name ?? null,
                    'permission_count' => count($names),
                    'message' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => 'No se pudieron sincronizar los permisos del rol.',
                    'error' => $e->getMessage(),
                ], 422);
            }
        }

        return response()->json($role->load('permissions'));
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(null, 204);
    }
}

