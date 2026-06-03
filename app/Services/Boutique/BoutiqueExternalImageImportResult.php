<?php

namespace App\Services\Boutique;

final class BoutiqueExternalImageImportResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $errorMessage = null,
        public readonly ?string $hint = null,
    ) {}

    public static function ok(): self
    {
        return new self(true);
    }

    public static function fail(string $message, ?string $hint = null): self
    {
        return new self(false, $message, $hint);
    }
}
