<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use App\Models\Event;
use App\Models\Ebook;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDonations = Donation::where('status', 'completed')->sum('amount');
        $pendingDonations = Donation::where('status', 'pending')->count();
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('start_date', '>', now())->count();
        $totalEbooks = Ebook::count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Donations', 'Rp ' . number_format($totalDonations, 0, ',', '.'))
                ->description('Total completed donations')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Pending Donations', $pendingDonations)
                ->description('Donations awaiting verification')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Events', $totalEvents)
                ->description('All events created')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Upcoming Events', $upcomingEvents)
                ->description('Events in the future')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Total Ebooks', $totalEbooks)
                ->description('Available ebooks')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('secondary'),

            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
