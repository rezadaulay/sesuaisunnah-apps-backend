<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class EventGalleriesRelationManager extends RelationManager
{
    protected static string $relationship = 'gallery';

    protected static ?string $recordTitleAttribute = 'type';

    protected static ?string $title = 'Event Gallery';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Gallery Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->options([
                                        'photo' => 'Photo',
                                        'video' => 'Video',
                                        'document' => 'Document',
                                    ])
                                    ->required()
                                    ->default('photo'),
 
                                FileUpload::make('photo_url')
                                    ->label('File')
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/gif',
                                        'video/mp4',
                                        'video/mov',
                                        'video/avi',
                                        'application/pdf',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ])
                                    ->maxSize(10240) // 10MB
                                    ->directory('event-gallery')
                                    ->required(),
                            ]),
 
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Enter description for this gallery item'),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                ImageColumn::make('photo_url')
                    ->label('Preview')
                    ->circular()
                    ->size(60)
                    ->visibility(fn ($record) => $record->type === 'photo'),
 
                IconColumn::make('type')
                    ->label('Type')
                    ->icon(fn (string $state): string => match ($state) {
                        'photo' => 'heroicon-o-photo',
                        'video' => 'heroicon-o-video-camera',
                        'document' => 'heroicon-o-document',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'photo' => 'success',
                        'video' => 'warning',
                        'document' => 'info',
                        default => 'gray',
                    }),
 
                TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
 
                BadgeColumn::make('type')
                    ->colors([
                        'success' => 'photo',
                        'warning' => 'video',
                        'info' => 'document',
                    ]),
 
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'photo' => 'Photo',
                        'video' => 'Video',
                        'document' => 'Document',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Gallery Item'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
