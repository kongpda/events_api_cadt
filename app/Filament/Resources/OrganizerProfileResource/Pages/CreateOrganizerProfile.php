<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizerProfileResource\Pages;

use App\Filament\Resources\OrganizerProfileResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateOrganizerProfile extends CreateRecord
{
    protected static string $resource = OrganizerProfileResource::class;
}
