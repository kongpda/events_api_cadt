<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('EventApiSettings.event_fetch_count', 10);
        $this->migrator->add('EventApiSettings.feature_event_count', 3);
    }
};
