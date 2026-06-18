<?php

namespace App\Http\Controllers\Legal;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use App\Support\LegalDocumentRegistry;
use App\Support\LegalHtmlSanitizer;
use Illuminate\Http\Request;

class LegalAdminController extends Controller
{
    public function index()
    {
        try {
            $existing = LegalDocument::query()->get()->keyBy('slug');
            $documents = [];

            foreach (LegalDocumentRegistry::DOCUMENTS as $slug => $meta) {
                $row = $existing->get($slug);
                $documents[] = [
                    'slug' => $slug,
                    'title' => $row?->title ?? $meta['title'],
                    'public_path' => $meta['public_path'],
                    'is_published' => $row?->is_published ?? false,
                    'has_content' => $row !== null && trim(strip_tags((string) $row->body_html)) !== '',
                    'updated_at' => $row?->updated_at?->toIso8601String(),
                ];
            }

            return ApiResponseHelper::apiSuccess(200, 'Documentos legales obtenidos', ['documents' => $documents]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al listar documentos legales', $e->getMessage(), 500, 'LEGAL_ADMIN_LIST_ERROR');
        }
    }

    public function show(string $slug)
    {
        try {
            if (! LegalDocumentRegistry::isValidSlug($slug)) {
                return ApiResponseHelper::apiError('Documento no encontrado', null, 404, 'LEGAL_NOT_FOUND');
            }

            $meta = LegalDocumentRegistry::metaFor($slug);
            $document = LegalDocument::findBySlug($slug);

            if ($document === null) {
                return ApiResponseHelper::apiSuccess(200, 'Documento legal (sin contenido guardado)', [
                    'document' => [
                        'slug' => $slug,
                        'title' => $meta['title'],
                        'body_html' => '',
                        'meta_description' => $meta['meta_description'],
                        'is_published' => false,
                        'public_path' => $meta['public_path'],
                    ],
                ]);
            }

            return ApiResponseHelper::apiSuccess(200, 'Documento legal obtenido', [
                'document' => [
                    'slug' => $document->slug,
                    'title' => $document->title,
                    'body_html' => $document->body_html,
                    'meta_description' => $document->meta_description,
                    'is_published' => $document->is_published,
                    'public_path' => $meta['public_path'],
                    'updated_at' => $document->updated_at?->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al obtener documento legal', $e->getMessage(), 500, 'LEGAL_ADMIN_SHOW_ERROR');
        }
    }

    public function update(Request $request, string $slug)
    {
        try {
            if (! LegalDocumentRegistry::isValidSlug($slug)) {
                return ApiResponseHelper::apiError('Documento no encontrado', null, 404, 'LEGAL_NOT_FOUND');
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'body_html' => 'required|string|max:512000',
                'meta_description' => 'nullable|string|max:500',
                'is_published' => 'sometimes|boolean',
            ]);

            $meta = LegalDocumentRegistry::metaFor($slug);
            $body = LegalHtmlSanitizer::clean($data['body_html']);

            $document = LegalDocument::findBySlug($slug);
            if ($document === null) {
                $document = LegalDocument::query()->create([
                    'slug' => $slug,
                    'title' => $data['title'],
                    'body_html' => $body,
                    'meta_description' => $data['meta_description'] ?? $meta['meta_description'],
                    'is_published' => (bool) ($data['is_published'] ?? true),
                    'updated_by' => $request->user()?->id,
                ]);
            } else {
                $document->fill([
                    'title' => $data['title'],
                    'body_html' => $body,
                    'meta_description' => $data['meta_description'] ?? $document->meta_description,
                    'is_published' => array_key_exists('is_published', $data)
                        ? (bool) $data['is_published']
                        : $document->is_published,
                    'updated_by' => $request->user()?->id,
                ]);
                $document->save();
            }

            return ApiResponseHelper::apiSuccess(200, 'Documento legal guardado', [
                'document' => [
                    'slug' => $document->slug,
                    'title' => $document->title,
                    'body_html' => $document->body_html,
                    'meta_description' => $document->meta_description,
                    'is_published' => $document->is_published,
                    'public_path' => $meta['public_path'],
                    'updated_at' => $document->updated_at?->toIso8601String(),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::apiError('Datos inválidos', $e->errors(), 422, 'LEGAL_VALIDATION_ERROR');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al guardar documento legal', $e->getMessage(), 500, 'LEGAL_ADMIN_UPDATE_ERROR');
        }
    }
}
