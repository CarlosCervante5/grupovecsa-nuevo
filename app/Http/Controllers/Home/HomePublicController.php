<?php

namespace App\Http\Controllers\Home;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use App\Models\HomeTestimonial;

class HomePublicController extends Controller
{
    /**
     * Obtener slides activos ordenados por sort_id (público)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function slides()
    {
        try {
            $slides = HomeSlide::where('active', true)->orderBy('sort_id')->get();

            return ApiResponseHelper::apiSuccess(200, 'Slides obtenidos exitosamente', ['slides' => $slides]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los slides', $e->getMessage(), 500, 'GET_PUBLIC_SLIDES_ERROR');
        }
    }

    /**
     * Obtener testimonios activos ordenados por sort_id (público)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function testimonials()
    {
        try {
            $testimonials = HomeTestimonial::where('active', true)->orderBy('sort_id')->get();

            return ApiResponseHelper::apiSuccess(200, 'Testimonios obtenidos exitosamente', ['testimonials' => $testimonials]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener los testimonios', $e->getMessage(), 500, 'GET_PUBLIC_TESTIMONIALS_ERROR');
        }
    }
}
