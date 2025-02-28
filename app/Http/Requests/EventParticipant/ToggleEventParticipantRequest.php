<?php

declare(strict_types=1);

namespace App\Http\Requests\EventParticipant;

use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

final class ToggleEventParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', new Enum(ParticipationStatus::class)],
            'participation_type' => ['sometimes', new Enum(ParticipationType::class)],
            'ticket_type_id' => ['sometimes', 'exists:ticket_types,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.Illuminate\Validation\Rules\Enum' => 'The selected status is invalid. Valid options are: registered, attended, cancelled, waitlisted, declined.',
            'participation_type.Illuminate\Validation\Rules\Enum' => 'The selected participation type is invalid. Valid options are: paid, free.',
            'ticket_type_id.exists' => 'The selected ticket type does not exist.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Get the event from the route
        $event = $this->route('event');

        if ( ! $event) {
            return;
        }

        // Check if the event is active
        // if ( ! $event->is_active) {
        //     throw ValidationException::withMessages([
        //         'event' => ['This event is not active.'],
        //     ]);
        // }

        // Check if the event has already ended
        if ($event->end_date && $event->end_date < now()) {
            throw ValidationException::withMessages([
                'event' => ['This event has already ended.'],
            ]);
        }

        // Check if registration is closed
        if ($event->registration_deadline && $event->registration_deadline < now()) {
            throw ValidationException::withMessages([
                'event' => ['Registration for this event is closed.'],
            ]);
        }

        // Check if the event has reached maximum capacity
        // if ($event->max_participants && $event->participants()->count() >= $event->max_participants) {
        //     throw ValidationException::withMessages([
        //         'event' => ['This event has reached maximum capacity.'],
        //     ]);
        // }
    }
}
