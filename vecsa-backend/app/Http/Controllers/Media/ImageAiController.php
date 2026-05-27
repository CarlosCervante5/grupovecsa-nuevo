<?php

namespace App\Http\Controllers\Media;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Boutique\BoutiqueProductImage;
use App\Models\VehicleImage;
use App\Services\DealershipAccessService;
use App\Services\Media\ImageAiPersistenceService;
use App\Services\Media\ImageAiProcessingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class ImageAiController extends Controller
{
    public function __construct(
        private ImageAiProcessingService $processing,
        private ImageAiPersistenceService $persistence,
        private DealershipAccessService $dealershipAccess,
    ) {}

    public function config()
    {
        return ApiResponseHelper::apiSuccess(200, 'Configuración de IA para imágenes', [
            'provider' => 'gemini',
            'enabled' => $this->processing->isEnabled(),
            'configured' => $this->processing->isConfigured(),
            'model_resolved' => $this->processing->resolvedModel(),
            'default_model_hint' => \App\Services\Media\ImageAiProcessingService::DEFAULT_IMAGE_MODEL,
            'actions' => $this->processing->availableActions(),
        ]);
    }

    public function process(Request $request)
    {
        try {
            if (! $this->processing->isEnabled()) {
                return ApiResponseHelper::apiError(
                    'El procesamiento de imágenes con IA no está habilitado.',
                    null,
                    503,
                    'IMAGE_AI_DISABLED'
                );
            }

            $data = $request->validate([
                'action' => ['required', 'string', Rule::in(['remove_background', 'enhance', 'studio_white'])],
                'source_url' => 'required|string|max:2000',
                'target_type' => ['required', 'string', Rule::in(['preview_only', 'vehicle_image', 'boutique_product_image'])],
                'target_uuid' => 'nullable|string|max:64',
                'replace_original' => 'nullable|boolean',
            ]);

            $sourceUrl = trim($data['source_url']);
            if (! filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
                return ApiResponseHelper::apiError('URL de imagen no válida', null, 422, 'INVALID_SOURCE_URL');
            }

            $targetType = $data['target_type'];
            $replaceOriginal = ($data['replace_original'] ?? true) !== false;

            if ($targetType !== 'preview_only' && empty($data['target_uuid'])) {
                return ApiResponseHelper::apiError('Falta target_uuid para guardar la imagen.', null, 422, 'TARGET_UUID_REQUIRED');
            }

            $this->assertTargetAccess($request, $targetType, $data['target_uuid'] ?? null);

            $aiResult = $this->processing->process($sourceUrl, $data['action']);
            $publicId = $aiResult['public_id'] ?? null;
            $mime = (string) ($aiResult['mime_type'] ?? 'image/png');
            $rawBase64 = $aiResult['processed_base64'] ?? null;
            $processedUrl = $aiResult['processed_url'] ?? null;

            if ($rawBase64 !== null && $rawBase64 !== '') {
                if ($targetType === 'preview_only' || ! $replaceOriginal) {
                    return ApiResponseHelper::apiSuccess(200, 'Vista previa generada', [
                        'preview_url' => 'data:'.$mime.';base64,'.$rawBase64,
                        'action' => $data['action'],
                        'saved' => false,
                    ]);
                }

                $binary = base64_decode((string) $rawBase64, true);
                if ($binary === false || $binary === '') {
                    throw new \RuntimeException('La imagen devuelta por Gemini no es válida (base64).');
                }

                $format = str_contains(strtolower($mime), 'png') ? 'png' : 'jpg';
                $finalUrl = $this->persistProcessedImage(
                    $targetType,
                    (string) $data['target_uuid'],
                    $binary,
                    $format
                );

                $this->processing->destroyTemp($publicId);

                return ApiResponseHelper::apiSuccess(200, 'Imagen procesada y guardada', [
                    'image_url' => $finalUrl,
                    'action' => $data['action'],
                    'saved' => true,
                ]);
            }

            if ($processedUrl === null || $processedUrl === '') {
                throw new \RuntimeException('No se recibió imagen procesada del motor de IA.');
            }

            if ($targetType === 'preview_only' || ! $replaceOriginal) {
                return ApiResponseHelper::apiSuccess(200, 'Vista previa generada', [
                    'preview_url' => $processedUrl,
                    'action' => $data['action'],
                    'saved' => false,
                ]);
            }

            $imageContents = $this->downloadProcessedImage((string) $processedUrl);
            $format = str_contains(strtolower((string) ($aiResult['mime_type'] ?? '')), 'png') ? 'png' : 'jpg';
            $finalUrl = $this->persistProcessedImage(
                $targetType,
                (string) $data['target_uuid'],
                $imageContents,
                $format
            );

            $this->processing->destroyTemp($publicId);

            return ApiResponseHelper::apiSuccess(200, 'Imagen procesada y guardada', [
                'image_url' => $finalUrl,
                'action' => $data['action'],
                'saved' => true,
            ]);
        } catch (AuthorizationException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 403, 'IMAGE_AI_FORBIDDEN');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return ApiResponseHelper::apiError($e->getMessage(), null, 502, 'IMAGE_AI_PROCESS_FAILED');
        } catch (\Exception $e) {
            return ApiResponseHelper::apiError('Error al procesar imagen con IA', $e->getMessage(), 500, 'IMAGE_AI_ERROR');
        }
    }

    private function assertTargetAccess(Request $request, string $targetType, ?string $targetUuid): void
    {
        if ($targetType === 'preview_only' || $targetUuid === null || $targetUuid === '') {
            return;
        }

        if ($targetType === 'vehicle_image') {
            $image = VehicleImage::with('vehicle')->findByUuid($targetUuid);
            if (! $image) {
                throw new AuthorizationException('Imagen de vehículo no encontrada.');
            }

            return;
        }

        if ($targetType === 'boutique_product_image') {
            $image = BoutiqueProductImage::with('product')->where('uuid', $targetUuid)->first();
            if (! $image || ! $image->product) {
                throw new AuthorizationException('Imagen de producto no encontrada.');
            }
            $this->dealershipAccess->assertProductDealershipAccessible($request->user(), $image->product->dealership_id);

            return;
        }
    }

    private function downloadProcessedImage(string $url): string
    {
        $response = Http::timeout(120)->get($url);
        if (! $response->successful()) {
            throw new \RuntimeException('No se pudo descargar la imagen procesada.');
        }

        $body = $response->body();
        if ($body === '') {
            throw new \RuntimeException('La imagen procesada está vacía.');
        }

        return $body;
    }

    private function persistProcessedImage(string $targetType, string $targetUuid, string $contents, string $format): string
    {
        $ext = $format === 'png' ? 'png' : 'jpg';

        if ($targetType === 'vehicle_image') {
            $image = VehicleImage::with('vehicle')->findByUuid($targetUuid);
            if (! $image || ! $image->vehicle) {
                throw new \RuntimeException('Imagen de vehículo no encontrada.');
            }

            return $this->persistence->persistVehicleImage($image, $image->vehicle->uuid, $contents, $ext);
        }

        $image = BoutiqueProductImage::with('product')->where('uuid', $targetUuid)->first();
        if (! $image || ! $image->product) {
            throw new \RuntimeException('Imagen de producto no encontrada.');
        }

        return $this->persistence->persistBoutiqueProductImage($image, $image->product->uuid, $contents, $ext);
    }
}
