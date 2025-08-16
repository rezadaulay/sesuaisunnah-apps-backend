<?php

namespace App\Filament\Widgets;

use App\Models\EventRegistration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventRegistrationStats extends BaseWidget
{
    protected function getStats(): array
    {
        $referralStats = EventRegistration::getReferralSourceStats();
        $genderStats = EventRegistration::getGenderStats();
        $topEvents = EventRegistration::getTopEvents(5);
        $totalRegistrations = EventRegistration::count();
        $todayRegistrations = EventRegistration::whereDate('registered_at', today())->count();
        $monthlyRegistrations = EventRegistration::whereMonth('registered_at', now()->month)->count();

        return [
            Stat::make('Total Registrasi', $totalRegistrations)
                ->description('Semua waktu')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Registrasi Hari Ini', $todayRegistrations)
                ->description('Registrasi baru hari ini')
                ->descriptionIcon('heroicon-m-calendar-today')
                ->color('success'),

            Stat::make('Registrasi Bulan Ini', $monthlyRegistrations)
                ->description('Registrasi bulan ' . now()->format('M Y'))
                ->descriptionIcon('heroicon-m-calendar-month')
                ->color('info'),

            Stat::make('Top Referral Source', $referralStats->keys()->first() ?: 'N/A')
                ->description('Sumber registrasi terbanyak: ' . ($referralStats->values()->first() ?: 0) . ' orang')
                ->descriptionIcon('heroicon-m-share')
                ->color('warning'),

            Stat::make('Gender Distribution', $genderStats->keys()->first() ?: 'N/A')
                ->description('Distribusi gender: ' . ($genderStats->values()->first() ?: 0) . ' orang')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('secondary'),

            Stat::make('Top Event', $topEvents->first()?->event?->title ?: 'N/A')
                ->description('Event dengan registrasi terbanyak: ' . ($topEvents->first()?->registration_count ?: 0) . ' peserta')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
        ];
    }
}
