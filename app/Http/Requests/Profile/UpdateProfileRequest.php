<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'date'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'bio' => ['sometimes', 'string'],
            'address' => ['sometimes', 'string'],
            'social_links' => ['sometimes', 'array'],
        ];
    }
}
