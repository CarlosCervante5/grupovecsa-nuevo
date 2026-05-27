<?php

namespace App\Rules;

use App\Support\UploadableImage;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class UploadableImageFile implements ValidationRule
{
    public function __construct(
        private readonly bool $allowPdf = false
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('El archivo no es válido.');

            return;
        }

        if (! UploadableImage::isAllowed($value, $this->allowPdf)) {
            $formats = 'JPEG, PNG, GIF, WEBP o HEIC (foto iPhone)';
            if ($this->allowPdf) {
                $formats .= ' o PDF';
            }
            $fail("Formato no permitido. Use {$formats}.");
        }
    }
}
