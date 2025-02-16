<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FeaturedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'order',
        'active_from',
        'active_until',
    ];

    protected $casts = [
        'event_id' => 'string',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
        'order' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($query): void {
            $query->whereNull('active_until')
                ->orWhere('active_until', '>', now());
        })->where(function ($query): void {
            $query->whereNull('active_from')
                ->orWhere('active_from', '<=', now());
        });
    }
}
