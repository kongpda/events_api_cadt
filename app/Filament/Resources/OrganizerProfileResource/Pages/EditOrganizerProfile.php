<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizerProfileResource\Pages;

use App\Filament\Resources\OrganizerProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditOrganizerProfile extends EditRecord
{
    protected static string $resource = OrganizerProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
