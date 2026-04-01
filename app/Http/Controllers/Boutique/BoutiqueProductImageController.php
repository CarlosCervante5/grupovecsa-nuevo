<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boutique\DeleteBoutiqueProductRequest;
use App\Http\Requests\Boutique\SortBoutiqueProductImageRequest;
use App\Http\Requests\Boutique\StoreBoutiqueProductImageRequest;
use App\Jobs\UploadBoutiqueProductImage;
use App\Models\Boutique\BoutiqueProduct;
use App\Models\Boutique\BoutiqueProductImage;
use Illuminate\Support\Facades\DB;

class BoutiqueProductImageController extends Controller
{
    public function store(StoreBoutiqueProductImageRequest $request)
    {
        try {
            $data = $request->validated();

            $product = BoutiqueProduct::findByUuid($data['product_uuid']);
            if (!$product) {
                return ApiResponseHelper::apiError('El producto no existe', null, 404, 'PRODUCT_NOT_FOUND');
            }

            $sortId = BoutiqueProductImage::where('product_id', $product->id)->max('sort_id') + 1 ?? 1;

            $productImage = BoutiqueProductImage::create([
                'product_id' => $product->id,
                'image_path' => '',
                'sort_id' => $sortId,
                'status' => 'pending',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = $image->store('temp_images');
                UploadBoutiqueProductImage::dispatch($path, $product->uuid, $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(201, 'Imagen en proceso de subida', ['image' => $productImage]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al subir la imagen', $e->getMessage(), 500, 'UPLOAD_IMAGE_ERROR');
        }
    }

    public function sortUpdate(SortBoutiqueProductImageRequest $request)
    {
        try {
            $data = $request->validated();

            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    BoutiqueProductImage::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Imágenes reordenadas exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al reordenar las imágenes', $e->getMessage(), 500, 'SORT_IMAGES_ERROR');
        }
    }

    public function delete(DeleteBoutiqueProductRequest $request)
    {
        try {
            $data = $request->validated();

            $image = BoutiqueProductImage::findByUuid($data['uuid']);

            if (!$image) {
                return ApiResponseHelper::apiError('La imagen no existe', 'No existe el uuid: ' . $data['uuid'], 404, 'IMAGE_NOT_FOUND');
            }

            $image->delete();

            return ApiResponseHelper::apiSuccess(200, 'Imagen eliminada exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar la imagen', $e->getMessage(), 500, 'DELETE_IMAGE_ERROR');
        }
    }
}
