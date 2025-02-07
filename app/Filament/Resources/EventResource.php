<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

final class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Event Info Section ')
                    ->description('this where you provide event information')
                    ->schema([
                        TextInput::make('title')
                            ->live()
                            ->required(),
                        TextInput::make('slug')
                            ->required(),
                        TextInput::make('user_id')
                            ->required()
                            ->hidden()
                            ->numeric(),
                        Forms\Components\Select::make('venue_id')
                            ->relationship('venue', 'name')
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                        TextInput::make('tag_id')
                            ->required()
                            ->numeric(),
                        Forms\Components\Textarea::make('description'),
                    ])->columns(),
                Section::make('Content Section ')
                    ->description('this where you provide event information')
                    ->schema([
                        Builder::make('content')
                            ->label('')
                            ->addActionLabel('Add Different Content')
                            ->blocks([
                                Builder\Block::make('Add Content')
                                    ->schema([
                                        Forms\Components\RichEditor::make('description')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Builder\Block::make('image_gallery')
                                    ->label('Image Gallery')
                                    ->schema([
                                        FileUpload::make('image_content')
                                            ->label('Image Gallery')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Builder\Block::make('video_content')
                                    ->label('Add Video')
                                    ->schema([
                                        FileUpload::make('video_content')
                                            ->label('Upload Video')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Section::make('Event Date')
                            ->schema([
                                Repeater::make('event_date')
                                    ->label('')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('start_date')
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y H:i')
                                            ->default(now())
                                            ->native(false)
                                            ->required(),
                                        Forms\Components\DateTimePicker::make('end_date')
                                            ->format('d/m/Y')
                                            ->displayFormat('d/m/Y H:i')
                                            ->default(now())
                                            ->native(false)
                                            ->required(),
                                    ])
                                    ->itemLabel(fn (array $state): string => formatEventDateTimeSchedule($state['start_date'], $state['end_date']))
                                    ->columns()
                                    ->addActionLabel('Add Event Date')
                                    ->collapsible()
                                    ->cloneable()
                                    ->reorderableWithButtons(),
                            ]),
                        Section::make('Simple Ticket Section ')
                            ->description('this where you can sell simple tickets (where you dont need to provide user select seats.)')
                            ->schema([
                                Toggle::make('is_sell_tickets')
                                    ->label('Sell Tickets?')
                                    ->live(),
                                Repeater::make('Event Date')
                                    ->label('')
                                    ->schema([
                                        TextInput::make('title'),
                                        TextInput::make('kind'),
                                        TextInput::make('price'),
                                    ])->columns(3)
                                    //                                    ->itemLabel(function (array $state): ?string {
                                    //                                        return formatEventDateTimeSchedule($state['start_time'], $state['end_time']);
                                    //                                    })
                                    ->addActionLabel('Add Event Tickets')
                                    ->collapsible()
                                    ->cloneable()
                                    ->hidden(fn (Get $get): bool => ! $get('is_sell_tickets')),
                            ]),
                    ])->columnSpan(2),

                Section::make('Information Section')
                    ->schema([
                        FileUpload::make('feature_image')
                            ->directory('upload/events')
                            ->label('Featured Image')
                            ->image(),
                        Section::make('Action Buttons Section ')
                            ->description('this where user can download or click apply for your event')
                            ->schema([
                                Builder::make('action_content')
                                    ->label('')
                                    ->blocks([
                                        Builder\Block::make('Link_Button')
                                            ->schema([
                                                TextInput::make('label'),
                                                TextInput::make('url'),
                                            ]),
                                        Builder\Block::make('Download Button')
                                            ->schema([
                                                TextInput::make('label'),
                                                FileUpload::make('url')
                                                    ->label('attachment')
                                                    ->required(),
                                            ]),
                                    ])
                                    ->addActionLabel('Add Action Button'),
                            ]),
                        Section::make('Events Locations')
                            ->description('this where you can sell simple tickets (where you dont need to provide user select seats.)')
                            ->schema([
                                TextInput::make('maps url')
                                    ->label(''),
                            ]),
                    ])
                    ->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('venue_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tickets')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
