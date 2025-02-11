<?php

declare(strict_types=1);

namespace App\Http\Requests\TicketType;

use Illuminate\Foundation\Http\FormRequest;

final class StoreTicketTypeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'ulid', 'exists:events,id'],
            'user_id' => ['required', 'ulid', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:draft,published,sold_out'],
        ];
    }
}
