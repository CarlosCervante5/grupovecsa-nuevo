<?php

namespace App\Http\Requests\Boutique;

use App\Models\Boutique\BoutiqueCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoutiqueCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $table = (new BoutiqueCategory)->getTable();

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
            'parent_uuid' => ['nullable', 'string', Rule::exists($table, 'uuid')],
        ];
    }
}
