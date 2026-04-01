<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueProductAttribute;
use App\Models\Boutique\BoutiqueProductAttributeValue;
use Illuminate\Http\Request;

class BoutiqueAttributeController extends Controller
{
    public function list()
    {
        try {
            $attributes = BoutiqueProductAttribute::with(['values' => function ($q) {
                $q->orderBy('sort_order');
            }])->orderBy('name')->get();

            return ApiResponseHelper::apiSuccess(200, 'Atributos obtenidos exitosamente', ['attributes' => $attributes]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener atributos', $e->getMessage(), 500, 'GET_ATTRIBUTES_ERROR');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
            ]);

            $existing = BoutiqueProductAttribute::where('name', $request->input('name'))->first();
            if ($existing) {
                return ApiResponseHelper::apiError('El nombre del atributo ya existe', null, 400, 'ATTRIBUTE_NAME_EXISTS');
            }

            $attribute = BoutiqueProductAttribute::create([
                'name' => $request->input('name'),
            ]);

            $attribute->load('values');

            return ApiResponseHelper::apiSuccess(201, 'Atributo creado exitosamente', ['attribute' => $attribute]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear atributo', $e->getMessage(), 500, 'CREATE_ATTRIBUTE_ERROR');
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
                'name' => 'required|string|max:100',
            ]);

            $attribute = BoutiqueProductAttribute::findByUuid($request->input('uuid'));
            if (!$attribute) {
                return ApiResponseHelper::apiError('El atributo no existe', null, 404, 'ATTRIBUTE_NOT_FOUND');
            }

            $existing = BoutiqueProductAttribute::where('name', $request->input('name'))
                ->where('id', '!=', $attribute->id)
                ->first();
            if ($existing) {
                return ApiResponseHelper::apiError('El nombre del atributo ya existe', null, 400, 'ATTRIBUTE_NAME_EXISTS');
            }

            $attribute->update(['name' => $request->input('name')]);
            $attribute->load('values');

            return ApiResponseHelper::apiSuccess(200, 'Atributo actualizado exitosamente', ['attribute' => $attribute]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar atributo', $e->getMessage(), 500, 'UPDATE_ATTRIBUTE_ERROR');
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
            ]);

            $attribute = BoutiqueProductAttribute::findByUuid($request->input('uuid'));
            if (!$attribute) {
                return ApiResponseHelper::apiError('El atributo no existe', null, 404, 'ATTRIBUTE_NOT_FOUND');
            }

            if ($attribute->products()->count() > 0) {
                return ApiResponseHelper::apiError('El atributo está asignado a productos y no puede eliminarse', null, 409, 'ATTRIBUTE_IN_USE');
            }

            $attribute->delete();

            return ApiResponseHelper::apiSuccess(200, 'Atributo eliminado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar atributo', $e->getMessage(), 500, 'DELETE_ATTRIBUTE_ERROR');
        }
    }

    public function storeValue(Request $request)
    {
        try {
            $request->validate([
                'attribute_uuid' => 'required|string',
                'value' => 'required|string|max:100',
                'color_hex' => 'nullable|string|max:20',
                'sort_order' => 'nullable|integer',
            ]);

            $attribute = BoutiqueProductAttribute::findByUuid($request->input('attribute_uuid'));
            if (!$attribute) {
                return ApiResponseHelper::apiError('El atributo no existe', null, 404, 'ATTRIBUTE_NOT_FOUND');
            }

            $existing = BoutiqueProductAttributeValue::where('attribute_id', $attribute->id)
                ->where('value', $request->input('value'))
                ->first();
            if ($existing) {
                return ApiResponseHelper::apiError('El valor ya existe en este atributo', null, 400, 'VALUE_ALREADY_EXISTS');
            }

            $attrValue = BoutiqueProductAttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $request->input('value'),
                'color_hex' => $request->input('color_hex'),
                'sort_order' => $request->input('sort_order', 0),
            ]);

            return ApiResponseHelper::apiSuccess(201, 'Valor de atributo creado exitosamente', ['value' => $attrValue]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear valor de atributo', $e->getMessage(), 500, 'CREATE_VALUE_ERROR');
        }
    }

    public function updateValue(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
                'value' => 'nullable|string|max:100',
                'color_hex' => 'nullable|string|max:20',
                'sort_order' => 'nullable|integer',
            ]);

            $attrValue = BoutiqueProductAttributeValue::findByUuid($request->input('uuid'));
            if (!$attrValue) {
                return ApiResponseHelper::apiError('El valor de atributo no existe', null, 404, 'VALUE_NOT_FOUND');
            }

            $updateData = [];
            if ($request->has('value')) {
                $existing = BoutiqueProductAttributeValue::where('attribute_id', $attrValue->attribute_id)
                    ->where('value', $request->input('value'))
                    ->where('id', '!=', $attrValue->id)
                    ->first();
                if ($existing) {
                    return ApiResponseHelper::apiError('El valor ya existe en este atributo', null, 400, 'VALUE_ALREADY_EXISTS');
                }
                $updateData['value'] = $request->input('value');
            }
            if ($request->has('color_hex')) {
                $updateData['color_hex'] = $request->input('color_hex');
            }
            if ($request->has('sort_order')) {
                $updateData['sort_order'] = $request->input('sort_order');
            }

            $attrValue->update($updateData);

            return ApiResponseHelper::apiSuccess(200, 'Valor de atributo actualizado exitosamente', ['value' => $attrValue]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar valor de atributo', $e->getMessage(), 500, 'UPDATE_VALUE_ERROR');
        }
    }

    public function deleteValue(Request $request)
    {
        try {
            $request->validate([
                'uuid' => 'required|string',
            ]);

            $attrValue = BoutiqueProductAttributeValue::findByUuid($request->input('uuid'));
            if (!$attrValue) {
                return ApiResponseHelper::apiError('El valor de atributo no existe', null, 404, 'VALUE_NOT_FOUND');
            }

            if ($attrValue->variants()->count() > 0) {
                return ApiResponseHelper::apiError('El valor está asociado a variantes y no puede eliminarse', null, 409, 'VALUE_IN_USE');
            }

            $attrValue->delete();

            return ApiResponseHelper::apiSuccess(200, 'Valor de atributo eliminado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar valor de atributo', $e->getMessage(), 500, 'DELETE_VALUE_ERROR');
        }
    }
}
