<?php

namespace App\Filament\Resources\DonationSettingsResource\Pages;

use App\Filament\Resources\DonationSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDonationSettings extends ViewRecord
{
    protected static string $resource = DonationSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
