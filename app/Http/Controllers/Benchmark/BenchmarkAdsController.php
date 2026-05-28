<?php

namespace App\Http\Controllers\Benchmark;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BenchmarkAdsController extends Controller
{
    private string $dataDir = 'benchmark/data';
    private string $reportsDir = 'benchmark/reports';

    private function metaTokenStoragePath(): string
    {
        return 'benchmark/meta_access_token.enc';
    }

    // ─── Meta access token (Ad Library) ───

    /**
     * Estado del token: si hay valor en storage cifrado o en META_ACCESS_TOKEN (.env).
     */
    public function metaTokenStatus()
    {
        $fromStorage = $this->getMetaTokenFromStorage();
        $fromEnv = $this->getMetaTokenFromEnv();

        return response()->json([
            'configured' => $fromStorage !== null || $fromEnv !== null,
            'source' => $fromStorage !== null ? 'storage' : ($fromEnv !== null ? 'env' : null),
        ]);
    }

    /**
     * Opciones de UI: si el proxy al servicio Node reportADS está configurado (Web Scraper).
     */
    public function options()
    {
        $base = rtrim((string) env('BENCHMARK_REPORT_ADS_URL', ''), '/');

        return response()->json([
            'scraperProxyConfigured' => $base !== '',
        ]);
    }

