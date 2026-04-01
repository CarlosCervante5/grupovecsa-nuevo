<?php

namespace App\Http\Requests\Boutique;

use Illuminate\Foundation\Http\FormRequest;

class CreateBoutiqueOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_method' => 'required|in:envio_domicilio,recoleccion_sucursal',
            'payment_method' => 'required|in:stripe,transferencia,sucursal',
            'shipping_name' => 'required_if:delivery_method,envio_domicilio|string',
            'shipping_address' => 'required_if:delivery_method,envio_domicilio|string',
            'shipping_city' => 'required_if:delivery_method,envio_domicilio|string',
            'shipping_state' => 'required_if:delivery_method,envio_domicilio|string',
            'shipping_zip' => 'required_if:delivery_method,envio_domicilio|string',
            'shipping_phone' => 'required_if:delivery_method,envio_domicilio|string',
            'dealership_uuid' => 'required_if:delivery_method,recoleccion_sucursal|string',
            'shipping_option' => 'nullable|array',
            'notes' => 'nullable|string',
        ];
    }
}
