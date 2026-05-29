<?php

namespace App\Support;

final class LegalDocumentRegistry
{
    /** @var array<string, array{title: string, meta_description: string, public_path: string, seed_file: string|null}> */
    public const DOCUMENTS = [
        'privacidad' => [
            'title' => 'Aviso de privacidad',
            'meta_description' => 'Información sobre el tratamiento y protección de sus datos personales en Grupo VECSA.',
            'public_path' => '/aviso-privacidad',
            'seed_file' => 'privacidad.html',
        ],
        'condiciones' => [
            'title' => 'Condiciones de uso',
            'meta_description' => 'Términos y condiciones de uso de los sitios y servicios de Grupo VECSA.',
            'public_path' => '/condiciones-uso',
            'seed_file' => 'condiciones.html',
        ],
        'devoluciones' => [
            'title' => 'Políticas de devolución',
            'meta_description' => 'Políticas de devolución y reembolso de productos y servicios de Grupo VECSA.',
            'public_path' => '/politicas-devolucion',
            'seed_file' => 'devoluciones.html',
        ],
        'lealtad' => [
            'title' => 'Programa de lealtad',
            'meta_description' => 'Información del programa de lealtad y recompensas VECSA.',
            'public_path' => '/programa-lealtad',
            'seed_file' => 'lealtad.html',
        ],
        'cookies' => [
            'title' => 'Uso de cookies',
            'meta_description' => 'Información sobre cookies y tecnologías de rastreo en el sitio de Grupo VECSA.',
            'public_path' => '/uso-cookies',
            'seed_file' => 'cookies.html',
        ],
    ];

    public static function isValidSlug(string $slug): bool
    {
        return isset(self::DOCUMENTS[$slug]);
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::DOCUMENTS);
    }

    /**
     * @return array{title: string, meta_description: string, public_path: string, seed_file: string|null}|null
     */
    public static function metaFor(string $slug): ?array
    {
        return self::DOCUMENTS[$slug] ?? null;
    }
}
