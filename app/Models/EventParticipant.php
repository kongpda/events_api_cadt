<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ParticipationStatus;
use App\Enums\ParticipationType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EventParticipant extends Model
{
    /** @use HasFactory<\Database\Factories\EventParticipantFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'participation_type',
        'ticket_type_id',
        'check_in_time',
        'joined_at',
    ];

    protected $casts = [
        'status' => ParticipationStatus::class,
        'participation_type' => ParticipationType::class,
        'check_in_time' => 'datetime',
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }
}
