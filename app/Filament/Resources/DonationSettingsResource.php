<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationSettingsResource\Pages;
use App\Models\DonationSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Database\Eloquent\Builder;

class DonationSettingsResource extends Resource
{
    protected static ?string $model = DonationSettings::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pengaturan Donasi';

    protected static ?string $modelLabel = 'Pengaturan Donasi';

    protected static ?string $pluralModelLabel = 'Pengaturan Donasi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Rekening Bank')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Nama Bank')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Contoh: Bank Syariah Indonesia'),

                                Forms\Components\TextInput::make('account_number')
                                    ->label('Nomor Rekening')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Contoh: 1234567890'),
                            ]),

                        Forms\Components\TextInput::make('account_name')
                            ->label('Nama Pemilik Rekening')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: Yayasan Sesuai Sunnah'),

                        Forms\Components\TextInput::make('swift_code')
                                    ->label('Swift Code (Opsional)')
                                    ->maxLength(20)
                                    ->placeholder('Contoh: BSINIDJA'),

                        Forms\Components\TextInput::make('branch_name')
                                    ->label('Nama Cabang (Opsional)')
                                    ->maxLength(100)
                                    ->placeholder('Contoh: Cabang Jakarta Pusat'),
                    ])->columns(1),

                Forms\Components\Section::make('Teks dan Catatan')
                    ->schema([
                        Forms\Components\Textarea::make('donation_note')
                            ->label('Catatan Tambahan untuk Donatur')
                            ->rows(4)
                            ->maxLength(500)
                            ->placeholder('Contoh: Terima kasih atas donasi Anda. Semoga Allah SWT membalas kebaikan Anda dengan berlipat ganda.'),

                        Forms\Components\Textarea::make('bank_transfer_note')
                            ->label('Catatan untuk Transfer Bank')
                            ->rows(3)
                            ->maxLength(200)
                            ->placeholder('Contoh: Mohon cantumkan nama donatur pada kolom berita transfer'),

                        Forms\Components\TextInput::make('minimum_donation')
                            ->label('Donasi Minimum')
                            ->numeric()
                            ->minValue(1000)
                            ->step(1000)
                            ->default(10000)
                            ->helperText('Jumlah minimum donasi yang diterima (dalam Rupiah)'),
                    ])->columns(1),

                Forms\Components\Section::make('Pengaturan Umum')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktifkan Donasi')
                            ->default(true)
                            ->helperText('Jika dinonaktifkan, halaman donasi tidak akan tersedia'),

                        Forms\Components\TextInput::make('contact_person')
                            ->label('Kontak Person')
                            ->maxLength(100)
                            ->placeholder('Contoh: Ustadz Ahmad - 08123456789'),

                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email Kontak')
                            ->email()
                            ->maxLength(100)
                            ->placeholder('Contoh: donasi@sesuaisunnah.org'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('account_name')
                    ->label('Pemilik Rekening')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('minimum_donation')
                    ->label('Min. Donasi')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Rekening Bank')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('bank_name')
                                    ->label('Nama Bank')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),

                                TextEntry::make('account_number')
                                    ->label('Nomor Rekening')
                                    ->copyable()
                                    ->icon('heroicon-m-credit-card'),

                                TextEntry::make('account_name')
                                    ->label('Nama Pemilik Rekening')
                                    ->icon('heroicon-m-user'),

                                TextEntry::make('swift_code')
                                    ->label('Swift Code')
                                    ->icon('heroicon-m-globe-alt')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ditetapkan'),

                                TextEntry::make('branch_name')
                                    ->label('Nama Cabang')
                                    ->icon('heroicon-m-building-office')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ditetapkan'),
                            ]),
                    ])->columns(2),

                Section::make('Teks dan Catatan')
                    ->schema([
                        TextEntry::make('donation_note')
                            ->label('Catatan Tambahan untuk Donatur')
                            ->markdown()
                            ->columnSpan(2),

                        TextEntry::make('bank_transfer_note')
                            ->label('Catatan untuk Transfer Bank')
                            ->columnSpan(2),

                        TextEntry::make('minimum_donation')
                            ->label('Donasi Minimum')
                            ->money('IDR')
                            ->badge()
                            ->color('warning'),
                    ])->columns(2)->collapsible(),

                Section::make('Pengaturan Umum')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('is_active')
                                    ->label('Status Aktif')
                                    ->badge()
                                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('contact_person')
                                    ->label('Kontak Person')
                                    ->icon('heroicon-m-phone')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ditetapkan'),

                                TextEntry::make('contact_email')
                                    ->label('Email Kontak')
                                    ->icon('heroicon-m-envelope')
                                    ->formatStateUsing(fn ($state) => $state ?: 'Tidak Ditetapkan'),
                            ]),
                    ])->columns(2)->collapsible(),

                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime()
                                    ->icon('heroicon-m-clock'),
                            ]),
                    ])->columns(2)->collapsible(),
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
            'index' => Pages\ListDonationSettings::route('/'),
            'create' => Pages\CreateDonationSettings::route('/create'),
            'edit' => Pages\EditDonationSettings::route('/{record}/edit'),
            'view' => Pages\ViewDonationSettings::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
