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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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
                Section::make('Event Info')
                    ->description('Provide event information')
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
                        Select::make('venue_id')
                            ->relationship('venue', 'name')
                            ->required(),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->required(),
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->required()
                            ->default('draft'),
                    ])->columns(2),

                Section::make('Content')
                    ->schema([
                        Builder::make('content')
                            ->label('Event Content')
                            ->addActionLabel('Add Content Block')
                            ->blocks([
                                Builder\Block::make('text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('content')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Builder\Block::make('image_gallery')
                                    ->schema([
                                        FileUpload::make('images')
                                            ->multiple()
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Builder\Block::make('video')
                                    ->schema([
                                        FileUpload::make('video')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Section::make('Event Dates')
                            ->schema([
                                Repeater::make('event_date')
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('start_date')
                                            ->required(),
                                        Forms\Components\DateTimePicker::make('end_date')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Add Date')
                                    ->collapsible()
                                    ->cloneable(),
                            ]),
                    ])->columnSpan(2),

                Section::make('Media & Actions')
                    ->schema([
                        FileUpload::make('feature_image')
                            ->directory('events/features')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9'),

                        Builder::make('action_content')
                            ->label('Action Buttons')
                            ->blocks([
                                Builder\Block::make('button')
                                    ->schema([
                                        TextInput::make('label')
                                            ->required(),
                                        TextInput::make('url')
                                            ->required()
                                            ->url(),
                                        Select::make('type')
                                            ->options([
                                                'primary' => 'Primary',
                                                'secondary' => 'Secondary',
                                            ])
                                            ->default('primary'),
                                    ]),
                                Builder\Block::make('download')
                                    ->schema([
                                        TextInput::make('label')
                                            ->required(),
                                        FileUpload::make('file')
                                            ->required(),
                                    ]),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('venue.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'danger',
                    }),
                Tables\Columns\ImageColumn::make('feature_image'),
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\SelectFilter::make('venue')
                    ->relationship('venue', 'name'),
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
        return [];
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
