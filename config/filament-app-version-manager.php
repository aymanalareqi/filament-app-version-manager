<?php

// config for Alareqi/FilamentAppVersionManager
return [

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the API endpoints for version checking.
    |
    */
    'api' => [
        'enabled' => true,
        'prefix' => 'api/version',
        'middleware' => ['throttle:60,1'],
        'cache_ttl' => 300, // 5 minutes in seconds
        'enable_stats' => false, // Enable the /stats endpoint
    ],

    /*
    |--------------------------------------------------------------------------
    | Navigation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how the plugin appears in Filament navigation.
    |
    */
    'navigation' => [
        'group' => null, // defaul __('filament-app-version-manager::app_version.navigation_group')
        'icon' => 'heroicon-o-rocket-launch',
        'sort' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Configure validation rules for version management.
    |
    */
    'validation' => [
        'semantic_versioning' => true,
        'max_version_length' => 20,
        'max_build_number_length' => 50,
        'max_download_url_length' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Values
    |--------------------------------------------------------------------------
    |
    | Configure default values for new app versions.
    |
    */
    'defaults' => [
        'platform' => 'ios',
        'is_active' => true,
        'is_beta' => false,
        'is_rollback' => false,
        'force_update' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the plugin.
    |
    */
    'features' => [
        'multilingual_release_notes' => true,
        'version_rollback' => true,
        'beta_versions' => true,
        'force_updates' => true,
        'metadata_storage' => true,
        'audit_trail' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Platforms
    |--------------------------------------------------------------------------
    |
    | Configure the platforms supported by your application.
    |
    */
    'platforms' => [
        'ios' => [
            'label' => 'iOS',
            'color' => 'gray',
            'icon' => 'heroicon-o-device-phone-mobile',
        ],
        'android' => [
            'label' => 'Android',
            'color' => 'success',
            'icon' => 'heroicon-o-device-phone-mobile',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database-related settings.
    |
    */
    'database' => [
        'table_name' => 'app_versions',
        'connection' => null, // Use default connection
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Configure localization settings.
    |
    */
    'localization' => [
        'default_locale' => 'ar',
        'supported_locales' => ['ar', 'en'],
        'fallback_locale' => 'en',
    ],

];
