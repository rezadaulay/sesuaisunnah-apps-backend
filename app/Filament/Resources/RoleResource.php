<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid as InfolistGrid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Roles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Role Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Enter role name (e.g., admin, event_manager)'),

                        TextInput::make('display_name')
                            ->maxLength(255)
                            ->placeholder('Enter display name (e.g., Administrator, Event Manager)'),

                        TextInput::make('description')
                            ->maxLength(500)
                            ->placeholder('Enter role description'),
                    ])->columns(1),

                Section::make('Permissions')
                    ->schema([
                        Select::make('permissions')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->searchable()
                            ->placeholder('Select permissions for this role'),
                    ])->columns(1),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_system_role')
                            ->label('System Role (Cannot be deleted)')
                            ->default(false)
                            ->disabled(),

                        TextInput::make('created_at')
                            ->label('Created At')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('updated_at')
                            ->label('Updated At')
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
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('display_name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('description')
                    ->limit(80)
                    ->toggleable(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->badge()
                    ->color('info'),

                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_system_role')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Filter by Permission'),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->visible(fn (Role $record) => !$record->is_system_role),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Role Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Role Name'),
                        TextEntry::make('display_name')
                            ->label('Display Name'),
                        TextEntry::make('description')
                            ->label('Description'),
                    ]),
                InfolistSection::make('Permissions')
                    ->schema([
                        InfolistGrid::make('permissions')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Permission Name'),
                            ]),
                    ]),
                InfolistSection::make('Settings')
                    ->schema([
                        TextEntry::make('is_system_role')
                            ->label('System Role'),
                        TextEntry::make('created_at')
                            ->label('Created At'),
                        TextEntry::make('updated_at')
                            ->label('Updated At'),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
            'view' => Pages\ViewRole::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['permissions', 'users']);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
