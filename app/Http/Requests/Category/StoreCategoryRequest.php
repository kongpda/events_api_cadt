<?php

declare(strict_types=1);

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by the controller policy
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', 'unique:categories'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'position' => ['integer'],
        ];
    }
}
