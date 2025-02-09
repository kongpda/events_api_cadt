<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Share extends Model
{
    /** @use HasFactory<\Database\Factories\ShareFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'event_id',
        'platform',
        'share_url',
    ];

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'event_id' => 'string',
        'platform' => 'array',
        'share_url' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
