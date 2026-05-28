<?php

namespace App\Services\Benchmark;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BenchmarkAdsPdfReportService
{
    private const MAX_ADS_PER_COMPETITOR = 40;

    private const MAX_IMAGE_BYTES = 2_500_000;

    /**
     * Genera PDF A4 horizontal y lo guarda en $reportsDir.
     *
     * @param  array<int, array<string, mixed>>  $results
     */
    public function generateAndStore(array $results, string $reportsDir, string $timestamp): string
    {
        $viewData = $this->buildViewData($results);
        $filename = "reporte-{$timestamp}.pdf";
        $path = rtrim($reportsDir, '/').'/'.$filename;

        $pdf = Pdf::loadView('benchmark.report-pdf', $viewData)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true);

        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array<string, mixed>
     */
    public function buildViewData(array $results): array
    {
        $sections = [];
        $totalAds = 0;

        foreach ($results as $row) {
            if (! is_array($row)) {
                continue;
            }

            $competitor = (string) ($row['competitor'] ?? 'Desconocido');
            $error = isset($row['error']) && is_string($row['error']) ? trim($row['error']) : '';

            if ($error !== '') {
                $sections[] = [
                    'competitor' => $competitor,
                    'error' => $error,
                    'ads' => [],
                ];

                continue;
            }

            $ads = [];
            foreach ($this->normalizeAdsForPdf($row) as $ad) {
                if (count($ads) >= self::MAX_ADS_PER_COMPETITOR) {
                    break;
                }
                $ads[] = $ad;
            }

            $totalAds += count($ads);
            $sections[] = [
                'competitor' => $competitor,
                'error' => null,
                'ads' => $ads,
            ];
        }

        return [
            'generatedAt' => now()->format('d/m/Y H:i'),
            'totalAds' => $totalAds,
            'sections' => $sections,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, array{text: string, image: ?string}>
     */
    private function normalizeAdsForPdf(array $result): array
    {
        $out = [];

        if (isset($result['ads']) && is_array($result['ads'])) {
            foreach ($result['ads'] as $ad) {
                if (! is_array($ad)) {
                    continue;
                }
                $out[] = $this->mapAdRow($ad);
            }

            return $out;
        }

        if (isset($result['data']) && is_array($result['data'])) {
            foreach ($result['data'] as $ad) {
                if (! is_array($ad)) {
                    continue;
                }
                $bodies = $ad['ad_creative_bodies'] ?? '';
                $text = is_array($bodies)
                    ? implode("\n", array_filter($bodies, fn ($b) => is_string($b) && $b !== ''))
                    : (string) $bodies;
                $imageUrl = isset($ad['ad_snapshot_url']) && is_string($ad['ad_snapshot_url'])
                    ? $ad['ad_snapshot_url']
                    : null;
                $out[] = [
                    'text' => $this->truncateText($text),
                    'image' => $this->imageToDataUri($imageUrl),
                ];
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $ad
     * @return array{text: string, image: ?string}
     */
    private function mapAdRow(array $ad): array
    {
        $text = (string) ($ad['text'] ?? '');
        $imageUrl = null;
        if (! empty($ad['images']) && is_array($ad['images'])) {
            foreach ($ad['images'] as $img) {
                if (is_string($img) && $img !== '') {
                    $imageUrl = $img;
                    break;
                }
            }
        }

        return [
            'text' => $this->truncateText($text),
            'image' => $this->imageToDataUri($imageUrl),
        ];
    }

    private function truncateText(string $text): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        if (mb_strlen($text) > 2000) {
            return mb_substr($text, 0, 1997).'…';
        }

        return $text;
    }

    private function imageToDataUri(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, 'data:image/')) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        try {
            $response = Http::timeout(20)
                ->connectTimeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; GrupoVECSA-Benchmark/1.0)'])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();
            if ($body === '' || strlen($body) > self::MAX_IMAGE_BYTES) {
                return null;
            }

            $mime = $response->header('Content-Type');
            if (! is_string($mime) || ! str_starts_with(strtolower($mime), 'image/')) {
                $mime = 'image/jpeg';
            }
            $mime = strtok($mime, ';') ?: 'image/jpeg';

            return 'data:'.$mime.';base64,'.base64_encode($body);
        } catch (\Throwable $e) {
            Log::debug('BENCHMARK_PDF_IMAGE_SKIP', ['url' => $url, 'message' => $e->getMessage()]);

            return null;
        }
    }
}
