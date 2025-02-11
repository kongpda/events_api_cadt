<?php

declare(strict_types=1);

namespace App\Http\Requests\Organizer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateOrganizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('organizer'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('organizers')->ignore($this->route('organizer')),
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url'],
            'social_media' => ['nullable', 'url'],
            'logo' => ['nullable', 'string'],
        ];
    }
}
