<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventRegistrationResource\Pages;
use App\Models\EventRegistration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Event';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Registrasi Event';

    protected static ?string $modelLabel = 'Registrasi Event';

    protected static ?string $pluralModelLabel = 'Registrasi Event';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Event')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Event')
                            ->relationship('event', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('occupation')
                            ->label('Pekerjaan/Kegiatan')
                            ->maxLength(255)
                            ->placeholder('Contoh: Mahasiswa, Karyawan, Wiraswasta, dll')
                            ->helperText('Opsional'),

                        Forms\Components\Select::make('referral_source')
                            ->label('Sumber Referral')
                            ->options([
                                'website' => 'Website',
                                'instagram' => 'Instagram',
                                'facebook' => 'Facebook',
                                'whatsapp' => 'WhatsApp',
                                'friend' => 'Teman/Keluarga',
                                'email' => 'Email',
                                'other' => 'Lainnya',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'registered' => 'Terdaftar',
                                'confirmed' => 'Dikonfirmasi',
                                'cancelled' => 'Dibatalkan',
                                'attended' => 'Hadir',
                                'no_show' => 'Tidak Hadir',
                            ])
                            ->default('registered')
                            ->required(),

                        Forms\Components\DateTimePicker::make('registered_at')
                            ->label('Tanggal Registrasi')
                            ->default(now())
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('gender')
                    ->label('Gender')
                    ->colors([
                        'primary' => 'male',
                        'success' => 'female',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    }),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('occupation')
                    ->label('Pekerjaan/Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('referral_source')
                    ->label('Referral')
                    ->colors([
                        'info' => 'website',
                        'warning' => 'instagram',
                        'primary' => 'facebook',
                        'success' => 'whatsapp',
                        'secondary' => 'friend',
                        'danger' => 'email',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn (EventRegistration $record): string => $record->referral_source_label),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'registered',
                        'info' => 'confirmed',
                        'danger' => 'cancelled',
                        'success' => 'attended',
                        'warning' => 'no_show',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registered' => 'Terdaftar',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        'attended' => 'Hadir',
                        'no_show' => 'Tidak Hadir',
                    }),

                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Tanggal Registrasi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),

                SelectFilter::make('referral_source')
                    ->label('Sumber Referral')
                    ->options([
                        'website' => 'Website',
                        'instagram' => 'Instagram',
                        'facebook' => 'Facebook',
                        'whatsapp' => 'WhatsApp',
                        'friend' => 'Teman/Keluarga',
                        'email' => 'Email',
                        'other' => 'Lainnya',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'registered' => 'Terdaftar',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        'attended' => 'Hadir',
                        'no_show' => 'Tidak Hadir',
                    ]),
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
            ->defaultSort('registered_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Umum')
                    ->schema([
                        TextEntry::make('event.title')
                            ->label('Event'),
                        TextEntry::make('user.name')
                            ->label('User'),
                        TextEntry::make('phone')
                            ->label('Telepon'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('occupation')
                            ->label('Pekerjaan/Kegiatan'),
                        TextEntry::make('referral_source_label')
                            ->label('Sumber Referral'),
                        TextEntry::make('status_label')
                            ->label('Status'),
                        TextEntry::make('registered_at')
                            ->label('Tanggal Registrasi'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventRegistrations::route('/'),
            'create' => Pages\CreateEventRegistration::route('/create'),
            'edit' => Pages\EditEventRegistration::route('/{record}/edit'),
            'view' => Pages\ViewEventRegistration::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['event:id,title', 'user:id,name']);
    }
}
