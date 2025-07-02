<?php

use Alareqi\FilamentAppVersionManager\FilamentAppVersionManagerPlugin;
use Alareqi\FilamentAppVersionManager\FilamentAppVersionManager;

describe('Configuration Override System', function () {
    beforeEach(function () {
        // Set up default config values for testing
        config([
            'filament-app-version-manager.api.enabled' => true,
            'filament-app-version-manager.api.cache_ttl' => 300,
            'filament-app-version-manager.validation.max_version_length' => 20,
            'filament-app-version-manager.features.beta_versions' => true,
            'filament-app-version-manager.defaults.is_active' => true,
        ]);
    });

    describe('Plugin Configuration Override Methods', function () {
        it('can override single configuration values using configureUsing', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->configureUsing('api.enabled', false);
            
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
        });

        it('can override multiple configuration values using configureWith', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            $plugin->configureWith([
                'api' => [
                    'enabled' => false,
                    'cache_ttl' => 600,
                ],
                'validation' => [
                    'max_version_length' => 50,
                ],
            ]);
            
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
            expect($plugin->getConfig('api.cache_ttl'))->toBe(600);
            expect($plugin->getConfig('validation.max_version_length'))->toBe(50);
        });

        it('returns config file values when no override is set', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            expect($plugin->getConfig('api.enabled'))->toBeTrue();
            expect($plugin->getConfig('api.cache_ttl'))->toBe(300);
        });

        it('returns default value when config key does not exist', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            
            expect($plugin->getConfig('non.existent.key', 'default'))->toBe('default');
        });
    });

    describe('Fluent API Configuration Methods', function () {
        it('can configure API settings using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->api(false)
                ->apiPrefix('custom/api/version')
                ->apiCacheTtl(900)
                ->apiStats(true);
            
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
            expect($plugin->getConfig('api.prefix'))->toBe('custom/api/version');
            expect($plugin->getConfig('api.cache_ttl'))->toBe(900);
            expect($plugin->getConfig('api.enable_stats'))->toBeTrue();
        });

        it('can configure validation settings using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->semanticVersioning(false)
                ->maxVersionLength(30)
                ->maxBuildNumberLength(100)
                ->maxDownloadUrlLength(1000);
            
            expect($plugin->getConfig('validation.semantic_versioning'))->toBeFalse();
            expect($plugin->getConfig('validation.max_version_length'))->toBe(30);
            expect($plugin->getConfig('validation.max_build_number_length'))->toBe(100);
            expect($plugin->getConfig('validation.max_download_url_length'))->toBe(1000);
        });

        it('can configure default values using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->defaultPlatform('ios')
                ->defaultIsActive(false)
                ->defaultIsBeta(true)
                ->defaultForceUpdate(true);
            
            expect($plugin->getConfig('defaults.platform'))->toBe('ios');
            expect($plugin->getConfig('defaults.is_active'))->toBeFalse();
            expect($plugin->getConfig('defaults.is_beta'))->toBeTrue();
            expect($plugin->getConfig('defaults.force_update'))->toBeTrue();
        });

        it('can configure features using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->multilingualReleaseNotes(false)
                ->versionRollback(false)
                ->betaVersions(false)
                ->forceUpdates(false)
                ->metadataStorage(false)
                ->auditTrail(false);
            
            expect($plugin->getConfig('features.multilingual_release_notes'))->toBeFalse();
            expect($plugin->getConfig('features.version_rollback'))->toBeFalse();
            expect($plugin->getConfig('features.beta_versions'))->toBeFalse();
            expect($plugin->getConfig('features.force_updates'))->toBeFalse();
            expect($plugin->getConfig('features.metadata_storage'))->toBeFalse();
            expect($plugin->getConfig('features.audit_trail'))->toBeFalse();
        });

        it('can configure platforms using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->addPlatform('web', [
                    'label' => 'Web',
                    'color' => 'info',
                    'icon' => 'heroicon-o-globe-alt',
                ]);
            
            $platformConfig = $plugin->getConfig('platforms.web');
            expect($platformConfig)->toBe([
                'label' => 'Web',
                'color' => 'info',
                'icon' => 'heroicon-o-globe-alt',
            ]);
        });

        it('can configure database settings using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->tableName('custom_app_versions')
                ->databaseConnection('custom');
            
            expect($plugin->getConfig('database.table_name'))->toBe('custom_app_versions');
            expect($plugin->getConfig('database.connection'))->toBe('custom');
        });

        it('can configure localization settings using fluent methods', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->defaultLocale('en')
                ->supportedLocales(['en', 'fr', 'ar'])
                ->fallbackLocale('en');
            
            expect($plugin->getConfig('localization.default_locale'))->toBe('en');
            expect($plugin->getConfig('localization.supported_locales'))->toBe(['en', 'fr', 'ar']);
            expect($plugin->getConfig('localization.fallback_locale'))->toBe('en');
        });
    });

    describe('Method Chaining', function () {
        it('supports method chaining for fluent API', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->api(false)
                ->apiCacheTtl(600)
                ->maxVersionLength(25)
                ->defaultIsActive(false)
                ->betaVersions(false);
            
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
            expect($plugin->getConfig('api.cache_ttl'))->toBe(600);
            expect($plugin->getConfig('validation.max_version_length'))->toBe(25);
            expect($plugin->getConfig('defaults.is_active'))->toBeFalse();
            expect($plugin->getConfig('features.beta_versions'))->toBeFalse();
        });

        it('returns the same plugin instance for chaining', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            $result = $plugin->api(false);
            
            expect($result)->toBe($plugin);
        });
    });
});
