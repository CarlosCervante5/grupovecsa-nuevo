<?php

namespace App\Http\Requests\Rewards;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRewardRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reward_uuid' => [
                'required',
                'string',
                'uuid'
            ]
        ];
    }

}