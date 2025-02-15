<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthProvider: string
{
    case EMAIL = 'email';
    case GOOGLE = 'google';
    case FACEBOOK = 'facebook';
    // Add other providers as needed

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::GOOGLE => 'Google',
            self::FACEBOOK => 'Facebook',
        };
    }
}
