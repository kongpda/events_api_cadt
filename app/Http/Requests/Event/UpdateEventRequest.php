<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('events')->ignore($this->event)],
            'description' => ['sometimes', 'required', 'string', 'max:65535'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'feature_image' => ['nullable', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date', 'after:start_date'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'organizer_id' => ['sometimes', 'required', 'ulid', 'exists:organizers,id'],
            'participation_type' => ['sometimes', 'required', 'string', 'in:paid,free'],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            'registration_deadline' => ['sometimes', 'required', 'date', 'before:end_date'],
            'registration_status' => ['sometimes', 'required', 'string', 'in:open,closed,full'],
            'event_type' => ['sometimes', 'required', 'string', 'in:in_person,online,hybrid'],
            'online_url' => ['sometimes', 'required_if:event_type,online,hybrid', 'nullable', 'url'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('title') && $this->missing('slug')) {
            $this->merge([
                'slug' => str()->slug($this->input('title')),
            ]);
        }
    }
}
