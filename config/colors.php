<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Color Scheme
    |--------------------------------------------------------------------------
    |
    | This file contains the color scheme used throughout the application.
    | Colors are defined in hex format and organized by purpose.
    |
    */

    'primary' => [
        'main' => '#005555',
        'light' => '#67cbcb',
        'dark' => '#003f3f',
        '50' => '#f0f9f9',
        '100' => '#d9f2f2',
        '200' => '#b3e5e5',
        '300' => '#8dd8d8',
        '400' => '#67cbcb',
        '500' => '#005555',
        '600' => '#004a4a',
        '700' => '#003f3f',
        '800' => '#003434',
        '900' => '#002929',
        '950' => '#001e1e',
    ],

    'secondary' => [
        'main' => '#FFC700',
        'light' => '#ffd767',
        'dark' => '#cc9f00',
        '50' => '#fffbf0',
        '100' => '#fff5d9',
        '200' => '#ffebb3',
        '300' => '#ffe18d',
        '400' => '#ffd767',
        '500' => '#FFC700',
        '600' => '#e6b300',
        '700' => '#cc9f00',
        '800' => '#b38b00',
        '900' => '#997700',
        '950' => '#806300',
    ],

    'accent' => [
        'main' => '#0ea5e9',
        'light' => '#7dd3fc',
        'dark' => '#0369a1',
        '50' => '#f0f9ff',
        '100' => '#e0f2fe',
        '200' => '#bae6fd',
        '300' => '#7dd3fc',
        '400' => '#38bdf8',
        '500' => '#0ea5e9',
        '600' => '#0284c7',
        '700' => '#0369a1',
        '800' => '#075985',
        '900' => '#0c4a6e',
        '950' => '#082f49',
    ],

    'success' => [
        'main' => '#22c55e',
        'light' => '#86efac',
        'dark' => '#15803d',
        '50' => '#f0fdf4',
        '100' => '#dcfce7',
        '200' => '#bbf7d0',
        '300' => '#86efac',
        '400' => '#4ade80',
        '500' => '#22c55e',
        '600' => '#16a34a',
        '700' => '#15803d',
        '800' => '#166534',
        '900' => '#14532d',
        '950' => '#052e16',
    ],

    'warning' => [
        'main' => '#f59e0b',
        'light' => '#fcd34d',
        'dark' => '#b45309',
        '50' => '#fffbeb',
        '100' => '#fef3c7',
        '200' => '#fde68a',
        '300' => '#fcd34d',
        '400' => '#fbbf24',
        '500' => '#f59e0b',
        '600' => '#d97706',
        '700' => '#b45309',
        '800' => '#92400e',
        '900' => '#78350f',
        '950' => '#451a03',
    ],

    'error' => [
        'main' => '#ef4444',
        'light' => '#fca5a5',
        'dark' => '#b91c1c',
        '50' => '#fef2f2',
        '100' => '#fee2e2',
        '200' => '#fecaca',
        '300' => '#fca5a5',
        '400' => '#f87171',
        '500' => '#ef4444',
        '600' => '#dc2626',
        '700' => '#b91c1c',
        '800' => '#991b1b',
        '900' => '#7f1d1d',
        '950' => '#450a0a',
    ],

    'neutral' => [
        '50' => '#fafafa',
        '100' => '#f5f5f5',
        '200' => '#e5e5e5',
        '300' => '#d4d4d4',
        '400' => '#a3a3a3',
        '500' => '#737373',
        '600' => '#525252',
        '700' => '#404040',
        '800' => '#262626',
        '900' => '#171717',
    ],

    /*
    |--------------------------------------------------------------------------
    | Color Usage Guidelines
    |--------------------------------------------------------------------------
    |
    | Primary (#005555): Main brand color, buttons, links, headers
    | Secondary (#FFC700): Accent color, highlights, CTAs
    | Accent (#0ea5e9): Information, links, interactive elements
    | Success (#22c55e): Success states, confirmations
    | Warning (#f59e0b): Warning states, alerts
    | Error (#ef4444): Error states, destructive actions
    | Neutral: Text, backgrounds, borders
    |
    */

    'usage' => [
        'brand' => 'primary.500',
        'accent' => 'secondary.500',
        'info' => 'accent.500',
        'success' => 'success.500',
        'warning' => 'warning.500',
        'error' => 'error.500',
        'text' => 'neutral.800',
        'text_secondary' => 'neutral.600',
        'background' => 'neutral.50',
        'border' => 'neutral.200',
    ],

    /*
    |--------------------------------------------------------------------------
    | CSS Variables
    |--------------------------------------------------------------------------
    |
    | CSS variables that can be used in frontend styling
    |
    */

    'css_variables' => [
        '--color-primary' => '#005555',
        '--color-primary-light' => '#67cbcb',
        '--color-primary-dark' => '#003f3f',
        '--color-secondary' => '#FFC700',
        '--color-secondary-light' => '#ffd767',
        '--color-secondary-dark' => '#cc9f00',
        '--color-accent' => '#0ea5e9',
        '--color-accent-light' => '#7dd3fc',
        '--color-accent-dark' => '#0369a1',
        '--color-success' => '#22c55e',
        '--color-warning' => '#f59e0b',
        '--color-error' => '#ef4444',
    ],
];
