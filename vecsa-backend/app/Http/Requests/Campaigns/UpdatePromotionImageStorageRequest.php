<?php

namespace App\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionImageStorageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'campaign_uuid'=> [
                'required',
                'string',
                'uuid',
            ]
        ];
    }

}