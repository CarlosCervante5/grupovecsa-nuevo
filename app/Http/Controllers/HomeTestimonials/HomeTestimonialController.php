<?php

namespace App\Http\Controllers\HomeTestimonials;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\HomeTestimonials\DeleteHomeTestimonialRequest;
use App\Http\Requests\HomeTestimonials\StoreHomeTestimonialRequest;
use App\Http\Requests\HomeTestimonials\ToggleHomeTestimonialRequest;
use App\Http\Requests\HomeTestimonials\UpdateSortHomeTestimonialRequest;
use App\Jobs\UploadHomeTestimonialImage;
use App\Models\HomeTestimonial;
use Illuminate\Support\Facades\DB;

class HomeTestimonialController extends Controller
{
    /**
     * Obtener una lista de todos los testimonios ordenados por sort_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        try {
            $testimonials = HomeTestimonial::orderBy('sort_id')->get();

            return ApiResponseHelper::apiSuccess(200, 'Testimonios obtenidos exitosamente', ['testimonials' => $testimonials]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener la lista de testimonios', $e->getMessage(), 500, 'GET_TESTIMONIALS_ERROR');
        }
    }

    /**
     * Almacenar un nuevo testimonio
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreHomeTestimonialRequest $request)
    {
        try {
            $data = $request->validated();

            $sort_id = HomeTestimonial::max('sort_id') + 1 ?? 1;

            $testimonial = HomeTestimonial::create([
                'alt' => $data['alt'] ?? null,
                'sort_id' => $sort_id,
                'image_path' => 'processing',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $path = \App\Support\UploadableImage::storeTemp($image);
                UploadHomeTestimonialImage::dispatch($path, $testimonial->uuid, $sort_id, $image->getClientOriginalName());
            }

            return ApiResponseHelper::apiSuccess(201, 'Testimonio creado exitosamente', ['testimonial' => $testimonial]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al crear el testimonio', $e->getMessage(), 500, 'CREATE_TESTIMONIAL_ERROR');
        }
    }

    /**
     * Eliminar testimonio mediante uuid (soft delete)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteHomeTestimonialRequest $request)
    {
        try {
            $data = $request->validated();

            $testimonial = HomeTestimonial::findByUuid($data['uuid']);

            if ($testimonial) {
                $testimonial->delete();

                return ApiResponseHelper::apiSuccess(200, 'Testimonio eliminado exitosamente');
            } else {
                return ApiResponseHelper::apiError('El testimonio no existe', 'No existe el id: ' . $data['uuid'], 404, 'TESTIMONIAL_NOT_FOUND');
            }
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al eliminar el testimonio', $e->getMessage(), 500, 'DELETE_TESTIMONIAL_ERROR');
        }
    }

    /**
     * Actualizar orden de los testimonios
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortUpdate(UpdateSortHomeTestimonialRequest $request)
    {
        try {
            $data = $request->validated();

            DB::transaction(function () use ($data) {
                foreach ($data['image_order'] as $order) {
                    HomeTestimonial::where('uuid', $order['uuid'])->update(['sort_id' => $order['sort_id']]);
                }
            });

            return ApiResponseHelper::apiSuccess(200, 'Testimonios reordenados exitosamente');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al reordenar los testimonios', $e->getMessage(), 500, 'SORT_TESTIMONIALS_ERROR');
        }
    }

    /**
     * Toggle del estado activo/inactivo de un testimonio
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(ToggleHomeTestimonialRequest $request)
    {
        try {
            $data = $request->validated();

            $testimonial = HomeTestimonial::findByUuid($data['uuid']);

            if ($testimonial) {
                $testimonial->active = !$testimonial->active;
                $testimonial->save();

                return ApiResponseHelper::apiSuccess(200, 'Estado del testimonio actualizado exitosamente', ['testimonial' => $testimonial]);
            } else {
                return ApiResponseHelper::apiError('El testimonio no existe', 'No existe el id: ' . $data['uuid'], 404, 'TESTIMONIAL_NOT_FOUND');
            }
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al cambiar el estado del testimonio', $e->getMessage(), 500, 'TOGGLE_TESTIMONIAL_ERROR');
        }
    }
}
