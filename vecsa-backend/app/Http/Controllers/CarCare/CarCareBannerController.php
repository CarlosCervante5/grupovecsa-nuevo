<?php

namespace App\Http\Controllers\CarCare;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CarCareBanners\DeleteCarCareBannerRequest;
use App\Http\Requests\CarCareBanners\StoreCarCareBannerRequest;
use App\Http\Requests\CarCareBanners\ToggleCarCareBannerRequest;
use App\Http\Requests\CarCareBanners\UpdateSortCarCareBannerRequest;
use App\Jobs\UploadCarCareBannerImage;
use App\Models\CarCare\CarCareBanner;
use App\Support\UploadableImage;
use Illuminate\Support\Facades\DB;

class CarCareBannerController extends Controller
{
    public function search()
    {
        try {
            $banners = CarCareBanner::orderBy('sort_id')->get();

            return ApiResponseHelper::apiSuccess(200, 'Banners Car Care obtenidos exitosamente', ['banners' => $banners]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener banners Car Care', $e->getMessage(), 500, 'GET_CARCARE_BANNERS_ERROR');
        }
    }

    public function publicList()
    {
        try {
            $banners = CarCareBanner::where('active', true)->orderBy('sort_id')->get();

            return ApiResponseHelper::apiSuccess(200, 'Banners Car Care obtenidos', ['banners' => $banners]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener banners Car Care', $e->getMessage(), 500, 'GET_CARCARE_BANNERS_ERROR');
        }
    }

    public function store(StoreCarCareBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $sortId = (int) (CarCareBanner::max('sort_id') ?? 0) + 1;

            $banner = CarCareBanner::create([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'disclaimer' => $data['disclaimer'] ?? null,
                'sort_id' => $sortId,
            ]);

            $this->syncImagesFromRequest($request, $banner);
            $banner->refresh();

            return ApiResponseHelper::apiSuccess(201, 'Banner Car Care creado exitosamente', ['banner' => $banner]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear banner Car Care', $e->getMessage(), 500, 'CREATE_CARCARE_BANNER_ERROR');
        }
    }

    public function update(StoreCarCareBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $uuid = $request->input('uuid');
            $banner = CarCareBanner::findByUuid((string) $uuid);

            if (! $banner) {
                return ApiResponseHelper::apiError('Banner Car Care no encontrado', 'UUID: '.$uuid, 404, 'CARCARE_BANNER_NOT_FOUND');
            }

            $banner->update([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'disclaimer' => $data['disclaimer'] ?? null,
            ]);

            $this->syncImagesFromRequest($request, $banner);
            $banner->refresh();

            return ApiResponseHelper::apiSuccess(200, 'Banner Car Care actualizado exitosamente', ['banner' => $banner]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar banner Car Care', $e->getMessage(), 500, 'UPDATE_CARCARE_BANNER_ERROR');
        }
    }

    public function delete(DeleteCarCareBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $banner = CarCareBanner::findByUuid($data['uuid']);

            if (! $banner) {
                return ApiResponseHelper::apiError('Banner Car Care no encontrado', 'UUID: '.$data['uuid'], 404, 'CARCARE_BANNER_NOT_FOUND');
            }

            $banner->delete();

            return ApiResponseHelper::apiSuccess(200, 'Banner Car Care eliminado exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar banner Car Care', $e->getMessage(), 500, 'DELETE_CARCARE_BANNER_ERROR');
        }
    }

    public function sortUpdate(UpdateSortCarCareBannerRequest $request)
    {
        try {
            $data = $request->validated();
            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    CarCareBanner::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Banners Car Care reordenados exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al reordenar banners Car Care', $e->getMessage(), 500, 'SORT_CARCARE_BANNERS_ERROR');
        }
    }

    public function toggle(ToggleCarCareBannerRequest $request)
    {
        try {
            $data = $request->validated();
            $banner = CarCareBanner::findByUuid($data['uuid']);

            if (! $banner) {
                return ApiResponseHelper::apiError('Banner Car Care no encontrado', 'UUID: '.$data['uuid'], 404, 'CARCARE_BANNER_NOT_FOUND');
            }

            $banner->active = ! $banner->active;
            $banner->save();

            return ApiResponseHelper::apiSuccess(200, 'Estado del banner Car Care actualizado', ['banner' => $banner]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al cambiar estado del banner Car Care', $e->getMessage(), 500, 'TOGGLE_CARCARE_BANNER_ERROR');
        }
    }

    private function syncImagesFromRequest(StoreCarCareBannerRequest $request, CarCareBanner $banner): void
    {
        if ($request->hasFile('desktop_image')) {
            $image = $request->file('desktop_image');
            $path = UploadableImage::storeTemp($image);
            UploadCarCareBannerImage::dispatchSync($path, $banner->uuid, 'desktop', $image->getClientOriginalName());
        }

        if ($request->hasFile('mobile_image')) {
            $image = $request->file('mobile_image');
            $path = UploadableImage::storeTemp($image);
            UploadCarCareBannerImage::dispatchSync($path, $banner->uuid, 'mobile', $image->getClientOriginalName());
        }
    }
}
