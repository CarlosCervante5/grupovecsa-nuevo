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
            $categories = BoutiqueCategory::with('parent')->orderBy('name')->get();

            return ApiResponseHelper::apiSuccess(200, 'Categorías obtenidas exitosamente', ['categories' => $categories]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener categorías', $e->getMessage(), 500, 'GET_CATEGORIES_ERROR');
        }
    }

    public function store(StoreBoutiqueCategoryRequest $request)
    {
        try {
            $data = $request->validated();

            $parentId = null;
            if (! empty($data['parent_uuid'] ?? null)) {
                $parent = BoutiqueCategory::findByUuid($data['parent_uuid']);
                $parentId = $parent ? (int) $parent->id : null;
            }

            $category = BoutiqueCategory::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? true,
                'parent_id' => $parentId,
            ]);

            $category->load('parent');

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

            $parentId = $category->parent_id;
            if ($request->has('parent_uuid')) {
                $rawParent = $request->input('parent_uuid');
                if ($rawParent === null || $rawParent === '') {
                    $parentId = null;
                } else {
                    $parent = BoutiqueCategory::findByUuid($rawParent);
                    if (! $parent) {
                        return ApiResponseHelper::apiError('La categoría padre no existe', null, 404, 'PARENT_CATEGORY_NOT_FOUND');
                    }
                    $parentId = (int) $parent->id;
                }
            }

            if ($this->wouldCreateParentCycle((int) $category->id, $parentId)) {
                return ApiResponseHelper::apiError(
                    'No se puede asignar esa categoría padre (crearía un ciclo en la jerarquía)',
                    null,
                    400,
                    'CATEGORY_PARENT_CYCLE'
                );
            }

            $category->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'active' => $data['active'] ?? $category->active,
                'parent_id' => $parentId,
            ]);

            $category->load('parent');

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

            if ($category->children()->count() > 0) {
                return ApiResponseHelper::apiError(
                    'No se puede eliminar la categoría porque tiene subcategorías',
                    null,
                    400,
                    'CATEGORY_HAS_CHILDREN'
                );
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

    /**
     * True if assigning $newParentId to $categoryId would make $categoryId an ancestor of itself.
     */
    private function wouldCreateParentCycle(int $categoryId, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return false;
        }
        if ($newParentId === $categoryId) {
            return true;
        }
        $current = $newParentId;
        $guard = 0;
        while ($current !== null && $guard++ < 1000) {
            if ($current === $categoryId) {
                return true;
            }
            $current = BoutiqueCategory::where('id', $current)->value('parent_id');
        }

        return false;
    }
}
