<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('close_event')
                ->label('Tutup Event')
                ->icon('heroicon-m-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tutup Event')
                ->modalDescription('Apakah Anda yakin ingin menutup event ini? Event yang ditutup tidak bisa menerima registrasi baru.')
                ->action(function ($record) {
                    $record->closeEvent();
                })
                ->visible(fn ($record) => $record->status !== 'event_closed'),
        ];
    }
}
