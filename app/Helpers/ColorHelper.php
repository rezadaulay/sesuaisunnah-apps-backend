<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Get color value from config
     *
     * @param string $key
     * @param string|null $default
     * @return string
     */
    public static function get(string $key, ?string $default = null): string
    {
        $keys = explode('.', $key);
        $config = config('colors');
        
        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                return $default ?? '#000000';
            }
            $config = $config[$k];
        }
        
        return $config ?? $default ?? '#000000';
    }

    /**
     * Get primary color
     *
     * @param string $shade
     * @return string
     */
    public static function primary(string $shade = 'main'): string
    {
        return self::get("primary.{$shade}");
    }

    /**
     * Get secondary color
     *
     * @param string $shade
     * @return string
     */
    public static function secondary(string $shade = 'main'): string
    {
        return self::get("secondary.{$shade}");
    }

    /**
     * Get accent color
     *
     * @param string $shade
     * @return string
     */
    public static function accent(string $shade = 'main'): string
    {
        return self::get("accent.{$shade}");
    }

    /**
     * Get success color
     *
     * @param string $shade
     * @return string
     */
    public static function success(string $shade = 'main'): string
    {
        return self::get("success.{$shade}");
    }

    /**
     * Get warning color
     *
     * @param string $shade
     * @return string
     */
    public static function warning(string $shade = 'main'): string
    {
        return self::get("warning.{$shade}");
    }

    /**
     * Get error color
     *
     * @param string $shade
     * @return string
     */
    public static function error(string $shade = 'main'): string
    {
        return self::get("error.{$shade}");
    }

    /**
     * Get neutral color
     *
     * @param string $shade
     * @return string
     */
    public static function neutral(string $shade = '500'): string
    {
        return self::get("neutral.{$shade}");
    }

    /**
     * Get all CSS variables as array
     *
     * @return array
     */
    public static function getCssVariables(): array
    {
        return config('colors.css_variables', []);
    }

    /**
     * Get all CSS variables as string
     *
     * @return string
     */
    public static function getCssVariablesString(): string
    {
        $variables = self::getCssVariables();
        $css = '';
        
        foreach ($variables as $variable => $value) {
            $css .= "{$variable}: {$value};\n";
        }
        
        return $css;
    }

    /**
     * Get color palette for API responses
     *
     * @return array
     */
    public static function getPalette(): array
    {
        return [
            'primary' => [
                'main' => self::primary(),
                'light' => self::primary('light'),
                'dark' => self::primary('dark'),
                'shades' => [
                    '50' => self::primary('50'),
                    '100' => self::primary('100'),
                    '200' => self::primary('200'),
                    '300' => self::primary('300'),
                    '400' => self::primary('400'),
                    '500' => self::primary('500'),
                    '600' => self::primary('600'),
                    '700' => self::primary('700'),
                    '800' => self::primary('800'),
                    '900' => self::primary('900'),
                    '950' => self::primary('950'),
                ]
            ],
            'secondary' => [
                'main' => self::secondary(),
                'light' => self::secondary('light'),
                'dark' => self::secondary('dark'),
                'shades' => [
                    '50' => self::secondary('50'),
                    '100' => self::secondary('100'),
                    '200' => self::secondary('200'),
                    '300' => self::secondary('300'),
                    '400' => self::secondary('400'),
                    '500' => self::secondary('500'),
                    '600' => self::secondary('600'),
                    '700' => self::secondary('700'),
                    '800' => self::secondary('800'),
                    '900' => self::secondary('900'),
                    '950' => self::secondary('950'),
                ]
            ],
            'accent' => [
                'main' => self::accent(),
                'light' => self::accent('light'),
                'dark' => self::accent('dark'),
            ],
            'semantic' => [
                'success' => self::success(),
                'warning' => self::warning(),
                'error' => self::error(),
            ],
            'neutral' => [
                '50' => self::neutral('50'),
                '100' => self::neutral('100'),
                '200' => self::neutral('200'),
                '300' => self::neutral('300'),
                '400' => self::neutral('400'),
                '500' => self::neutral('500'),
                '600' => self::neutral('600'),
                '700' => self::neutral('700'),
                '800' => self::neutral('800'),
                '900' => self::neutral('900'),
            ]
        ];
    }

    /**
     * Check if color is light or dark
     *
     * @param string $hexColor
     * @return string 'light' or 'dark'
     */
    public static function getBrightness(string $hexColor): string
    {
        // Remove # if present
        $hex = ltrim($hexColor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        // Calculate brightness using YIQ formula
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        return $brightness > 128 ? 'light' : 'dark';
    }

    /**
     * Get contrasting text color (black or white)
     *
     * @param string $hexColor
     * @return string
     */
    public static function getContrastTextColor(string $hexColor): string
    {
        return self::getBrightness($hexColor) === 'light' ? '#000000' : '#ffffff';
    }

    /**
     * Generate color variations
     *
     * @param string $hexColor
     * @param float $percentage
     * @return string
     */
    public static function lighten(string $hexColor, float $percentage): string
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = min(255, $r + (255 - $r) * $percentage / 100);
        $g = min(255, $g + (255 - $g) * $percentage / 100);
        $b = min(255, $b + (255 - $b) * $percentage / 100);
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * Generate darker color variations
     *
     * @param string $hexColor
     * @param float $percentage
     * @return string
     */
    public static function darken(string $hexColor, float $percentage): string
    {
        $hex = ltrim($hexColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        $r = max(0, $r - $r * $percentage / 100);
        $g = max(0, $g - $g * $percentage / 100);
        $b = max(0, $b - $b * $percentage / 100);
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
