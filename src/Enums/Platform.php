<?php

namespace Alareqi\FilamentAppVersionManager\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Platform: string implements HasLabel, HasColor, HasIcon
{
    case IOS = 'ios';
    case ANDROID = 'android';
    case ALL = 'all';

    public function getLabel(): ?string
    {
        $config = config('filament-app-version-manager.platforms.' . $this->value . '.label');

        if ($config) {
            return $config;
        }

        return match ($this) {
            self::IOS => __('filament-app-version-manager::app_version.platforms.ios'),
            self::ANDROID => __('filament-app-version-manager::app_version.platforms.android'),
            self::ALL => __('filament-app-version-manager::app_version.platforms.all'),
        };
    }

    public function getColor(): string|array|null
    {
        $config = config('filament-app-version-manager.platforms.' . $this->value . '.color');

        if ($config) {
            return $config;
        }

        return match ($this) {
            self::IOS => 'gray',
            self::ANDROID => 'success',
            self::ALL => 'primary',
        };
    }

    public function getIcon(): ?string
    {
        $config = config('filament-app-version-manager.platforms.' . $this->value . '.icon');

        if ($config) {
            return $config;
        }

        return match ($this) {
            self::IOS => 'heroicon-o-device-phone-mobile',
            self::ANDROID => 'heroicon-o-device-phone-mobile',
            self::ALL => 'heroicon-o-globe-alt',
        };
    }

    /**
     * Get all platform values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get platform options for forms
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }

    /**
     * Get platform from configuration
     */
    public static function fromConfig(): array
    {
        $platforms = config('filament-app-version-manager.platforms', []);
        $cases = [];

        foreach ($platforms as $value => $config) {
            if (in_array($value, self::values())) {
                $cases[] = self::from($value);
            }
        }

        return $cases ?: self::cases();
    }
}
