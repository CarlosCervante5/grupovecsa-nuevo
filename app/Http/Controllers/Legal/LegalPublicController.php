<?php

namespace App\Http\Controllers\Legal;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Support\LegalDocumentRegistry;
use App\Support\LegalHtmlSanitizer;

class LegalPublicController extends Controller
{
    public function show(string $slug)
    {
        try {
            if (! LegalDocumentRegistry::isValidSlug($slug)) {
                return ApiResponseHelper::apiError('Documento no encontrado', null, 404, 'LEGAL_NOT_FOUND');
            }

            $document = LegalDocument::query()
                ->where('slug', $slug)
                ->where('is_published', true)
                ->first();

            if ($document === null) {
                return ApiResponseHelper::apiError('Documento no publicado', null, 404, 'LEGAL_NOT_PUBLISHED');
            }

            $meta = LegalDocumentRegistry::metaFor($slug);

            return ApiResponseHelper::apiSuccess(200, 'Documento legal obtenido', [
                'document' => [
                    'slug' => $document->slug,
                    'title' => $document->title,
                    'body_html' => LegalHtmlSanitizer::clean($document->body_html),
                    'meta_description' => $document->meta_description,
                    'public_path' => $meta['public_path'] ?? null,
                    'updated_at' => $document->updated_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener documento legal', $e->getMessage(), 500, 'LEGAL_PUBLIC_ERROR');
        }
    }
}
