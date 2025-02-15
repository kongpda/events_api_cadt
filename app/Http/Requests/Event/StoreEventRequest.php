<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:events,slug'],
            'description' => ['required', 'string', 'max:65535'],
            'address' => ['required', 'string', 'max:255'],
            'feature_image' => ['image', 'required', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'category_id' => ['required', 'exists:categories,id'],
            'organizer_id' => ['required', 'ulid', 'exists:organizers,id'],
            'participation_type' => ['required', 'string', 'in:paid,free'],
            'capacity' => ['required', 'integer', 'min:1'],
            'registration_deadline' => ['required', 'date', 'before:end_date'],
            'registration_status' => ['required', 'string', 'in:open,closed,full'],
            'event_type' => ['required', 'string', 'in:in_person,online,hybrid'],
            'online_url' => ['required_if:event_type,online,hybrid', 'nullable', 'url'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->missing('slug') && $this->filled('title')) {
            $this->merge([
                'slug' => str()->slug($this->input('title')),
            ]);
        }
    }
}
