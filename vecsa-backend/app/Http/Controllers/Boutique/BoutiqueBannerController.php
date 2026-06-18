<?php

namespace App\Http\Controllers\Boutique;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BoutiqueBanners\DeleteBoutiqueBannerRequest;
use App\Http\Requests\BoutiqueBanners\StoreBoutiqueBannerRequest;
use App\Http\Requests\BoutiqueBanners\ToggleBoutiqueBannerRequest;
use App\Http\Requests\BoutiqueBanners\UpdateSortBoutiqueBannerRequest;
use App\Jobs\UploadBoutiqueBannerImage;
use App\Models\Boutique\BoutiqueBanner;
use Illuminate\Support\Facades\DB;

class BoutiqueBannerController extends Controller
{
    public function search()
    {
        try {
            $banners = BoutiqueBanner::orderBy('sort_id')->get();
            return ApiResponseHelper::apiSuccess(200, 'Banners obtenidos exitosamente', ['banners' => $banners]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener banners', $e->getMessage(), 500, 'GET_BANNERS_ERROR');
        }
    }

    public function publicList()
    {
        try {
            $banners = BoutiqueBanner::where('active', true)->orderBy('sort_id')->get();
            return ApiResponseHelper::apiSuccess(200, 'Banners obtenidos', ['banners' => $banners]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener banners', $e->getMessage(), 500, 'GET_BANNERS_ERROR');
        }
    }

    public function store(StoreBoutiqueBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $sort_id = BoutiqueBanner::max('sort_id') + 1 ?? 1;

            $banner = BoutiqueBanner::create([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
                'cta_link' => $data['cta_link'] ?? null,
                'bg_class' => $data['bg_class'] ?? null,
                'sort_id' => $sort_id,
            ]);

            if ($request->hasFile('desktop_image')) {
                $image = $request->file('desktop_image');
                $path = \App\Support\UploadableImage::storeTemp($image);
                UploadBoutiqueBannerImage::dispatchSync($path, $banner->uuid, 'desktop', $image->getClientOriginalName());
            }
            if ($request->hasFile('mobile_image')) {
                $image = $request->file('mobile_image');
                $path = \App\Support\UploadableImage::storeTemp($image);
                UploadBoutiqueBannerImage::dispatchSync($path, $banner->uuid, 'mobile', $image->getClientOriginalName());
            }

            $banner->refresh();

            return ApiResponseHelper::apiSuccess(201, 'Banner creado exitosamente', ['banner' => $banner]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear banner', $e->getMessage(), 500, 'CREATE_BANNER_ERROR');
        }
    }

    public function update(StoreBoutiqueBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $uuid = $request->input('uuid');
            $banner = BoutiqueBanner::findByUuid($uuid);

            if (!$banner) {
                return ApiResponseHelper::apiError('Banner no encontrado', 'UUID: ' . $uuid, 404, 'BANNER_NOT_FOUND');
            }

            $banner->update([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'cta_text' => $data['cta_text'] ?? null,
                'cta_link' => $data['cta_link'] ?? null,
                'bg_class' => $data['bg_class'] ?? null,
            ]);

            if ($request->hasFile('desktop_image')) {
                $image = $request->file('desktop_image');
                $path = \App\Support\UploadableImage::storeTemp($image);
                UploadBoutiqueBannerImage::dispatchSync($path, $banner->uuid, 'desktop', $image->getClientOriginalName());
            }
            if ($request->hasFile('mobile_image')) {
                $image = $request->file('mobile_image');
                $path = \App\Support\UploadableImage::storeTemp($image);
                UploadBoutiqueBannerImage::dispatchSync($path, $banner->uuid, 'mobile', $image->getClientOriginalName());
            }

            $banner->refresh();

            return ApiResponseHelper::apiSuccess(200, 'Banner actualizado exitosamente', ['banner' => $banner]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar banner', $e->getMessage(), 500, 'UPDATE_BANNER_ERROR');
        }
    }

    public function delete(DeleteBoutiqueBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $banner = BoutiqueBanner::findByUuid($data['uuid']);

            if (!$banner) {
                return ApiResponseHelper::apiError('Banner no encontrado', 'UUID: ' . $data['uuid'], 404, 'BANNER_NOT_FOUND');
            }

            $banner->delete();
            return ApiResponseHelper::apiSuccess(200, 'Banner eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar banner', $e->getMessage(), 500, 'DELETE_BANNER_ERROR');
        }
    }

    public function sortUpdate(UpdateSortBoutiqueBannerRequest $request)
    {
        try {
            $data = $request->validated();
            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    BoutiqueBanner::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });
            return ApiResponseHelper::apiSuccess(200, 'Banners reordenados exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al reordenar banners', $e->getMessage(), 500, 'SORT_BANNERS_ERROR');
        }
    }

    public function toggle(ToggleBoutiqueBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $banner = BoutiqueBanner::findByUuid($data['uuid']);

            if (!$banner) {
                return ApiResponseHelper::apiError('Banner no encontrado', 'UUID: ' . $data['uuid'], 404, 'BANNER_NOT_FOUND');
            }

            $banner->active = !$banner->active;
            $banner->save();
            return ApiResponseHelper::apiSuccess(200, 'Estado del banner actualizado', ['banner' => $banner]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al cambiar estado', $e->getMessage(), 500, 'TOGGLE_BANNER_ERROR');
        }
    }
}
