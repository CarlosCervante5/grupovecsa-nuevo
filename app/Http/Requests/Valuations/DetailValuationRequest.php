<?php

namespace App\Http\Requests\Valuations;

use Illuminate\Foundation\Http\FormRequest;

class DetailValuationRequest extends FormRequest
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
            'book_trade_in_offer' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'book_sale_price' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'intellimotors_trade_in_offer' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'intellimotors_sale_price' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'labor_cost' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'spare_parts_cost' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'body_work_painting_cost' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'final_offer' => 'sometimes|nullable|numeric|min:0|max:9999999.99',
            'status' => 'sometimes|required|in:to_appraise,on_progress,appraised,on_hold,acquired,to_acquire',
            'trade_in' => 'sometimes|nullable|in:yes,no',
            'comments' => 'sometimes|nullable|string|max:65535',
        ];
    }
}