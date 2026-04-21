<?php

namespace App\Http\Requests\Boutique;

use Illuminate\Foundation\Http\FormRequest;

class ShippingQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * El checkout Angular envía shipping_city / shipping_state / shipping_zip;
     * otros clientes pueden usar city / state / zip_code.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('shipping_zip') && ! $this->has('zip_code')) {
            $this->merge([
                'zip_code' => $this->input('shipping_zip'),
                'city' => $this->input('shipping_city'),
                'state' => $this->input('shipping_state'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'zip_code' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'shipping_address' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_state' => 'nullable|string',
            'shipping_zip' => 'nullable|string',
        ];
    }
}
