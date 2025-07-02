<?php

namespace Alareqi\FilamentAppVersionManager\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Platform: string implements HasLabel, HasColor, HasIcon
{
    case IOS = 'ios';
    case ANDROID = 'android';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IOS => __('filament-app-version-manager::app_version.platforms.ios'),
            self::ANDROID => __('filament-app-version-manager::app_version.platforms.android'),
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::IOS => 'gray',
            self::ANDROID => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::IOS => 'heroicon-o-device-phone-mobile',
            self::ANDROID => 'heroicon-o-device-phone-mobile',
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
     * Get all available platform cases
     *
     * @deprecated Use Platform::cases() directly instead
     */
    public static function fromConfig(): array
    {
        return self::cases();
    }
}
