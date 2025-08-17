<?php

namespace App\Filament\Resources\DonationSettingsResource\Pages;

use App\Filament\Resources\DonationSettingsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonationSettings extends EditRecord
{
    protected static string $resource = DonationSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
