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
                                DatePicker::make('start_date')
                                    ->label('Tanggal & Waktu Mulai')
                                    ->required()
                                    ->seconds(false),

                                DatePicker::make('end_date')
                                    ->label('Tanggal & Waktu Selesai')
                                    ->required()
                                    ->seconds(false),
                            ]),

                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->placeholder('Masukkan lokasi acara'),

                        FileUpload::make('featured_image')
                            ->label('Gambar Acara')
                            ->image()
                            ->imageEditor()
                            ->directory('events/images')
                            ->placeholder('Upload gambar acara'),
                    ])->columns(1),

                FormSection::make('Pengaturan Registrasi')
                    ->schema([
                        Toggle::make('requires_registration')
                            ->label('Menggunakan Form Registrasi')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, event ini tidak akan menampilkan form registrasi'),

                        Select::make('status')
                            ->label('Status Acara')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'registration_open' => 'Registrasi Dibuka',
                                'registration_closed' => 'Registrasi Ditutup',
                                'event_closed' => 'Event Ditutup',
                            ])
                            ->default('draft')
                            ->required()
                            ->visible(fn ($get) => $get('requires_registration')),

                        FormGrid::make(2)
                            ->schema([
                                DatePicker::make('registration_opens_at')
                                    ->label('Registrasi Dibuka Pada')
                                    ->seconds(false)
                                    ->placeholder('Opsional - Kosongkan jika tidak ada batasan')
                                    ->visible(fn ($get) => $get('requires_registration')),

                                DatePicker::make('registration_closes_at')
                                    ->label('Registrasi Ditutup Pada')
                                    ->seconds(false)
                                    ->placeholder('Opsional - Kosongkan jika tidak ada batasan')
                                    ->visible(fn ($get) => $get('requires_registration')),
                            ])
                            ->visible(fn ($get) => $get('requires_registration')),

                        FormGrid::make(2)
                            ->schema([
                                TextInput::make('max_participants')
                                    ->label('Kuota Maksimal')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Opsional - Kosongkan jika tidak terbatas')
                                    ->visible(fn ($get) => $get('requires_registration')),

                                TextInput::make('current_participants')
                                    ->label('Peserta Saat Ini')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->helperText('Otomatis terupdate saat ada registrasi')
                                    ->visible(fn ($get) => $get('requires_registration')),
                            ])
                            ->visible(fn ($get) => $get('requires_registration')),
                    ])->columns(1)->collapsible(),

                FormSection::make('Pengaturan Umum')
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

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'published',
                        'success' => 'registration_open',
                        'warning' => 'registration_closed',
                        'danger' => 'event_closed',
                    ]),

                TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('current_participants')
                    ->label('Peserta')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->max_participants) {
                            return "{$state}/{$record->max_participants}";
                        }
                        return $state;
                    }),

                TextColumn::make('registration_status_text')
                    ->label('Status Registrasi')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),

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

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'registration_open' => 'Registrasi Dibuka',
                        'registration_closed' => 'Registrasi Ditutup',
                        'event_closed' => 'Event Ditutup',
                    ])
                    ->label('Status Acara'),

                SelectFilter::make('registration_status')
                    ->options([
                        'open' => 'Bisa Daftar',
                        'closed' => 'Tidak Bisa Daftar',
                        'full' => 'Kuota Penuh',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'open' => $query->canAcceptRegistrations(),
                            'closed' => $query->where('status', '!=', 'registration_open'),
                            'full' => $query->whereRaw('current_participants >= max_participants'),
                            default => $query,
                        };
                    })
                    ->label('Status Registrasi'),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                TernaryFilter::make('is_featured')
                    ->label('Acara Unggulan'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Tables\Actions\Action::make('close_event')
                        ->label('Tutup Event')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tutup Event')
                        ->modalDescription('Apakah Anda yakin ingin menutup event ini? Event yang ditutup tidak bisa menerima registrasi baru.')
                        ->action(function (Event $record) {
                            $record->closeEvent();
                        })
                        ->visible(fn (Event $record) => $record->status !== 'event_closed'),

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
                                TextEntry::make('status')
                                    ->label('Status Acara')
                                    ->badge()
                                    ->color(fn ($record) => $record->status_color),

                                TextEntry::make('registration_status_text')
                                    ->label('Status Registrasi')
                                    ->badge()
                                    ->color(fn ($record) => $record->status_color),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('current_participants')
                                    ->label('Peserta Saat Ini')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('max_participants')
                                    ->label('Kuota Maksimal')
                                    ->badge()
                                    ->color('warning')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Terbatas'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('registration_opens_at')
                                    ->label('Registrasi Dibuka')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-calendar')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ditetapkan'),

                                TextEntry::make('registration_closes_at')
                                    ->label('Registrasi Ditutup')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-calendar')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ditetapkan'),
                            ]),

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

                        TextEntry::make('event_closed_at')
                            ->label('Event Ditutup Pada')
                            ->dateTime('M d, Y H:i')
                            ->icon('heroicon-m-clock')
                            ->formatStateUsing(fn ($state) => $state ?: 'Belum Ditutup'),
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
