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
            'provider_id' => ['required', 'string'],
            'provider' => ['required', 'string', 'in:google,facebook,github'], // add supported providers
            'access_token' => ['required', 'string'],
            'device_name' => ['required', 'string'],
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
            'provider_id.required' => 'Provider ID is required',
        ];
    }
}
