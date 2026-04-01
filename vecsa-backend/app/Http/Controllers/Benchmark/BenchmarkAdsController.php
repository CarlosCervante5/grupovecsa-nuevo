<?php

namespace App\Http\Controllers\Benchmark;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BenchmarkAdsController extends Controller
{
    private string $dataDir = 'benchmark/data';
    private string $reportsDir = 'benchmark/reports';

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
        $token = $this->getMetaToken();

        if (!$token) {
            return response()->json(['error' => 'No se ha configurado META_ACCESS_TOKEN'], 400);
        }

        try {
            $results = [];
            foreach ($competitors as $competitor) {
                $results[] = $this->searchMetaAds($competitor, $token);
                usleep(1000000); // 1s delay between requests
            }

            $timestamp = now()->format('Y-m-d\TH-i-s');
            $dataFile = $this->dataDir . "/scan-{$timestamp}.json";
            Storage::put($dataFile, json_encode($results, JSON_PRETTY_PRINT));

            $htmlFile = $this->generateHTMLReport($results, $timestamp);
            $csvFile = $this->generateCSVReport($results, $timestamp);

            return response()->json([
                'success' => true,
                'summary' => collect($results)->map(function ($r) {
                    return [
                        'competitor' => $r['competitor'],
                        'adsCount' => count($r['data'] ?? []),
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
            return response()->json(['error' => 'No se ha configurado META_ACCESS_TOKEN'], 400);
        }

        try {
            $result = $this->searchMetaAds($q, $token);
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
        $token = env('META_ACCESS_TOKEN', '');
        return ($token && $token !== 'TU_ACCESS_TOKEN_AQUI') ? $token : null;
    }

    private function searchMetaAds(string $searchTerm, string $token): array
    {
        try {
            $response = Http::timeout(30)->get('https://graph.facebook.com/v21.0/ads_archive', [
                'access_token' => $token,
                'search_terms' => $searchTerm,
                'ad_reached_countries' => 'MX',
                'ad_active_status' => 'ACTIVE',
                'ad_type' => 'ALL',
                'fields' => implode(',', [
                    'id', 'ad_creation_time', 'ad_creative_bodies',
                    'ad_creative_link_captions', 'ad_creative_link_titles',
                    'ad_delivery_start_time', 'ad_delivery_stop_time',
                    'page_id', 'page_name', 'publisher_platforms',
                    'estimated_audience_size', 'impressions',
                    'spend', 'currency', 'languages',
                ]),
                'limit' => 50,
            ]);

            $data = $response->json();

            return [
                'competitor' => $searchTerm,
                'data' => $data['data'] ?? [],
                'paging' => $data['paging'] ?? null,
                'fetchedAt' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            return [
                'competitor' => $searchTerm,
                'data' => [],
                'error' => $e->getMessage(),
                'fetchedAt' => now()->toISOString(),
            ];
        }
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
        $totalAds = collect($results)->sum(fn($r) => count($r['data'] ?? []));
        $successful = collect($results)->filter(fn($r) => empty($r['error']))->count();

        $competitorRows = '';
        foreach ($results as $r) {
            $name = $r['competitor'] ?? 'Desconocido';
            $count = count($r['data'] ?? []);
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
            $count = count($r['data'] ?? []);
            $status = !empty($r['error']) ? 'Error' : ($count > 0 ? 'Activo' : 'Sin anuncios');
            $date = $r['fetchedAt'] ?? '-';
            $rows[] = "\"{$name}\",{$count},\"{$status}\",\"{$date}\"";
        }

        $path = $this->reportsDir . "/reporte-{$timestamp}.csv";
        Storage::put($path, implode("\n", $rows));
        return $path;
    }
}
