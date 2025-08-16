<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Manajemen Pengguna';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama lengkap'),
 
                        TextInput::make('email')
                            ->label('Alamat Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Masukkan alamat email')
                            ->unique(ignoreRecord: true),
 
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Masukkan nomor telepon')
                            ->unique(ignoreRecord: true),
 
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->required(),
 
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])->columns(2),

                FormSection::make('Peran & Izin')
                    ->schema([
                        Select::make('roles')
                            ->label('Peran')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable(),
 
                        Select::make('permissions')
                            ->label('Izin')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->searchable(),
                    ])->columns(2),

                FormSection::make('Informasi Tambahan')
                    ->schema([
                        TextInput::make('email_verified_at')
                            ->label('Email Diverifikasi Pada')
                            ->disabled()
                            ->dehydrated(false),
 
                        TextInput::make('created_at')
                            ->label('Dibuat Pada')
                            ->disabled()
                            ->dehydrated(false),
 
                        TextInput::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(3)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
 
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
 
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
 
                BadgeColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->colors([
                        'primary' => 'male',
                        'secondary' => 'female',
                    ]),
 
                IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
 
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->separator(',')
                    ->color('info'),
 
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
 
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
 
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
 
                SelectFilter::make('roles')
                    ->label('Peran')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pengguna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nama Lengkap')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
 
                                TextEntry::make('email')
                                    ->label('Alamat Email')
                                    ->copyable()
                                    ->icon('heroicon-m-envelope'),
 
                                TextEntry::make('phone')
                                    ->label('Nomor Telepon')
                                    ->copyable()
                                    ->icon('heroicon-m-phone'),
 
                                TextEntry::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->badge()
                                    ->colors([
                                        'primary' => 'male',
                                        'secondary' => 'female',
                                    ]),
                            ]),
 
                        IconEntry::make('is_active')
                            ->label('Status Aktif')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])->columns(1),

                Section::make('Peran & Izin')
                    ->schema([
                        TextEntry::make('roles.name')
                            ->label('Peran yang Ditetapkan')
                            ->badge()
                            ->separator(',')
                            ->color('info'),
 
                        TextEntry::make('permissions.name')
                            ->label('Izin Langsung')
                            ->badge()
                            ->separator(',')
                            ->color('warning'),
                    ])->columns(1)->collapsible(),

                Section::make('Informasi Akun')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('email_verified_at')
                                    ->label('Email Diverifikasi Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-check-circle')
                                    ->color('success'),
 
                                TextEntry::make('created_at')
                                    ->label('Akun Dibuat')
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),
 
                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime()
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])->columns(1)->collapsible(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['roles', 'permissions']);
    }
}
