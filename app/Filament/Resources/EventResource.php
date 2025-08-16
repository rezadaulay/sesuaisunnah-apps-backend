<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Model;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Event Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Event Information')
                    ->schema([
                        TextInput::make('title')
                            ->label('Event Title')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Enter event title'),
                        
                        RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('Enter event description'),
                        
                        DatePicker::make('event_date')
                            ->label('Event Date')
                            ->required()
                            ->minDate(now())
                            ->displayFormat('d/m/Y'),
                        
                        FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->imageEditor()
                            ->directory('events/featured')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Documentation')
                    ->schema([
                        RichEditor::make('documentation_desc')
                            ->label('Documentation Description')
                            ->columnSpanFull()
                            ->placeholder('Enter event documentation description (can be filled after event)'),
                    ])->collapsible(),

                Forms\Components\Section::make('Creator Information')
                    ->schema([
                        Select::make('created_by')
                            ->label('Created By')
                            ->relationship('creator', 'name')
                            ->required()
                            ->disabled(),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular()
                    ->size(50),
                
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('event_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color(fn (Model $record): string => match (true) {
                        $record->is_today => 'success',
                        $record->is_upcoming => 'warning',
                        $record->is_past => 'gray',
                        default => 'primary',
                    }),
                
                TextColumn::make('participants_count')
                    ->label('Participants')
                    ->counts('registrations')
                    ->sortable(),
                
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_status')
                    ->label('Event Status')
                    ->options([
                        'upcoming' => 'Upcoming',
                        'today' => 'Today',
                        'past' => 'Past',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'upcoming' => $query->where('event_date', '>', now()),
                            'today' => $query->whereDate('event_date', now()),
                            'past' => $query->where('event_date', '<', now()),
                            default => $query,
                        };
                    }),
                
                SelectFilter::make('created_by')
                    ->label('Created By')
                    ->relationship('creator', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('event_date', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\RegistrationsRelationManager::class,
            // RelationManagers\GalleryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
