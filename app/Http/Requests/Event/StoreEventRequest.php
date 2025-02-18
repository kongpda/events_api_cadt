<?php

declare(strict_types=1);

namespace App\Http\Requests\Event;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use RuntimeException;

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
            'location' => ['required', 'string', 'max:255'],
            'feature_image' => ['image', 'required', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'category_id' => ['required', 'exists:categories,id'],
            'organizer_id' => ['nullable', 'string', 'exists:organizers,id'],
            'participation_type' => ['required', 'string', Rule::enum(ParticipationType::class)],
            'capacity' => ['required', 'integer', 'min:0'],
            'registration_deadline' => ['nullable', 'date', 'before:start_date'],
            'registration_status' => ['required', 'string', Rule::enum(RegistrationStatus::class)],
            'event_type' => ['required', 'string', Rule::enum(EventType::class)],
            'online_url' => ['required_if:event_type,online,hybrid', 'nullable', 'url'],
            'status' => ['sometimes', 'string', Rule::enum(EventStatus::class)],
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

        if ($this->missing('status')) {
            $this->merge([
                'status' => EventStatus::DRAFT->value,
            ]);
        }

        if ($this->missing('capacity')) {
            $this->merge([
                'capacity' => 0,
            ]);
        }

        $organizerId = auth()->user()->organizer?->id;
        if ( ! $organizerId) {
            throw new RuntimeException('User must be associated with an organizer to create events.');
        }

        $this->merge([
            'organizer_id' => $organizerId,
            'user_id' => auth()->id(),
        ]);
    }
}
