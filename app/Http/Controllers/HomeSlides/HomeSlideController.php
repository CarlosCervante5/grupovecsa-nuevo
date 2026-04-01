<?php

namespace App\Http\Controllers\HomeSlides;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\HomeSlides\DeleteHomeSlideRequest;
use App\Http\Requests\HomeSlides\StoreHomeSlideRequest;
use App\Http\Requests\HomeSlides\ToggleHomeSlideRequest;
use App\Http\Requests\HomeSlides\UpdateSortHomeSlideRequest;
use App\Jobs\UploadHomeSlideImage;
use App\Models\HomeSlide;
use Illuminate\Support\Facades\DB;

class HomeSlideController extends Controller
{
    /**
     * Obtener una lista de todos los slides ordenados por sort_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        try {
            $slides = HomeSlide::orderBy('sort_id')->get();

            return ApiResponseHelper::apiSuccess(200, 'Slides obtenidos exitosamente', ['slides' => $slides]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de slides', $e->getMessage(), 500, 'GET_SLIDES_ERROR');
        }
    }

    /**
     * Almacenar un nuevo slide
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreHomeSlideRequest $request)
    {
        try {
            $data = $request->validated();

            $sort_id = HomeSlide::max('sort_id') + 1 ?? 1;

            $slide = HomeSlide::create([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'offer_main' => $data['offer_main'] ?? null,
                'offer_main_text' => $data['offer_main_text'] ?? null,
                'offer_sub' => $data['offer_sub'] ?? null,
                'offer_secondary' => $data['offer_secondary'] ?? null,
                'offer_secondary_text' => $data['offer_secondary_text'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_link' => $data['button_link'] ?? null,
                'disclaimer' => $data['disclaimer'] ?? null,
                'sort_id' => $sort_id,
            ]);

            if ($request->hasFile('desktop_image')) {
                $image = $request->file('desktop_image');
                $path = $image->store('temp_images');
                UploadHomeSlideImage::dispatch($path, $slide->uuid, 'desktop', $image->getClientOriginalName());
            }

            if ($request->hasFile('mobile_image')) {
                $image = $request->file('mobile_image');
                $path = $image->store('temp_images');
                UploadHomeSlideImage::dispatch($path, $slide->uuid, 'mobile', $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(201, 'Slide creado exitosamente', ['slide' => $slide]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el slide', $e->getMessage(), 500, 'CREATE_SLIDE_ERROR');
        }
    }

    /**
     * Actualizar un slide existente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StoreHomeSlideRequest $request)
    {
        try {
            $data = $request->validated();
            $uuid = $request->input('uuid');

            $slide = HomeSlide::findByUuid($uuid);

            if (!$slide) {
                return ApiResponseHelper::apiError('El slide no existe', 'No existe el id: ' . $uuid, 404, 'SLIDE_NOT_FOUND');
            }

            $slide->update([
                'title' => $data['title'],
                'subtitle' => $data['subtitle'] ?? null,
                'offer_main' => $data['offer_main'] ?? null,
                'offer_main_text' => $data['offer_main_text'] ?? null,
                'offer_sub' => $data['offer_sub'] ?? null,
                'offer_secondary' => $data['offer_secondary'] ?? null,
                'offer_secondary_text' => $data['offer_secondary_text'] ?? null,
                'button_text' => $data['button_text'] ?? null,
                'button_link' => $data['button_link'] ?? null,
                'disclaimer' => $data['disclaimer'] ?? null,
            ]);

            if ($request->hasFile('desktop_image')) {
                $image = $request->file('desktop_image');
                $path = $image->store('temp_images');
                UploadHomeSlideImage::dispatch($path, $slide->uuid, 'desktop', $image->getClientOriginalName());
            }

            if ($request->hasFile('mobile_image')) {
                $image = $request->file('mobile_image');
                $path = $image->store('temp_images');
                UploadHomeSlideImage::dispatch($path, $slide->uuid, 'mobile', $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(200, 'Slide actualizado exitosamente', ['slide' => $slide]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al actualizar el slide', $e->getMessage(), 500, 'UPDATE_SLIDE_ERROR');
        }
    }

    /**
     * Eliminar slide mediante uuid (soft delete)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteHomeSlideRequest $request)
    {
        try {
            $data = $request->validated();

            $slide = HomeSlide::findByUuid($data['uuid']);

            if ($slide) {
                $slide->delete();

                return ApiResponseHelper::apiSuccess(200, 'Slide eliminado exitosamente');
            } else {
                return ApiResponseHelper::apiError('El slide no existe', 'No existe el id: ' . $data['uuid'], 404, 'SLIDE_NOT_FOUND');
            }
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el slide', $e->getMessage(), 500, 'DELETE_SLIDE_ERROR');
        }
    }

    /**
     * Actualizar orden de los slides
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortUpdate(UpdateSortHomeSlideRequest $request)
    {
        try {
            $data = $request->validated();

            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    HomeSlide::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Slides reordenados exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al reordenar los slides', $e->getMessage(), 500, 'SORT_SLIDES_ERROR');
        }
    }

    /**
     * Toggle del estado activo/inactivo de un slide
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(ToggleHomeSlideRequest $request)
    {
        try {
            $data = $request->validated();

            $slide = HomeSlide::findByUuid($data['uuid']);

            if ($slide) {
                $slide->active = !$slide->active;
                $slide->save();

                return ApiResponseHelper::apiSuccess(200, 'Estado del slide actualizado exitosamente', ['slide' => $slide]);
            } else {
                return ApiResponseHelper::apiError('El slide no existe', 'No existe el id: ' . $data['uuid'], 404, 'SLIDE_NOT_FOUND');
            }
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al cambiar el estado del slide', $e->getMessage(), 500, 'TOGGLE_SLIDE_ERROR');
        }
    }
}
