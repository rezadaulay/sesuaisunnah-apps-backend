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
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Manajemen Konten';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Acara';

    protected static ?string $modelLabel = 'Acara';

    protected static ?string $pluralModelLabel = 'Acara';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make('Informasi Acara')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Acara')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan judul acara'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->placeholder('Masukkan deskripsi acara'),

                        FormGrid::make(2)
                            ->schema([
                                DatePicker::make('event_date')
                                    ->label('Tanggal Acara')
                                    ->required(),

                                TextInput::make('location')
                                    ->label('Lokasi')
                                    ->maxLength(255)
                                    ->placeholder('Masukkan lokasi acara'),
                            ]),

                        FileUpload::make('image')
                            ->label('Gambar Acara')
                            ->image()
                            ->imageEditor()
                            ->directory('events/images')
                            ->placeholder('Upload gambar acara'),
                    ])->columns(1),

                FormSection::make('Pengaturan')
                    ->schema([
                        FormGrid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->label('Acara Unggulan')
                                    ->default(false),
                            ]),

                    ])->columns(2)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->size(50),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(80)
                    ->toggleable(),

                TextColumn::make('event_date')
                    ->label('Tanggal Acara')
                    ->date()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star'),

                TextColumn::make('registrations_count')
                    ->label('Jumlah Peserta')
                    ->counts('registrations')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'Akan Datang',
                        'today' => 'Hari Ini',
                        'past' => 'Sudah Lewat',
                    ])
                    ->label('Status Acara'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                TernaryFilter::make('is_featured')
                    ->label('Acara Unggulan'),
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
            ->defaultSort('event_date', 'asc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Acara')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Judul Acara')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                TextEntry::make('event_date')
                                    ->label('Tanggal Acara')
                                    ->date()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('location')
                                    ->label('Lokasi')
                                    ->icon('heroicon-m-map-pin'),

                                TextEntry::make('description')
                                    ->label('Deskripsi')
                                    ->markdown()
                                    ->columnSpan(2),
                            ]),

                        ImageEntry::make('image')
                            ->label('Gambar Acara')
                            ->circular()
                            ->size(100),
                    ])->columns(2),

                Section::make('Status & Pengaturan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label('Status Aktif')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                                IconEntry::make('is_featured')
                                    ->label('Acara Unggulan')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-star')
                                    ->falseIcon('heroicon-o-star')
                                    ->trueColor('warning'),
                            ]),
                    ])->columns(2)->collapsible(),

                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Dibuat Oleh')
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])->columns(3)->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EventGalleriesRelationManager::class,
            RelationManagers\EventRegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'view' => Pages\ViewEvent::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['creator', 'registrations', 'gallery']);
    }
}
