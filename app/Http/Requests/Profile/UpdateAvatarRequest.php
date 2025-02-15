<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // 2MB max
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image to upload.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be a JPEG, PNG or JPG file.',
            'avatar.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
