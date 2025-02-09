<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
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
        'name',
        'slug',
        'description',
        'is_active',
        'position',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * The Events that belong to the tag.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
