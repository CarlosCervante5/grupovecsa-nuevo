<?php

namespace App\Http\Controllers\Dealerships;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Dealership;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DealershipController extends Controller
{
    public function search()
    {
        try {
            $dealerships = Dealership::query()
                ->withCount(['vehicles', 'users'])
                ->orderBy('name')
                ->get();

            return ApiResponseHelper::authSuccess(200, 'Sucursales encontradas', $dealerships);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener las sucursales', $e->getMessage(), 500, 'GET_DEALERSHIPS_ERROR');
        }
    }

    public function store(Request $request)
    {
        try {
            $table = (new Dealership)->getTable();
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:500',
                'state' => 'nullable|string|max:120',
                'description' => 'nullable|string|max:5000',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'whatsapp_phone' => 'nullable|string|max:50',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'image_url' => 'nullable|string|max:2048',
                'opening_hours' => 'nullable|string|max:255',
            ]);

            $dealership = Dealership::create($data);
            $dealership->loadCount(['vehicles', 'users']);

            return ApiResponseHelper::apiSuccess(201, 'Sucursal creada', ['dealership' => $dealership]);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la sucursal', $e->getMessage(), 500, 'DEALERSHIP_STORE_ERROR');
        }
    }

    public function update(Request $request)
    {
        try {
            $table = (new Dealership)->getTable();
            $data = $request->validate([
                'id' => ['required', 'integer', Rule::exists($table, 'id')],
                'name' => 'required|string|max:255',
                'location' => 'required|string|max:500',
                'state' => 'nullable|string|max:120',
                'description' => 'nullable|string|max:5000',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'whatsapp_phone' => 'nullable|string|max:50',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'image_url' => 'nullable|string|max:2048',
                'opening_hours' => 'nullable|string|max:255',
            ]);

            $id = $data['id'];
            unset($data['id']);

            $dealership = Dealership::findOrFail($id);
            $dealership->update($data);
            $dealership->loadCount(['vehicles', 'users']);

            return ApiResponseHelper::apiSuccess(200, 'Sucursal actualizada', ['dealership' => $dealership->fresh()]);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la sucursal', $e->getMessage(), 500, 'DEALERSHIP_UPDATE_ERROR');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $table = (new Dealership)->getTable();
            $data = $request->validate([
                'id' => ['required', 'integer', Rule::exists($table, 'id')],
            ]);

            $dealership = Dealership::findOrFail($data['id']);
            $dealership->delete();

            return ApiResponseHelper::apiSuccess(200, 'Sucursal eliminada', null);
        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la sucursal', $e->getMessage(), 500, 'DEALERSHIP_DELETE_ERROR');
        }
    }

    public function users(\Illuminate\Http\Request $request)
    {
        try {
            $data = $request->validate([
                'dealership_id' => 'required|integer|exists:' . env('DB_TABLE_PREFIX', '') . 'dealerships,id',
            ]);

            $dealership = Dealership::find($data['dealership_id']);
            if (! $dealership) {
                return ApiResponseHelper::apiError('Sucursal no encontrada', null, 404, 'DEALERSHIP_NOT_FOUND');
            }

            $users = $dealership->users()->with('userProfile')->paginate(15);

            return ApiResponseHelper::apiSuccess(200, 'Usuarios de la sucursal obtenidos', ['users' => $users]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener usuarios de la sucursal', $e->getMessage(), 500, 'GET_DEALERSHIP_USERS_ERROR');
        }
    }
}
