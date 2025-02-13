<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class SocialAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'photo_url' => ['nullable', 'url'],
            'provider_user_id' => ['required', 'string'],
            'provider' => ['required', 'string', 'in:google,facebook,github'], // This will map to provider_slug
            'access_token' => ['required', 'string'], // This will map to token
            'device_name' => ['required', 'string'],
            'nickname' => ['nullable', 'string'], // Add this to match DB schema
            'refresh_token' => ['nullable', 'string'], // Add this to match DB schema
            'token_expires_at' => ['nullable', 'date'], // Add this to match DB schema
            'provider_data' => ['nullable', 'array'], // Add this to match DB schema
        ];
    }

    public function messages(): array
    {
        return [
            'access_token.required' => 'Social provider access token is required',
            'device_name.required' => 'Device name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'name.required' => 'Name is required',
            'photo_url.url' => 'Please provide a valid URL for the photo',
            'provider.required' => 'Social provider is required',
            'provider.in' => 'Invalid social provider',
            'provider_user_id.required' => 'Provider User ID is required',
            // Add new validation messages if needed
        ];
    }
}
