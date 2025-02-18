<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeaturedEventResource\Pages;

use App\Filament\Resources\FeaturedEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditFeaturedEvent extends EditRecord
{
    protected static string $resource = FeaturedEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
