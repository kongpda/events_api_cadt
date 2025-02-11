<?php

declare(strict_types=1);

namespace App\Http\Requests\EventParticipant;

use App\Enums\ParticipationStatus;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEventParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                'in:' . implode(',', [
                    ParticipationStatus::REGISTERED->value,
                    ParticipationStatus::ATTENDED->value,
                    ParticipationStatus::CANCELLED->value,
                    ParticipationStatus::WAITLISTED->value,
                    ParticipationStatus::DECLINED->value,
                ]),
            ],
            'check_in_time' => ['nullable', 'date'],
        ];
    }
}
