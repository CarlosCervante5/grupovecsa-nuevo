<?php

namespace App\Services\Boutique;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnviacomService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected array $origin;

    public function __construct()
    {
        $this->apiKey = env('ENVIACOM_API_KEY', '');
        $this->apiUrl = rtrim(env('ENVIACOM_API_URL', 'https://api.envia.com'), '/');
        $this->origin = [
            'name' => env('ENVIACOM_ORIGIN_NAME', ''),
            'street' => env('ENVIACOM_ORIGIN_STREET', ''),
            'city' => env('ENVIACOM_ORIGIN_CITY', ''),
            'state' => env('ENVIACOM_ORIGIN_STATE', ''),
            'zip' => env('ENVIACOM_ORIGIN_ZIP', ''),
            'phone' => env('ENVIACOM_ORIGIN_PHONE', ''),
            'country' => env('ENVIACOM_ORIGIN_COUNTRY', 'MX'),
        ];
    }

    /**
     * Get shipping quotes from Envia.com.
     */
    public function getShippingQuotes(array $destination, array $packages): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/ship/rate/', [
                'origin' => [
                    'name' => $this->origin['name'],
                    'street' => $this->origin['street'],
                    'city' => $this->origin['city'],
                    'state' => $this->origin['state'],
                    'postal_code' => $this->origin['zip'],
                    'phone' => $this->origin['phone'],
                    'country' => $this->origin['country'],
                ],
                'destination' => [
                    'name' => $destination['name'] ?? '',
                    'street' => $destination['street'] ?? '',
                    'city' => $destination['city'] ?? '',
                    'state' => $destination['state'] ?? '',
                    'postal_code' => $destination['zip'] ?? '',
                    'phone' => $destination['phone'] ?? '',
                    'country' => $destination['country'] ?? 'MX',
                ],
                'packages' => $packages,
            ]);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            Log::error('Envia.com getShippingQuotes error', ['response' => $response->body()]);
            throw new Exception('Error al obtener cotizaciones de envío');
        } catch (Exception $e) {
            Log::error('Envia.com getShippingQuotes exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a shipment via Envia.com.
     */
    public function createShipment(array $origin, array $destination, array $packages, string $carrier): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/ship/generate/', [
                'origin' => $origin,
                'destination' => $destination,
                'packages' => $packages,
                'carrier' => $carrier,
            ]);

            if ($response->successful()) {
                $data = $response->json('data', []);
                return [
                    'tracking_number' => $data['trackingNumber'] ?? $data['tracking_number'] ?? null,
                    'label_url' => $data['label'] ?? $data['label_url'] ?? null,
                    'shipment_id' => $data['shipmentId'] ?? $data['shipment_id'] ?? null,
                ];
            }

            Log::error('Envia.com createShipment error', ['response' => $response->body()]);
            throw new Exception('Error al crear el envío');
        } catch (Exception $e) {
            Log::error('Envia.com createShipment exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Track a shipment via Envia.com.
     */
    public function trackShipment(string $trackingNumber): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/ship/tracking/', [
                'tracking_number' => $trackingNumber,
            ]);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            Log::error('Envia.com trackShipment error', ['response' => $response->body()]);
            throw new Exception('Error al rastrear el envío');
        } catch (Exception $e) {
            Log::error('Envia.com trackShipment exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
