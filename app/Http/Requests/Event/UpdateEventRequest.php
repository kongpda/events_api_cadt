<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
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
            'location' => ['sometimes', 'required', 'string', 'max:255'],
            'feature_image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'participation_type' => ['sometimes', 'required', 'string', Rule::enum(ParticipationType::class)],
            /** @ignoreParam */
            'capacity' => ['sometimes', 'required', 'integer', 'min:1'],
            /** @ignoreParam */
            'registration_deadline' => ['nullable', 'date', 'before:start_date'],
            'registration_status' => ['sometimes', 'required', 'string', Rule::enum(RegistrationStatus::class)],
            'event_type' => ['sometimes', 'required', 'string', Rule::enum(EventType::class)],
            /** @ignoreParam */
            'online_url' => ['sometimes', 'required_if:event_type,online,hybrid', 'nullable', 'url'],
            /** @ignoreParam */
            'status' => ['sometimes', 'string', Rule::enum(EventStatus::class)],
            /** @ignoreParam */
            'tags' => ['sometimes', 'array'],
            /** @ignoreParam */
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

        // Remove organizer_id if it's in the request to prevent changes
        $this->request->remove('organizer_id');
    }
}
