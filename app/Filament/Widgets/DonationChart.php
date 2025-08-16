<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DonationChart extends ChartWidget
{
    protected static ?string $heading = 'Donation Trends';

    protected function getData(): array
    {
        $days = collect();
        $amounts = collect();

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push($date->format('M d'));
            
            $amount = Donation::where('status', 'completed')
                ->whereDate('donation_date', $date)
                ->sum('amount');
            
            $amounts->push($amount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Daily Donations (Rp)',
                    'data' => $amounts->toArray(),
                    'borderColor' => '#069A8E',
                    'backgroundColor' => 'rgba(6, 154, 142, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
