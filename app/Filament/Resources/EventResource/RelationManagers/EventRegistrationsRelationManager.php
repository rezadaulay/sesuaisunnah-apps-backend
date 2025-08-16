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
                Section::make('Registration Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Participant'),
 
                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'attended' => 'Attended',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
 
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('registration_date')
                                    ->default(now())
                                    ->required(),
 
                                TextInput::make('referral_source')
                                    ->maxLength(100)
                                    ->placeholder('How did you hear about this event?'),
                            ]),
 
                        Textarea::make('notes')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Additional notes about this registration'),
                    ])->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Participant Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
 
                TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),
 
                BadgeColumn::make('user.gender')
                    ->colors([
                        'primary' => 'male',
                        'secondary' => 'female',
                    ]),
 
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'confirmed',
                        'success' => 'attended',
                        'danger' => 'cancelled',
                    ]),
 
                TextColumn::make('registration_date')
                    ->date()
                    ->sortable(),
 
                TextColumn::make('referral_source')
                    ->limit(30)
                    ->toggleable(),
 
                IconColumn::make('is_attended')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
 
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
                        'attended' => 'Attended',
                        'cancelled' => 'Cancelled',
                    ]),
 
                SelectFilter::make('user.gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->label('Gender'),
 
                TernaryFilter::make('is_attended')
                    ->label('Attendance'),
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
