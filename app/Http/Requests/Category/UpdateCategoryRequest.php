<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by the controller policy
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                Rule::unique('categories')->ignore($this->category),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'position' => ['integer'],
        ];
    }
}
