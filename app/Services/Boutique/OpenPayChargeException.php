<?php

namespace App\Services\Boutique;

use Exception;

class OpenPayChargeException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 400,
        public readonly ?array $openpayBody = null,
        public readonly ?string $openpayErrorCode = null,
    ) {
        parent::__construct($message);
    }
}
