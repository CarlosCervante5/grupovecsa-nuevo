<?php

namespace App\Http\Requests\Boutique;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBoutiqueOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uuid' => 'required|string',
            'status' => 'required|in:pendiente,pagado,en_preparacion,enviado,entregado,cancelado',
        ];
    }
}
