<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Event extends Model
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
        'title',
        'slug',
        'description',
        'address',
        'feature_image',
        'start_date',
        'end_date',
        'category_id',
        'user_id',
        'organizer_id',
        'participation_type',
        'capacity',
        'registration_deadline',
        'registration_status',
        'event_type',
        'online_url',
    ];

    protected $casts = [
        'id' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'capacity' => 'integer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected array $participationTypes = [
        'paid' => 'Paid',
        'free' => 'Free',
    ];

    protected array $registrationStatuses = [
        'open' => 'Open',
        'closed' => 'Closed',
        'full' => 'Full',
    ];

    protected array $eventTypes = [
        'in_person' => 'In Person',
        'online' => 'Online',
        'hybrid' => 'Hybrid',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    /**
     * Scope a query to only include events with a specific status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the available participation types.
     *
     * @return array<string, string>
     */
    public function getParticipationTypes(): array
    {
        return $this->participationTypes;
    }

    /**
     * Get the available registration statuses.
     *
     * @return array<string, string>
     */
    public function getRegistrationStatuses(): array
    {
        return $this->registrationStatuses;
    }

    /**
     * Get the available event types.
     *
     * @return array<string, string>
     */
    public function getEventTypes(): array
    {
        return $this->eventTypes;
    }
}
