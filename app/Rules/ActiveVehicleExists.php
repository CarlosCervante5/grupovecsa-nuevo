<?php

namespace App\Rules;

use App\Models\Vehicle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveVehicleExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Vehicle::where('uuid', $value)->whereNull('deleted_at')->exists();

        if (!$exists) {
            $fail('El :attribute does not exist or has been eliminated.');
        }
    }
}
