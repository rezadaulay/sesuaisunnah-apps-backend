<?php

namespace App\Filament\Resources\EventGalleryResource\Pages;

use App\Filament\Resources\EventGalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventGalleries extends ListRecords
{
    protected static string $resource = EventGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
