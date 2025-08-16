<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventGalleryResource\Pages;
use App\Filament\Resources\EventGalleryResource\RelationManagers;
use App\Models\EventGallery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventGalleryResource extends Resource
{
    protected static ?string $model = EventGallery::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListEventGalleries::route('/'),
            'create' => Pages\CreateEventGallery::route('/create'),
            'edit' => Pages\EditEventGallery::route('/{record}/edit'),
        ];
    }
}
