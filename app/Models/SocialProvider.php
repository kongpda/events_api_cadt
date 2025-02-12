<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SocialProvider extends Model
{
    protected $table = 'social_provider_user';

    protected $fillable = [
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

    protected $casts = [
        'provider_data' => 'array',
        'token_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
