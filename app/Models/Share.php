<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ShareFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Share extends Model
{
    /** @use HasFactory<ShareFactory> */
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
        'user_id',
        'event_id',
        'platform',
        'share_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'user_id' => 'string',
            'event_id' => 'string',
            'platform' => 'array',
            'share_url' => 'array',
        ];
    }
}
