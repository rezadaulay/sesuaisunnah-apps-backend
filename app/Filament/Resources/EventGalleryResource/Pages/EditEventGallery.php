<?php

namespace App\Filament\Resources\EventGalleryResource\Pages;

use App\Filament\Resources\EventGalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEventGallery extends EditRecord
{
    protected static string $resource = EventGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
