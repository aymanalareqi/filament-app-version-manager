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

        it('platform configuration methods are deprecated but maintain backward compatibility', function () {
            $plugin = FilamentAppVersionManagerPlugin::make()
                ->addPlatform('web', [
                    'label' => 'Web',
                    'color' => 'info',
                    'icon' => 'heroicon-o-globe-alt',
                ]);

            // The addPlatform method is now deprecated and doesn't actually configure platforms
            // Platform configuration is handled directly by the Platform enum
            // This test verifies backward compatibility (method exists and returns plugin instance)
            expect($plugin)->toBeInstanceOf(FilamentAppVersionManagerPlugin::class);
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

    describe('Closure Support', function () {
        it('supports closures for navigation configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->navigationGroup(fn() => 'Dynamic Group')
                ->navigationIcon(fn() => 'heroicon-o-star')
                ->navigationSort(fn() => 5);

            expect($plugin->getNavigationGroup())->toBe('Dynamic Group');
            expect($plugin->getNavigationIcon())->toBe('heroicon-o-star');
            expect($plugin->getNavigationSort())->toBe(5);
        });

        it('supports closures for API configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->api(fn() => false)
                ->apiPrefix(fn() => 'v2')
                ->apiCacheTtl(fn() => 900)
                ->apiStats(fn() => true);

            expect($plugin->getConfig('api.enabled'))->toBeFalse();
            expect($plugin->getConfig('api.prefix'))->toBe('v2');
            expect($plugin->getConfig('api.cache_ttl'))->toBe(900);
            expect($plugin->getConfig('api.enable_stats'))->toBeTrue();
        });

        it('supports closures for validation configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->semanticVersioning(fn() => false)
                ->maxVersionLength(fn() => 30)
                ->maxBuildNumberLength(fn() => 15);

            expect($plugin->getConfig('validation.semantic_versioning'))->toBeFalse();
            expect($plugin->getConfig('validation.max_version_length'))->toBe(30);
            expect($plugin->getConfig('validation.max_build_number_length'))->toBe(15);
        });

        it('supports closures for default values configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->defaultPlatform(fn() => 'ios')
                ->defaultIsActive(fn() => false)
                ->defaultIsBeta(fn() => true);

            expect($plugin->getConfig('defaults.platform'))->toBe('ios');
            expect($plugin->getConfig('defaults.is_active'))->toBeFalse();
            expect($plugin->getConfig('defaults.is_beta'))->toBeTrue();
        });

        it('supports closures for feature configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->multilingualReleaseNotes(fn() => false)
                ->betaVersions(fn() => true)
                ->forceUpdates(fn() => false);

            expect($plugin->getConfig('features.multilingual_release_notes'))->toBeFalse();
            expect($plugin->getConfig('features.beta_versions'))->toBeTrue();
            expect($plugin->getConfig('features.force_updates'))->toBeFalse();
        });

        it('supports closures for localization configuration', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->defaultLocale(fn() => 'fr')
                ->supportedLocales(fn() => ['fr', 'de', 'es'])
                ->fallbackLocale(fn() => 'en');

            expect($plugin->getConfig('localization.default_locale'))->toBe('fr');
            expect($plugin->getConfig('localization.supported_locales'))->toBe(['fr', 'de', 'es']);
            expect($plugin->getConfig('localization.fallback_locale'))->toBe('en');
        });

        it('evaluates closures dynamically on each access', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            // Test with a closure that returns different values based on time
            $plugin->navigationSort(function () {
                return (int) date('s') % 2 === 0 ? 10 : 20;
            });

            // The closure should be evaluated each time
            $firstCall = $plugin->getNavigationSort();
            expect($firstCall)->toBeIn([10, 20]);

            // Test with a closure that accesses external state
            $value = 5;
            $plugin->apiCacheTtl(function () use (&$value) {
                return $value * 60;
            });

            expect($plugin->getConfig('api.cache_ttl'))->toBe(300);

            $value = 10;
            expect($plugin->getConfig('api.cache_ttl'))->toBe(600);
        });

        it('supports closures that access external variables', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();
            $environment = 'production';

            $plugin
                ->api(function () use (&$environment) {
                    return $environment === 'production';
                })
                ->apiPrefix(function () use (&$environment) {
                    return $environment === 'production' ? 'api' : 'dev-api';
                });

            expect($plugin->getConfig('api.enabled'))->toBeTrue();
            expect($plugin->getConfig('api.prefix'))->toBe('api');

            // Change environment and test again
            $environment = 'development';
            expect($plugin->getConfig('api.enabled'))->toBeFalse();
            expect($plugin->getConfig('api.prefix'))->toBe('dev-api');
        });

        it('maintains backward compatibility with primitive values', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            // Mix closures and primitive values
            $plugin
                ->navigationGroup('Static Group')
                ->navigationIcon(fn() => 'heroicon-o-dynamic')
                ->navigationSort(10)
                ->api(fn() => true)
                ->apiPrefix('v1');

            expect($plugin->getNavigationGroup())->toBe('Static Group');
            expect($plugin->getNavigationIcon())->toBe('heroicon-o-dynamic');
            expect($plugin->getNavigationSort())->toBe(10);
            expect($plugin->getConfig('api.enabled'))->toBeTrue();
            expect($plugin->getConfig('api.prefix'))->toBe('v1');
        });

        it('supports closures with complex return types', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin
                ->apiMiddleware(fn() => ['auth', 'throttle:60,1'])
                ->supportedLocales(fn() => ['en', 'ar', 'fr']);

            expect($plugin->getConfig('api.middleware'))->toBe(['auth', 'throttle:60,1']);
            expect($plugin->getConfig('localization.supported_locales'))->toBe(['en', 'ar', 'fr']);
        });

        it('evaluates closures in nested configuration arrays', function () {
            $plugin = FilamentAppVersionManagerPlugin::make();

            $plugin->configureWith([
                'api' => [
                    'enabled' => fn() => true,
                    'prefix' => fn() => 'dynamic',
                ],
                'features' => [
                    'beta_versions' => fn() => false,
                ],
            ]);

            $fullConfig = $plugin->getConfig();
            expect($fullConfig['api']['enabled'])->toBeTrue();
            expect($fullConfig['api']['prefix'])->toBe('dynamic');
            expect($fullConfig['features']['beta_versions'])->toBeFalse();
        });
    });
});
