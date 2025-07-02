<?php

/**
 * Basic Configuration Example for Filament App Version Manager
 * 
 * This example shows a simple, production-ready configuration
 * suitable for most applications.
 */

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;

// Basic plugin registration in your Filament Panel Provider
public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentAppVersionManagerPlugin::make()
                ->navigationGroup('App Management')
                ->navigationSort(10)
                ->navigationIcon('heroicon-o-rocket-launch')
                ->enableApiRoutes(true)
                ->apiCacheTtl(3600) // Cache API responses for 1 hour
                ->supportedLocales(['en', 'ar'])
                ->defaultLocale('en')
                ->maxVersionLength(20)
                ->enableBetaVersions(true)
                ->enableForceUpdate(true),
        ]);
}

// Alternative: Configuration file approach
// In config/filament-app-version-manager.php
return [
    'api' => [
        'enabled' => true,
        'prefix' => 'api/version',
        'middleware' => ['throttle:60,1'],
        'cache_ttl' => 3600,
        'enable_stats' => false,
    ],

    'navigation' => [
        'group' => 'App Management',
        'sort' => 10,
        'icon' => 'heroicon-o-rocket-launch',
    ],

    'features' => [
        'multilingual_release_notes' => true,
        'version_rollback' => true,
        'beta_versions' => true,
        'force_update' => true,
        'metadata_storage' => true,
        'audit_trail' => true,
    ],

    'localization' => [
        'supported_locales' => ['en', 'ar'],
        'default_locale' => 'en',
    ],

    'validation' => [
        'max_version_length' => 20,
        'max_build_number' => 99999,
        'max_release_notes_length' => 1000,
    ],

    'defaults' => [
        'platform' => 'all',
        'is_active' => true,
        'is_beta' => false,
        'force_update' => false,
    ],

    'database' => [
        'table_name' => 'app_versions',
        'connection' => null,
    ],

    'platforms' => [
        'ios' => [
            'label' => 'iOS',
            'color' => 'info',
            'icon' => 'heroicon-o-device-phone-mobile',
        ],
        'android' => [
            'label' => 'Android',
            'color' => 'success',
            'icon' => 'heroicon-o-device-phone-mobile',
        ],
        'all' => [
            'label' => 'All Platforms',
            'color' => 'primary',
            'icon' => 'heroicon-o-globe-alt',
        ],
    ],
];
