<?php

/**
 * Advanced Configuration Example for Filament App Version Manager
 * 
 * This example demonstrates advanced features including:
 * - Dynamic configuration with closures
 * - Environment-based settings
 * - Custom validation rules
 * - Multi-environment API configuration
 * - Advanced localization setup
 */

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;

// Advanced plugin registration with dynamic configuration
public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentAppVersionManagerPlugin::make()
                // Dynamic navigation based on user role
                ->navigationGroup(function () {
                    $user = auth()->user();
                    if ($user && method_exists($user, 'hasRole')) {
                        return $user->hasRole('super-admin') ? 'System Management' : 'App Management';
                    }
                    return 'App Management';
                })
                
                // Environment-based navigation sort
                ->navigationSort(fn() => config('app.env') === 'production' ? 5 : 10)
                
                // Dynamic icon based on app state
                ->navigationIcon(function () {
                    return app()->isDownForMaintenance() 
                        ? 'heroicon-o-exclamation-triangle' 
                        : 'heroicon-o-rocket-launch';
                })
                
                // Environment-based API configuration
                ->enableApiRoutes(fn() => !app()->environment('local'))
                ->apiPrefix(function () {
                    return match (config('app.env')) {
                        'production' => 'api/v1/version',
                        'staging' => 'api/staging/version',
                        default => 'api/dev/version',
                    };
                })
                
                // Dynamic cache TTL based on environment
                ->apiCacheTtl(function () {
                    return match (config('app.env')) {
                        'production' => 3600,  // 1 hour in production
                        'staging' => 1800,     // 30 minutes in staging
                        default => 60,         // 1 minute in development
                    };
                })
                
                // Enable stats only in production
                ->enableApiStats(fn() => config('app.env') === 'production')
                
                // Dynamic localization based on app configuration
                ->supportedLocales(function () {
                    $locales = config('app.available_locales', ['en']);
                    // Always include English as fallback
                    return array_unique(array_merge(['en'], $locales));
                })
                
                // Dynamic default locale
                ->defaultLocale(fn() => config('app.locale', 'en'))
                
                // Environment-based validation rules
                ->maxVersionLength(function () {
                    return config('app.env') === 'production' ? 20 : 50;
                })
                
                // Dynamic feature flags
                ->enableBetaVersions(fn() => config('app.debug') || config('app.env') !== 'production')
                ->enableForceUpdate(fn() => !app()->environment('testing'))
                ->enableMetadataStorage(fn() => config('app.env') !== 'local')
                
                // Custom platform configuration
                ->addPlatform('web', [
                    'label' => 'Web App',
                    'color' => 'warning',
                    'icon' => 'heroicon-o-globe-alt',
                ])
                ->addPlatform('desktop', [
                    'label' => 'Desktop',
                    'color' => 'gray',
                    'icon' => 'heroicon-o-computer-desktop',
                ])
                
                // Database configuration for multi-tenant apps
                ->databaseConnection(function () {
                    return tenant() ? 'tenant' : 'landlord';
                })
                ->tableName(function () {
                    $prefix = config('app.env') === 'testing' ? 'test_' : '';
                    return $prefix . 'app_versions';
                })
                
                // Bulk configuration for complex setups
                ->configureWith([
                    'validation.max_build_number' => 999999,
                    'validation.max_release_notes_length' => 2000,
                    'defaults.is_active' => true,
                ]),
        ]);
}

// Advanced configuration file with environment variables
// In config/filament-app-version-manager.php
return [
    'api' => [
        'enabled' => env('APP_VERSION_API_ENABLED', true),
        'prefix' => env('APP_VERSION_API_PREFIX', 'api/version'),
        'middleware' => [
            'throttle:' . env('APP_VERSION_API_RATE_LIMIT', '60,1'),
            'auth:sanctum', // Add authentication if needed
        ],
        'cache_ttl' => (int) env('APP_VERSION_CACHE_TTL', 3600),
        'enable_stats' => env('APP_VERSION_STATS_ENABLED', false),
    ],

    'navigation' => [
        'group' => env('APP_VERSION_NAV_GROUP'),
        'sort' => (int) env('APP_VERSION_NAV_SORT', 10),
        'icon' => env('APP_VERSION_NAV_ICON', 'heroicon-o-rocket-launch'),
    ],

    'features' => [
        'multilingual_release_notes' => env('APP_VERSION_MULTILINGUAL', true),
        'version_rollback' => env('APP_VERSION_ROLLBACK', true),
        'beta_versions' => env('APP_VERSION_BETA', true),
        'force_update' => env('APP_VERSION_FORCE_UPDATE', true),
        'metadata_storage' => env('APP_VERSION_METADATA', true),
        'audit_trail' => env('APP_VERSION_AUDIT', true),
    ],

    'localization' => [
        'supported_locales' => explode(',', env('APP_VERSION_LOCALES', 'en,ar')),
        'default_locale' => env('APP_VERSION_DEFAULT_LOCALE', 'en'),
    ],

    'validation' => [
        'max_version_length' => (int) env('APP_VERSION_MAX_LENGTH', 20),
        'max_build_number' => (int) env('APP_VERSION_MAX_BUILD', 99999),
        'max_release_notes_length' => (int) env('APP_VERSION_MAX_NOTES', 1000),
    ],

    'defaults' => [
        'platform' => env('APP_VERSION_DEFAULT_PLATFORM', 'ios'),
        'is_active' => env('APP_VERSION_DEFAULT_ACTIVE', true),
        'is_beta' => env('APP_VERSION_DEFAULT_BETA', false),
        'force_update' => env('APP_VERSION_DEFAULT_FORCE', false),
    ],

    'database' => [
        'table_name' => env('APP_VERSION_TABLE', 'app_versions'),
        'connection' => env('APP_VERSION_DB_CONNECTION'),
    ],

    // Custom platforms for specialized apps
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
        'web' => [
            'label' => 'Web App',
            'color' => 'warning',
            'icon' => 'heroicon-o-globe-alt',
        ],
        'desktop' => [
            'label' => 'Desktop',
            'color' => 'gray',
            'icon' => 'heroicon-o-computer-desktop',
        ],

    ],
];

// Example .env configuration
/*
# App Version Manager Configuration
APP_VERSION_API_ENABLED=true
APP_VERSION_API_PREFIX=api/v1/version
APP_VERSION_API_RATE_LIMIT=100,1
APP_VERSION_CACHE_TTL=3600
APP_VERSION_STATS_ENABLED=true

APP_VERSION_NAV_GROUP="System Management"
APP_VERSION_NAV_SORT=5
APP_VERSION_NAV_ICON=heroicon-o-rocket-launch

APP_VERSION_MULTILINGUAL=true
APP_VERSION_ROLLBACK=true
APP_VERSION_BETA=true
APP_VERSION_FORCE_UPDATE=true
APP_VERSION_METADATA=true
APP_VERSION_AUDIT=true

APP_VERSION_LOCALES=en,ar,fr,es
APP_VERSION_DEFAULT_LOCALE=en

APP_VERSION_MAX_LENGTH=25
APP_VERSION_MAX_BUILD=999999
APP_VERSION_MAX_NOTES=2000

APP_VERSION_DEFAULT_PLATFORM=all
APP_VERSION_DEFAULT_ACTIVE=true
APP_VERSION_DEFAULT_BETA=false
APP_VERSION_DEFAULT_FORCE=false

APP_VERSION_TABLE=app_versions
APP_VERSION_DB_CONNECTION=mysql
*/
