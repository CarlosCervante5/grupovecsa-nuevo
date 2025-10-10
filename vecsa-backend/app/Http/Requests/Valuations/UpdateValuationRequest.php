<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateValuationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'valuation_uuid' => [
                'required',
                'string',
                'uuid',
            ],
            'seller_uuid' => [
                'nullable',
                'string',
                'uuid',
            ],
            'book_trade_in_offer' => 'nullable|numeric|min:0|max:99999999.99',
            'book_sale_price' => 'nullable|numeric|min:0|max:99999999.99',
            'intellimotors_trade_in_offer' => 'nullable|numeric|min:0|max:99999999.99',
            'intellimotors_sale_price' => 'nullable|numeric|min:0|max:99999999.99',
            'labor_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'spare_parts_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'body_work_painting_cost' => 'nullable|numeric|min:0|max:99999999.99',
            'estimated_total' => 'nullable|numeric|min:0|max:99999999.99',
            'trade_in_final' => 'nullable|numeric|min:0|max:99999999.99',
            'final_offer' => 'nullable|numeric|min:0|max:99999999.99',
            'status' => 'required|string',
            'take_type' => 'nullable|string',
            'comments' => 'nullable|string|max:65535',
        ];
    }
}