    public function saveMetaToken(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|min:10|max:4096',
        ]);

        $token = trim($data['token']);
        if ($token === '' || $token === 'TU_ACCESS_TOKEN_AQUI') {
            return response()->json(['error' => 'Token inválido'], 422);
        }

        try {
            Storage::put($this->metaTokenStoragePath(), Crypt::encryptString($token));
        } catch (\Throwable $e) {
            return response()->json(['error' => 'No se pudo guardar el token'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token guardado de forma segura en el servidor.',
            'configured' => true,
            'source' => 'storage',
        ]);
    }

    public function clearMetaToken()
    {
        if (Storage::exists($this->metaTokenStoragePath())) {
            Storage::delete($this->metaTokenStoragePath());
        }

        return response()->json([
            'success' => true,
            'message' => 'Token eliminado del almacenamiento de la aplicación. Si existe META_ACCESS_TOKEN en .env, se seguirá usando.',
            'configured' => $this->getMetaTokenFromEnv() !== null,
            'source' => $this->getMetaTokenFromEnv() !== null ? 'env' : null,
        ]);
    }

    // ─── Competitors ───

    public function competitors()
    {
        return response()->json($this->getCompetitors());
    }

    public function addCompetitor(Request $request)
    {
        $name = trim($request->input('name', ''));
        if (!$name) {
            return response()->json(['error' => 'Nombre requerido'], 400);
        }

        $list = $this->getCompetitors();
        if (in_array($name, $list)) {
            return response()->json(['error' => 'Ya existe'], 400);
        }

        $list[] = $name;
        $this->saveCompetitors($list);

        return response()->json(['success' => true, 'competitors' => $list]);
    }

    public function removeCompetitor(string $name)
    {
        $name = urldecode($name);
        $list = $this->getCompetitors();
        $index = array_search($name, $list);

        if ($index === false) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        array_splice($list, $index, 1);
        $this->saveCompetitors($list);

        return response()->json(['success' => true, 'competitors' => $list]);
    }

    // ─── Scan ───

    public function scan(Request $request)
    {
        $competitors = $request->input('competitors', $this->getCompetitors());
        if (! is_array($competitors) || count($competitors) === 0) {
            return response()->json(['error' => 'No hay competidores para escanear.'], 400);
        }

        $method = $request->input('method', 'api');
        if (! in_array($method, ['api', 'scraper'], true)) {
            $method = 'api';
        }

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            if ($method === 'scraper') {
                $results = $this->runScraperViaRemoteService($competitors);
                if ($results instanceof \Illuminate\Http\JsonResponse) {
                    return $results;
                }
            } else {
                $token = $this->getMetaToken();
                if (! $token) {
                    return response()->json(['error' => 'No hay token de Meta. Configúralo en Benchmark ADS (Token de Meta) o define META_ACCESS_TOKEN en el .env del backend.'], 400);
                }

                $delayUs = (int) env('BENCHMARK_META_REQUEST_DELAY_US', 250000);
                if ($delayUs < 0) {
                    $delayUs = 0;
                }

                $results = [];
                foreach ($competitors as $competitor) {
                    $raw = $this->searchMetaAds((string) $competitor, $token);
                    $results[] = $this->enrichScanResultForUi($raw);
                    if ($delayUs > 0) {
                        usleep($delayUs);
                    }
                }
            }

            $timestamp = now()->format('Y-m-d\TH-i-s');
            $dataFile = $this->dataDir . "/scan-{$timestamp}.json";
            Storage::put($dataFile, json_encode($results, JSON_PRETTY_PRINT));

            $htmlFile = $this->generateHTMLReport($results, $timestamp);
            $csvFile = $this->generateCSVReport($results, $timestamp);

            return response()->json([
                'success' => true,
                'method' => $method,
                'summary' => collect($results)->map(function ($r) {
                    return [
                        'competitor' => $r['competitor'] ?? '',
                        'adsCount' => (int) ($r['adsFound'] ?? count($r['ads'] ?? $r['data'] ?? [])),
                        'error' => $r['error'] ?? null,
                    ];
                }),
                'files' => ['data' => $dataFile, 'html' => $htmlFile, 'csv' => $csvFile],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── Search single ───

    public function search(Request $request)
    {
        $q = $request->query('q');
        if (!$q) {
            return response()->json(['error' => 'Parámetro q requerido'], 400);
        }

        $token = $this->getMetaToken();
        if (!$token) {
            return response()->json(['error' => 'No hay token de Meta. Configúralo en Benchmark ADS (Token de Meta) o define META_ACCESS_TOKEN en el .env del backend.'], 400);
        }

        try {
            $result = $this->enrichScanResultForUi($this->searchMetaAds($q, $token));

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ─── History ───

    public function history()
    {
        $files = $this->getDataFiles();
        return response()->json(collect($files)->map(function ($f) {
            return [
                'file' => basename($f),
                'date' => str_replace(['scan-', '.json'], '', basename($f)),
            ];
        })->values());
    }

    public function historyDetail(string $file)
    {
        $path = $this->dataDir . '/' . $file;
        if (!Storage::exists($path)) {
            return response()->json(['error' => 'No encontrado'], 404);
        }
        return response()->json(json_decode(Storage::get($path), true));
    }

    // ─── Reports ───

    public function reports()
    {
        if (!Storage::exists($this->reportsDir)) {
            return response()->json([]);
        }

        $files = collect(Storage::files($this->reportsDir))
            ->map(fn($f) => basename($f))
            ->filter(fn($f) => str_ends_with($f, '.html') || str_ends_with($f, '.csv'))
            ->sortDesc()
            ->values();

        return response()->json($files);
    }

    // ─── Private helpers ───

    private function getCompetitors(): array
    {
        $path = 'benchmark/competitors.json';
        if (Storage::exists($path)) {
            return json_decode(Storage::get($path), true) ?: [];
        }
        // Fallback: read from env or default
        $env = env('BENCHMARK_COMPETITORS', 'BMWVanguardiaMotors,bmwhubserdan');
        $list = array_filter(array_map('trim', explode(',', $env)));
        $this->saveCompetitors($list);
        return $list;
    }

    private function saveCompetitors(array $list): void
    {
        Storage::put('benchmark/competitors.json', json_encode(array_values($list)));
    }

    private function getMetaToken(): ?string
    {
        $fromStorage = $this->getMetaTokenFromStorage();
        if ($fromStorage !== null) {
            return $fromStorage;
        }

        return $this->getMetaTokenFromEnv();
    }

    private function getMetaTokenFromStorage(): ?string
    {
        $path = $this->metaTokenStoragePath();
        if (! Storage::exists($path)) {
            return null;
        }
        try {
            $raw = trim((string) Storage::get($path));
            if ($raw === '') {
                return null;
            }

            return Crypt::decryptString($raw);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getMetaTokenFromEnv(): ?string
    {
        $token = (string) env('META_ACCESS_TOKEN', '');
        $token = trim($token);

        return ($token !== '' && $token !== 'TU_ACCESS_TOKEN_AQUI') ? $token : null;
    }

    /**
     * @return array<int, mixed>|\Illuminate\Http\JsonResponse
     */
    private function runScraperViaRemoteService(array $competitors)
    {
        $base = rtrim((string) env('BENCHMARK_REPORT_ADS_URL', ''), '/');
        if ($base === '') {
            return response()->json([
                'error' => 'Modo Web Scraper: falta BENCHMARK_REPORT_ADS_URL en el backend (URL pública del servicio Node reportADS con Puppeteer). En Railway, añádala a las variables del servicio Laravel. Alternativa: use Meta API con token.',
                'code' => 'SCRAPER_UNAVAILABLE',
            ], 422);
        }

        $timeout = (int) env('BENCHMARK_REPORT_ADS_TIMEOUT', 900);
        if ($timeout < 60) {
            $timeout = 60;
        }

        $response = Http::timeout($timeout)
            ->connectTimeout(30)
            ->post($base.'/api/scan', [
                'competitors' => array_values($competitors),
                'method' => 'scraper',
                'includeResults' => true,
            ]);

        $body = $response->json();
        if ($response->failed()) {
            $upstreamStatus = (int) $response->status();
            $msg = is_array($body) ? ($body['error'] ?? json_encode($body)) : $response->body();
            if (! is_string($msg) || $msg === '') {
                $msg = 'Error al contactar el servicio de scraper.';
            }

            if ($upstreamStatus === 404) {
                return response()->json([
                    'error' => 'No se encontró POST '.$base.'/api/scan. BENCHMARK_REPORT_ADS_URL debe apuntar al servicio Node reportADS desplegado (no al backend Laravel). Alternativa: use el método Meta API con token.',
                    'code' => 'SCRAPER_ENDPOINT_NOT_FOUND',
                ], 502);
            }

            // No reenviar 404/4xx del upstream como status HTTP del API Laravel (confunde con “ruta no existe”).
            return response()->json([
                'error' => $msg,
                'code' => 'SCRAPER_UPSTREAM_ERROR',
                'upstreamStatus' => $upstreamStatus > 0 ? $upstreamStatus : null,
            ], 502);
        }

        if (! ($body['success'] ?? false)) {
            return response()->json([
                'error' => is_array($body) ? ($body['error'] ?? 'Fallo del servicio de scraper') : 'Fallo del servicio de scraper',
            ], 502);
        }

        $results = $body['results'] ?? null;
        if (! is_array($results)) {
            return response()->json([
                'error' => 'El servicio reportADS no devolvió el cuerpo de resultados. Actualice reportADS (POST /api/scan con includeResults: true) o use Meta API.',
                'code' => 'SCRAPER_LEGACY',
            ], 502);
        }

        $enriched = [];
        foreach ($results as $row) {
            $enriched[] = $this->enrichScanResultForUi(is_array($row) ? $row : []);
        }

        return $enriched;
    }

    private function searchMetaAds(string $searchTerm, string $token): array
    {
        $base = [
            'competitor' => $searchTerm,
            'data' => [],
            'fetchedAt' => now()->toISOString(),
        ];

        try {
            $response = Http::timeout(90)
                ->connectTimeout(25)
                ->get('https://graph.facebook.com/v21.0/ads_archive', [
                    'access_token' => $token,
                    'search_terms' => $searchTerm,
                    'ad_reached_countries' => 'MX',
                    'ad_active_status' => 'ACTIVE',
                    'ad_type' => 'ALL',
                    'fields' => implode(',', [
                        'id',
                        'page_id',
                        'page_name',
                        'ad_creation_time',
                        'ad_delivery_start_time',
                        'ad_creative_bodies',
                        'publisher_platforms',
                        'ad_snapshot_url',
                    ]),
                    'limit' => 50,
                ]);

            if ($response->failed()) {
                $snippet = $response->json('error.message');
                if (! is_string($snippet) || $snippet === '') {
                    $snippet = substr($response->body(), 0, 500);
                }

                return array_merge($base, [
                    'error' => 'HTTP '.$response->status().': '.$snippet,
                ]);
            }

            $payload = $response->json();
            if (isset($payload['error']) && is_array($payload['error'])) {
                $msg = $payload['error']['message'] ?? json_encode($payload['error']);

                return array_merge($base, ['error' => is_string($msg) ? $msg : json_encode($payload['error'])]);
            }

            return array_merge($base, [
                'data' => $payload['data'] ?? [],
                'paging' => $payload['paging'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return array_merge($base, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Añade claves `ads` y `adsFound` para la UI y reportes (formato compatible con el scraper Node).
     */
    private function enrichScanResultForUi(array $result): array
    {
        if (array_key_exists('ads', $result) && is_array($result['ads'])) {
            $result['adsFound'] = (int) ($result['adsFound'] ?? count($result['ads']));

            return $result;
        }

        $items = $result['data'] ?? [];
        $ads = [];
        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }
            $bodies = $row['ad_creative_bodies'] ?? null;
            $text = is_array($bodies)
                ? implode("\n", array_filter($bodies, fn ($b) => is_string($b) && $b !== ''))
                : (string) $bodies;
            $images = [];
            if (! empty($row['ad_snapshot_url']) && is_string($row['ad_snapshot_url'])) {
                $images[] = $row['ad_snapshot_url'];
            }
            $ads[] = [
                'text' => $text,
                'images' => $images,
                'imageCount' => count($images),
                'videoCount' => 0,
            ];
        }
        $result['ads'] = $ads;
        $result['adsFound'] = count($ads);

        return $result;
    }

    private function getDataFiles(): array
    {
        if (!Storage::exists($this->dataDir)) {
            return [];
        }
        return collect(Storage::files($this->dataDir))
            ->filter(fn($f) => str_ends_with($f, '.json'))
            ->sortDesc()
            ->values()
            ->toArray();
    }

    private function generateHTMLReport(array $results, string $timestamp): string
    {
        $date = now()->format('d \d\e F Y');
        $totalAds = collect($results)->sum(fn ($r) => (int) ($r['adsFound'] ?? count($r['ads'] ?? $r['data'] ?? [])));
        $successful = collect($results)->filter(fn ($r) => empty($r['error']))->count();

        $competitorRows = '';
        foreach ($results as $r) {
            $name = $r['competitor'] ?? 'Desconocido';
            $count = (int) ($r['adsFound'] ?? count($r['ads'] ?? $r['data'] ?? []));
            $hasError = !empty($r['error']);
            $status = $hasError ? '<span style="color:#ff4444">Error</span>' : ($count > 0 ? 'Activo' : 'Sin anuncios');
            $fetchedAt = $r['fetchedAt'] ?? '-';
            $competitorRows .= "<tr><td>{$name}</td><td>" . ($hasError ? '<span style="color:#ff4444">Error</span>' : $count) . "</td><td>{$status}</td><td>{$fetchedAt}</td></tr>";
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Reporte ADS - {$date}</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:sans-serif;background:#0a0a0a;color:#e0e0e0;padding:20px}.c{max-width:1200px;margin:0 auto}h1{color:#1877f2;margin-bottom:5px}h2{color:#ccc;margin:30px 0 15px;border-bottom:1px solid #333;padding-bottom:8px}.sub{color:#888;margin-bottom:30px}.sg{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:30px}.sc{background:#1a1a1a;border:1px solid #333;border-radius:8px;padding:20px;text-align:center}.sc .n{font-size:36px;font-weight:bold;color:#1877f2}.sc .l{color:#888;margin-top:5px}table{width:100%;border-collapse:collapse;background:#1a1a1a;border-radius:8px;overflow:hidden}th{background:#1877f2;color:#fff;padding:12px 15px;text-align:left}td{padding:12px 15px;border-bottom:1px solid #333}tr:hover{background:#222}</style>
</head><body><div class="c">
<h1>Reporte de Anuncios - Competencia BMW México</h1>
<p class="sub">Generado: {$date} | Fuente: Meta Ad Library API</p>
<div class="sg"><div class="sc"><div class="n">{$totalAds}</div><div class="l">Anuncios encontrados</div></div><div class="sc"><div class="n">{$successful}</div><div class="l">Búsquedas exitosas</div></div></div>
<h2>Resumen por Competidor</h2>
<table><thead><tr><th>Competidor</th><th>Anuncios</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>{$competitorRows}</tbody></table>
</div></body></html>
HTML;

        $path = $this->reportsDir . "/reporte-{$timestamp}.html";
        Storage::put($path, $html);
        return $path;
    }

    private function generateCSVReport(array $results, string $timestamp): string
    {
        $rows = ["Competidor,Anuncios Activos,Estado,Fecha"];
        foreach ($results as $r) {
            $name = $r['competitor'] ?? 'Desconocido';
            $count = (int) ($r['adsFound'] ?? count($r['ads'] ?? $r['data'] ?? []));
            $status = !empty($r['error']) ? 'Error' : ($count > 0 ? 'Activo' : 'Sin anuncios');
            $date = $r['fetchedAt'] ?? '-';
            $rows[] = "\"{$name}\",{$count},\"{$status}\",\"{$date}\"";
        }

        $path = $this->reportsDir . "/reporte-{$timestamp}.csv";
        Storage::put($path, implode("\n", $rows));
        return $path;
    }
}
