<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedEventResource\Pages\CreateFeaturedEvent;
use App\Filament\Resources\FeaturedEventResource\Pages\EditFeaturedEvent;
use App\Filament\Resources\FeaturedEventResource\Pages\ListFeaturedEvents;
use App\Models\FeaturedEvent;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class FeaturedEventResource extends Resource
{
    protected static ?string $model = FeaturedEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Events';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('event_id')
                    ->relationship(
                        name: 'event',
                        titleAttribute: 'title',
                        modifyQueryUsing: fn (Builder $query) => $query->whereDoesntHave('featuredEvent')
                            ->orWhere('id', fn ($q) => $q->select('event_id')->from('featured_events')->where('id', request()->route('record'))),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('order')
                    ->integer()
                    ->default(0)
                    ->required(),

                DateTimePicker::make('active_from')
                    ->label('Active From')
                    ->nullable(),

                DateTimePicker::make('active_until')
                    ->label('Active Until')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->columns([
                TextColumn::make('event.title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order')
                    ->sortable(),
                TextColumn::make('active_from')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('active_until')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Create By'),
            ])
            ->defaultSort('order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeaturedEvents::route('/'),
            'create' => CreateFeaturedEvent::route('/create'),
            'edit' => EditFeaturedEvent::route('/{record}/edit'),
        ];
    }
}
