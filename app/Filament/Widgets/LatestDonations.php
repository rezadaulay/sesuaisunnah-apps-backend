<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestDonations extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Donation::query()
            ->latest('donation_date')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('donor_name')
                ->label('Donor')
                ->searchable()
                ->formatStateUsing(fn (Donation $record): string => $record->donor_display_name),

            Tables\Columns\TextColumn::make('amount')
                ->label('Amount')
                ->money('IDR')
                ->sortable(),

            Tables\Columns\TextColumn::make('payment_method')
                ->label('Payment Method')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'bank_transfer' => 'info',
                    'cash' => 'success',
                    'e_wallet' => 'warning',
                    default => 'gray',
                }),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => 'pending',
                    'info' => 'confirmed',
                    'success' => 'completed',
                    'danger' => 'cancelled',
                ]),

            Tables\Columns\TextColumn::make('donation_date')
                ->label('Date')
                ->dateTime('M d, Y')
                ->sortable(),

            Tables\Columns\IconColumn::make('is_verified')
                ->label('Verified')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label('View Details')
                ->url(fn (Donation $record): string => route('filament.admin.resources.donations.edit', $record))
                ->icon('heroicon-m-eye')
                ->color('info'),
        ];
    }
}
