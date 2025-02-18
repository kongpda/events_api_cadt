<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeaturedEventResource\Pages;

use App\Filament\Resources\FeaturedEventResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateFeaturedEvent extends CreateRecord
{
    protected static string $resource = FeaturedEventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
