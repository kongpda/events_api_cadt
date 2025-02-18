<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case IN_PERSON = 'in_person';
    case ONLINE = 'online';
    case HYBRID = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::IN_PERSON => 'In Person',
            self::ONLINE => 'Online',
            self::HYBRID => 'Hybrid',
        };
    }
}
