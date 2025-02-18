<?php

declare(strict_types=1);

namespace App\Enums;

enum ParticipationType: string
{
    case PAID = 'paid';
    case FREE = 'free';

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::FREE => 'Free',
        };
    }
}
