<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';
    case POSTPONED = 'postponed';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::CANCELLED => 'Cancelled',
            self::POSTPONED => 'Postponed',
            self::COMPLETED => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'green',
            self::CANCELLED => 'red',
            self::POSTPONED => 'yellow',
            self::COMPLETED => 'blue',
        };
    }
}
