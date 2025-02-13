<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SocialProvider extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'social_provider_user';

    /**
     * The primary keys for the model.
     *
     * @var array<string>
     */
    protected $primaryKey = ['user_id', 'provider_slug'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'provider_slug',
        'provider_user_id',
        'nickname',
        'name',
        'email',
        'avatar',
        'provider_data',
        'token',
        'refresh_token',
        'token_expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'provider_data' => 'array',
        'token_expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the social provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
