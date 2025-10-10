<?php

namespace App\Http\Requests\Blogs;

use Illuminate\Foundation\Http\FormRequest;

class SearchPostsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'keyword' => 'sometimes|string|nullable',
            'order_by' => 'sometimes|string|nullable',
            'paginate' => 'sometimes|integer|min:1',
        ];
    }

    protected function prepareForValidation()
    {
        $stringToArray = function ($string) {
            return $string ? explode(',', $string) : [];
        };

        $this->merge([

            'keyword' => $this->input('keyword', ''),

            'order_by' => $this->input('order_by', ''),

            'paginate' => $this->input('paginate', 15),
        ]);
    }
}