<?php

declare(strict_types=1);

namespace App\Enums;

enum ParticipationStatus: string
{
    case REGISTERED = 'registered';
    case ATTENDED = 'attended';
    case CANCELLED = 'cancelled';
    case WAITLISTED = 'waitlisted';
    case DECLINED = 'declined';
}
