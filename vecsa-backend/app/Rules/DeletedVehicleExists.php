<?php

namespace App\Rules;

use App\Models\Vehicle;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DeletedVehicleExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Vehicle::onlyTrashed()->where('uuid', $value)->exists();

        if (!$exists) {
            $fail('El :attribute does not exist on eliminated vehicles');
        }
    }
}
