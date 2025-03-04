<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Ticket extends Model
{
    use HasFactory;
    use HasUlids;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'user_id',
        'ticket_type_id',
        'status',
        'purchase_date',
        'price',
        'token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'token',
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

    /**
     * Get the QR code data for this ticket.
     * This data will be used by the client to generate the QR code.
     */
    public function getQrCodeData(): array
    {
        return [
            'ticket_id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'verification' => $this->getVerificationHash(),
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        self::creating(function (Ticket $ticket): void {
            // Generate a unique token for QR code if not set
            if (empty($ticket->token)) {
                $ticket->token = Str::random(32);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'purchase_date' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Generate a verification hash for the ticket.
     * This helps prevent ticket forgery.
     */
    protected function getVerificationHash(): string
    {
        return hash_hmac('sha256', $this->id . $this->event_id . $this->user_id, $this->token);
    }
}
