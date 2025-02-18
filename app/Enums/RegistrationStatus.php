<?php

declare(strict_types=1);

namespace App\Enums;

enum RegistrationStatus: string
{
    case OPEN = 'open';
    case CLOSED = 'closed';
    case FULL = 'full';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::CLOSED => 'Closed',
            self::FULL => 'Full',
        };
    }
}
