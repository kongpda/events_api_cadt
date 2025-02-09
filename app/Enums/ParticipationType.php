<?php

declare(strict_types=1);

namespace App\Enums;

enum ParticipationType: string
{
    case TICKET = 'ticket_holder';
    case FREE = 'free_participant';
}
