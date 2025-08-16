<?php

namespace App\Providers;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class FilamentColorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom colors for Filament
        FilamentColor::register([
            'primary' => Color::hex('#005555'), // Blue-600
            'secondary' => Color::hex('#069A8E'), // Violet-600
            'success' => Color::hex('#069A8E'), // Emerald-600
            'warning' => Color::hex('#FFC700'), // Amber-600
            'danger' => Color::hex('#dc2626'), // Red-600
            'info' => Color::hex('#0284c7'), // Sky-600
            'gray' => Color::hex('#4b5563'), // Gray-600
        ]);
    }
}
