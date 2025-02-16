<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\EventApiSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

final class ManageEventApi extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = EventApiSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('event_fetch_count')
                    ->label('Event Fetch Count')
                    ->default(10)
                    ->required(),
                TextInput::make('feature_event_count')
                    ->label('Feature Event Count')
                    ->default(5)
                    ->required(),
            ]);
    }
}
