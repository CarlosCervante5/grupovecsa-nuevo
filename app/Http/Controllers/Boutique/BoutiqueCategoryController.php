<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\DeleteBoutiqueCategoryRequest;
use App\Http\Requests\Boutique\StoreBoutiqueCategoryRequest;
use App\Models\Boutique\BoutiqueCategory;

class BoutiqueCategoryController extends Controller
{
    public function search()
    {
        try {
            $categories = BoutiqueCategory::orderBy('name')->get();

            return ApiResponseHelper::apiSuccess(200, 'Categorías obtenidas exitosamente', ['categories' => $categories]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener categorías', $e->getMessage(), 500, 'GET_CATEGORIES_ERROR');
        }
    }

    public function store(StoreBoutiqueCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            $category = BoutiqueCategory::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
            ]);

            return ApiResponseHelper::apiSuccess(201, 'Categoría creada exitosamente', ['category' => $category]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear la categoría', $e->getMessage(), 500, 'CREATE_CATEGORY_ERROR');
        }
    }

    public function update(StoreBoutiqueCategoryRequest $request)
    {
        try {
            $data = $request->validated();
            $uuid = $request->input('uuid');

            $category = BoutiqueCategory::findByUuid($uuid);

            if (!$category) {
                return ApiResponseHelper::apiError('La categoría no existe', 'No existe el uuid: ' . $uuid, 404, 'CATEGORY_NOT_FOUND');
            }

            $category->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? $category->active,
            ]);

            return ApiResponseHelper::apiSuccess(200, 'Categoría actualizada exitosamente', ['category' => $category]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar la categoría', $e->getMessage(), 500, 'UPDATE_CATEGORY_ERROR');
        }
    }

    public function delete(DeleteBoutiqueCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            $category = BoutiqueCategory::findByUuid($data['uuid']);

            if (!$category) {
                return ApiResponseHelper::apiError('La categoría no existe', 'No existe el uuid: ' . $data['uuid'], 404, 'CATEGORY_NOT_FOUND');
            }

            if ($category->products()->count() > 0) {
                return ApiResponseHelper::apiError('No se puede eliminar la categoría porque tiene productos asociados', null, 400, 'CATEGORY_HAS_PRODUCTS');
            }

            $category->delete();

            return ApiResponseHelper::apiSuccess(200, 'Categoría eliminada exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la categoría', $e->getMessage(), 500, 'DELETE_CATEGORY_ERROR');
        }
    }
}
