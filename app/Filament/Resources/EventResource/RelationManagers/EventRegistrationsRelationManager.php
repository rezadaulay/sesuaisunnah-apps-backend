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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class EventRegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $recordTitleAttribute = 'user.name';

    protected static ?string $title = 'Event Registrations';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Registrasi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('gender')
                                    ->label('Gender')
                                    ->options([
                                        'male' => 'Laki-laki',
                                        'female' => 'Perempuan',
                                    ])
                                    ->required(),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('occupation')
                                    ->label('Pekerjaan/Kegiatan')
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Mahasiswa, Karyawan, Wiraswasta, dll')
                                    ->helperText('Opsional'),

                                Select::make('referral_source')
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
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('status')
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

                                DatePicker::make('registered_at')
                                    ->label('Tanggal Registrasi')
                                    ->default(now())
                                    ->required(),
                            ]),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Peserta')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('occupation')
                    ->label('Pekerjaan/Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),

                BadgeColumn::make('gender')
                    ->label('Gender')
                    ->colors([
                        'primary' => 'male',
                        'secondary' => 'female',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    }),

                BadgeColumn::make('status')
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

                TextColumn::make('registered_at')
                    ->label('Tanggal Registrasi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                BadgeColumn::make('referral_source')
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
                    ->formatStateUsing(fn ($record) => $record->referral_source_label),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'registered' => 'Terdaftar',
                        'confirmed' => 'Dikonfirmasi',
                        'cancelled' => 'Dibatalkan',
                        'attended' => 'Hadir',
                        'no_show' => 'Tidak Hadir',
                    ]),

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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Registration'),
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
