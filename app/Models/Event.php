<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

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
        'location',
        'feature_image',
        'start_date',
        'end_date',
        'user_id',
        'organizer_id',
        'participation_type',
        'capacity',
        'registration_deadline',
        'registration_status',
        'event_type',
        'online_url',
        'status',
        'category_id',
    ];

    protected $casts = [
        'id' => 'string',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'capacity' => 'integer',
        'status' => EventStatus::class,
        'participation_type' => ParticipationType::class,
        'registration_status' => RegistrationStatus::class,
        'event_type' => EventType::class,
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

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_favorites')
            ->withTimestamps();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if ( ! $user) {
            return false;
        }

        return $this->favorites()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Scope a query to only include events with a specific status.
     */
    public function scopeStatus(Builder $query, EventStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('start_date', '<', now());
    }

    public function featuredEvent(): HasOne
    {
        return $this->hasOne(FeaturedEvent::class);
    }

    /**
     * Check if the event is currently featured.
     */
    public function isFeatured(): bool
    {
        return $this->featuredEvent()
            ->active()
            ->exists();
    }

    protected function featureImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ( ! $this->feature_image) {
                    return 'https://picsum.photos/800/600';
                }

                // Check if the feature_image is already a URL
                if (filter_var($this->feature_image, FILTER_VALIDATE_URL)) {
                    return $this->feature_image;
                }

                // Otherwise, treat it as a local file path
                return Storage::disk('public')->url($this->feature_image);
            },
        );
    }
}
