<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class GoogleAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_token' => ['required', 'string'],
            'device_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'name' => ['required', 'string'],
            'photo_url' => ['nullable', 'string', 'url'],
            'provider_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'access_token.required' => 'Google access token is required',
            'device_name.required' => 'Device name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'name.required' => 'Name is required',
            'photo_url.url' => 'Please provide a valid URL for the photo',
            'provider_id.required' => 'Google provider ID is required',
        ];
    }
}
