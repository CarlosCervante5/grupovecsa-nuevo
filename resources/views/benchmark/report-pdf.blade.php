<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Benchmark ADS</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 14mm 12mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .cover {
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #1877f2;
        }
        .cover h1 {
            font-size: 20px;
            color: #1877f2;
            margin: 0 0 6px 0;
        }
        .cover .meta {
            color: #555;
            font-size: 9px;
        }
        .section {
            page-break-inside: avoid;
            margin-bottom: 16px;
        }
        .section-title {
            background: #1877f2;
            color: #fff;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 10px 0;
            border-radius: 4px;
        }
        .section-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px 12px;
            border-radius: 4px;
            margin-bottom: 8px;
        }
        .ad-row {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            overflow: hidden;
        }
        .ad-row table {
            width: 100%;
            border-collapse: collapse;
        }
        .ad-row td {
            vertical-align: top;
            padding: 0;
        }
        .ad-image-cell {
            width: 42%;
            background: #f3f4f6;
            border-right: 1px solid #e5e7eb;
        }
        .ad-image-wrap {
            width: 100%;
            height: 155px;
            text-align: center;
            line-height: 155px;
        }
        .ad-image-wrap img {
            max-width: 100%;
            max-height: 155px;
            vertical-align: middle;
        }
        .ad-no-image {
            color: #9ca3af;
            font-size: 9px;
            font-style: italic;
        }
        .ad-text-cell {
            width: 58%;
            padding: 12px 14px !important;
        }
        .ad-label {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }
        .ad-text {
            font-size: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="cover">
        <h1>Reporte de anuncios — Benchmark ADS</h1>
        <div class="meta">
            Generado: {{ $generatedAt }} · Total de anuncios en este reporte: {{ $totalAds }}
        </div>
    </div>

    @foreach ($sections as $index => $section)
        <div class="section {{ $index > 0 ? 'page-break' : '' }}">
            <div class="section-title">{{ $section['competitor'] }}</div>

            @if (!empty($section['error']))
                <div class="section-error">{{ $section['error'] }}</div>
            @elseif (count($section['ads']) === 0)
                <p style="color:#6b7280; padding: 8px 0;">Sin anuncios activos detectados.</p>
            @else
                @foreach ($section['ads'] as $adIndex => $ad)
                    <div class="ad-row">
                        <table>
                            <tr>
                                <td class="ad-image-cell">
                                    <div class="ad-image-wrap">
                                        @if (!empty($ad['image']))
                                            <img src="{{ $ad['image'] }}" alt="Anuncio {{ $adIndex + 1 }}">
                                        @else
                                            <span class="ad-no-image">Sin imagen disponible</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="ad-text-cell">
                                    <div class="ad-label">Anuncio #{{ $adIndex + 1 }} — Texto</div>
                                    <div class="ad-text">{{ $ad['text'] !== '' ? $ad['text'] : '(Sin texto en el anuncio)' }}</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</body>
</html>
