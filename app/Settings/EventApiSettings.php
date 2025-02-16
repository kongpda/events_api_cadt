<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

final class EventApiSettings extends Settings
{
    public int $event_fetch_count;

    public int $feature_event_count;

    public static function group(): string
    {
        return 'EventApiSettings';
    }
}
