<?php

declare(strict_types=1);

namespace App\Http\Requests\EventParticipant;

use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class StoreEventParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'user_id' => ['required', 'exists:users,id'],
            'status' => ['required', new Enum(ParticipationStatus::class)],
            'participation_type' => ['required', new Enum(ParticipationType::class)],
            'ticket_id' => ['nullable', 'exists:tickets,id'],
            'check_in_time' => ['nullable', 'date'],
            'joined_at' => ['required', 'date'],
        ];
    }
}
