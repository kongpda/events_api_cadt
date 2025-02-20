<?php

declare(strict_types=1);

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

final class StoreOrganizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:organizers'],
            'phone' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'social_media' => ['nullable', 'url'],
            'logo' => ['nullable', 'string'],
            'user_id' => ['required', 'exists:users,id'],
            'slug' => ['nullable', 'string', 'unique:organizers'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The organizer name is required.',
            'name.max' => 'The organizer name cannot exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered for another organizer.',
            'website.url' => 'Please provide a valid website URL.',
            'social_media.url' => 'Please provide a valid social media URL.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'organizer name',
            'email' => 'email address',
            'phone' => 'phone number',
            'description' => 'description',
            'address' => 'address',
            'website' => 'website URL',
            'social_media' => 'social media URL',
            'logo' => 'logo',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $userId = auth()->id();

        if ( ! $userId) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.');
        }

        $this->merge([
            'user_id' => $userId,
            'slug' => $this->input('slug') ?? Str::slug($this->input('name')),
        ]);
    }
}
