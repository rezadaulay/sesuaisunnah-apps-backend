<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Manajemen Konten';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Donasi';

    protected static ?string $modelLabel = 'Donasi';

    protected static ?string $pluralModelLabel = 'Donasi';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Donation Information')
                    ->schema([
                        TextInput::make('donor_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter donor name'),

                        TextInput::make('donor_phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Enter donor phone number'),

                        TextInput::make('donor_email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Enter donor email address'),

                        Textarea::make('message')
                            ->rows(3)
                            ->placeholder('Enter donation message (optional)'),
                    ])->columns(2),

                Section::make('Donation Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('amount')
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->placeholder('0.00')
                                    ->required(),

                                Select::make('payment_method')
                                    ->options([
                                        'bank_transfer' => 'Bank Transfer',
                                        'cash' => 'Cash',
                                        'other' => 'Other',
                                    ])
                                    ->default('bank_transfer')
                                    ->required(),
                            ]),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required(),

                        DatePicker::make('donation_date')
                            ->default(now())
                            ->required(),
                    ])->columns(2),

                Section::make('Bank Information')
                    ->schema([
                        TextInput::make('bank_name')
                            ->maxLength(100)
                            ->placeholder('Enter bank name'),

                        TextInput::make('account_number')
                            ->maxLength(50)
                            ->placeholder('Enter account number'),

                        TextInput::make('account_name')
                            ->maxLength(100)
                            ->placeholder('Enter account holder name'),
                    ])->columns(3)->collapsible(),

                Section::make('Additional Settings')
                    ->schema([
                        Toggle::make('is_anonymous')
                            ->label('Anonymous Donation')
                            ->default(false),

                        Toggle::make('is_verified')
                            ->label('Payment Verified')
                            ->default(false),

                        TextInput::make('verified_by')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('verified_at')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(4)->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('donor_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                BadgeColumn::make('payment_method')
                    ->colors([
                        'primary' => 'bank_transfer',
                        'secondary' => 'cash',
                        'gray' => 'other',
                    ]),

                TextColumn::make('donation_date')
                    ->date()
                    ->sortable(),

                IconColumn::make('is_anonymous')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye'),

                IconColumn::make('is_verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'other' => 'Other',
                    ]),

                TernaryFilter::make('is_verified')
                    ->label('Payment Verified'),

                TernaryFilter::make('is_anonymous')
                    ->label('Anonymous'),
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
            ->defaultSort('donation_date', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Donation Details')
                    ->schema([
                        TextEntry::make('donor_name')
                            ->label('Donor Name'),
                        TextEntry::make('donor_phone')
                            ->label('Donor Phone'),
                        TextEntry::make('donor_email')
                            ->label('Donor Email'),
                        TextEntry::make('message')
                            ->label('Message'),
                    ]),
                InfolistSection::make('Donation Information')
                    ->schema([
                        TextEntry::make('amount')
                            ->label('Amount'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('payment_method')
                            ->label('Payment Method'),
                        TextEntry::make('donation_date')
                            ->label('Donation Date'),
                    ]),
                InfolistSection::make('Bank Information')
                    ->schema([
                        TextEntry::make('bank_name')
                            ->label('Bank Name'),
                        TextEntry::make('account_number')
                            ->label('Account Number'),
                        TextEntry::make('account_name')
                            ->label('Account Holder Name'),
                    ]),
                InfolistSection::make('Additional Settings')
                    ->schema([
                        TextEntry::make('is_anonymous')
                            ->label('Anonymous Donation')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'warning' : 'success'),
                        TextEntry::make('is_verified')
                            ->label('Payment Verified')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                        TextEntry::make('verified_by')
                            ->label('Verified By'),
                        TextEntry::make('verified_at')
                            ->label('Verified At'),
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
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
            'view' => Pages\ViewDonation::route('/{record}'),
        ];
    }
}
