<?php

declare(strict_types=1);

namespace App\Filament\Resources\VenueResource\Pages;

use App\Filament\Resources\VenueResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateVenue extends CreateRecord
{
    protected static string $resource = VenueResource::class;
}
